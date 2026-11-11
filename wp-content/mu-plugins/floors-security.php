<?php
/**
 * Plugin Name: Floors Today Security
 * Description: XML-RPC off, staff-only user list, failed-login limits (+ optional Cloudflare Turnstile),
 *              security headers (CSP in report-only), readme/license removal after updates,
 *              and the real visitor IP behind Cloudflare for rate limits.
 *
 * Update-safe: lives in mu-plugins, no core or plugin edits.
 *
 * Turnstile on wp-login.php switches on when both constants are in wp-config.php:
 *   define('FT_TURNSTILE_SITE_KEY', '...');
 *   define('FT_TURNSTILE_SECRET_KEY', '...');
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ─── Real visitor IP ──────────────────────────────────────────────────────
 * The server also answers without Cloudflare, so CF-Connecting-IP is only
 * trusted when the request really comes from a Cloudflare address.
 */

function ft_sec_cloudflare_ranges(): array {
    return [
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18',
        '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17',
        '162.158.0.0/15', '104.16.0.0/13', '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32',
        '2a06:98c0::/29', '2c0f:f248::/32',
    ];
}

function ft_sec_ip_in_cidr(string $ip, string $cidr): bool {
    [$subnet, $bits] = explode('/', $cidr) + [1 => null];
    $ip_bin  = @inet_pton($ip);
    $net_bin = @inet_pton($subnet);
    if ($ip_bin === false || $net_bin === false || strlen($ip_bin) !== strlen($net_bin)) {
        return false;
    }
    $bits  = (int) $bits;
    $bytes = intdiv($bits, 8);
    if (strncmp($ip_bin, $net_bin, $bytes) !== 0) {
        return false;
    }
    $rest = $bits % 8;
    if ($rest === 0) {
        return true;
    }
    $mask = chr((0xff << (8 - $rest)) & 0xff);
    return (($ip_bin[$bytes] & $mask) === ($net_bin[$bytes] & $mask));
}

function ft_security_client_ip(): string {
    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $cf     = trim((string) ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
    if ($cf !== '' && filter_var($cf, FILTER_VALIDATE_IP) && filter_var($remote, FILTER_VALIDATE_IP)) {
        foreach (ft_sec_cloudflare_ranges() as $range) {
            if (ft_sec_ip_in_cidr($remote, $range)) {
                return $cf;
            }
        }
    }
    return $remote;
}

/* ─── Simple counters (transients) ─────────────────────────────────────────── */

function ft_sec_hits(string $key): int {
    return (int) get_transient('ft_sec_' . md5($key));
}

function ft_sec_hit(string $key, int $window): void {
    set_transient('ft_sec_' . md5($key), ft_sec_hits($key) + 1, $window);
}

function ft_sec_clear(string $key): void {
    delete_transient('ft_sec_' . md5($key));
}

/** One call for public forms: false when over the limit, otherwise counts the hit. */
function ft_security_rate_ok(string $key, int $max, int $window): bool {
    if (ft_sec_hits($key) >= $max) {
        return false;
    }
    ft_sec_hit($key, $window);
    return true;
}

/* ─── 1. XML-RPC fully off ──────────────────────────────────────────────────
 * Not used by the site; it allowed password guessing (system.multicall) and pingbacks.
 */

if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
    status_header(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'XML-RPC is disabled.';
    exit;
}
add_filter('xmlrpc_enabled', '__return_false');
add_filter('xmlrpc_methods', '__return_empty_array');
add_filter('wp_headers', function ($headers) {
    unset($headers['X-Pingback']);
    return $headers;
});
remove_action('wp_head', 'rsd_link');
add_filter('pings_open', '__return_false', 99);

/* ─── 2. No user list for visitors ─────────────────────────────────────────── */

add_filter('rest_pre_dispatch', function ($result, $server, $request) {
    $route = (string) $request->get_route();
    if (!preg_match('#^/wp/v2/users(/|$)#', $route)) {
        return $result;
    }
    // Logged-in staff keep it (editor author lists, Rank Math, /users/me).
    if (is_user_logged_in() && (current_user_can('list_users') || current_user_can('edit_posts'))) {
        return $result;
    }
    if (is_user_logged_in() && preg_match('#^/wp/v2/users/me/?$#', $route)) {
        return $result;
    }
    return new WP_Error('rest_forbidden', 'Sorry, you are not allowed to do that.', ['status' => rest_authorization_required_code()]);
}, 10, 3);

// ?author=N and author archives never reveal user names to visitors.
add_action('template_redirect', function () {
    if (is_user_logged_in()) {
        return;
    }
    if (isset($_GET['author']) || is_author()) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
}, 1);

add_filter('oembed_response_data', function ($data) {
    unset($data['author_name'], $data['author_url']);
    return $data;
});

/* ─── 3. Failed-login limits ───────────────────────────────────────────────
 * 8 wrong passwords per username+IP, or 30 per IP, in 15 minutes → blocked for 15 minutes.
 * Also counts wrong application passwords (REST Basic auth).
 */

const FT_SEC_LOGIN_WINDOW   = 15 * MINUTE_IN_SECONDS;
const FT_SEC_MAX_PER_USER   = 8;
const FT_SEC_MAX_PER_IP     = 30;

function ft_sec_login_keys(string $username): array {
    $ip = ft_security_client_ip();
    return ['login:' . strtolower(trim($username)) . '|' . $ip, 'login-ip:' . $ip];
}

function ft_sec_login_blocked(string $username): bool {
    [$user_key, $ip_key] = ft_sec_login_keys($username);
    return ft_sec_hits($user_key) >= FT_SEC_MAX_PER_USER || ft_sec_hits($ip_key) >= FT_SEC_MAX_PER_IP;
}

add_filter('authenticate', function ($user, $username, $password) {
    if ($username === '' || $username === null) {
        return $user;
    }
    if (ft_sec_login_blocked((string) $username)) {
        return new WP_Error('ft_too_many_logins', __('<strong>Error:</strong> Too many failed logins. Please wait 15 minutes and try again.'));
    }
    return $user;
}, 30, 3);

add_action('wp_login_failed', function ($username) {
    [$user_key, $ip_key] = ft_sec_login_keys((string) $username);
    ft_sec_hit($user_key, FT_SEC_LOGIN_WINDOW);
    ft_sec_hit($ip_key, FT_SEC_LOGIN_WINDOW);
});

add_action('wp_login', function ($user_login) {
    [$user_key] = ft_sec_login_keys((string) $user_login);
    ft_sec_clear($user_key);
});

add_action('application_password_failed_authentication', function () {
    ft_sec_hit('login-ip:' . ft_security_client_ip(), FT_SEC_LOGIN_WINDOW);
});

add_filter('rest_authentication_errors', function ($result) {
    if (!empty($result) || empty($_SERVER['PHP_AUTH_USER'])) {
        return $result;
    }
    if (ft_sec_hits('login-ip:' . ft_security_client_ip()) >= FT_SEC_MAX_PER_IP) {
        return new WP_Error('ft_too_many_logins', 'Too many failed logins. Please wait 15 minutes and try again.', ['status' => 429]);
    }
    return $result;
}, 5);

/* ─── 3b. Cloudflare Turnstile on wp-login.php (when keys are configured) ── */

function ft_sec_turnstile_on(): bool {
    return defined('FT_TURNSTILE_SITE_KEY') && defined('FT_TURNSTILE_SECRET_KEY')
        && FT_TURNSTILE_SITE_KEY !== '' && FT_TURNSTILE_SECRET_KEY !== '';
}

add_action('login_enqueue_scripts', function () {
    if (ft_sec_turnstile_on()) {
        wp_enqueue_script('cf-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', [], null, true);
    }
});

add_action('login_form', function () {
    if (ft_sec_turnstile_on()) {
        echo '<div class="cf-turnstile" data-sitekey="' . esc_attr(FT_TURNSTILE_SITE_KEY) . '" style="margin:0 0 16px"></div>';
    }
});

add_filter('authenticate', function ($user, $username) {
    // Only the wp-login.php form (not cookies, REST or app passwords).
    if (!ft_sec_turnstile_on() || $username === '' || $username === null || empty($_POST['log'])) {
        return $user;
    }
    $token = sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'] ?? ''));
    if ($token === '') {
        return new WP_Error('ft_turnstile', __('<strong>Error:</strong> Please complete the security check.'));
    }
    $res = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        'timeout' => 8,
        'body'    => ['secret' => FT_TURNSTILE_SECRET_KEY, 'response' => $token, 'remoteip' => ft_security_client_ip()],
    ]);
    if (is_wp_error($res)) {
        // Cloudflare unreachable: do not lock admins out; the login limits still apply.
        return $user;
    }
    $body = json_decode((string) wp_remote_retrieve_body($res), true);
    if (empty($body['success'])) {
        return new WP_Error('ft_turnstile', __('<strong>Error:</strong> The security check failed. Please try again.'));
    }
    return $user;
}, 20, 2);

/* ─── 4. Security headers ──────────────────────────────────────────────────
 * PHP pages only (the homepage is a PHP page too). The CSP is report-only for now:
 * nothing is blocked, the browser reports what it would block to csp-report below.
 */

function ft_sec_csp(): string {
    $s3 = 'https://floors-today-media.s3.amazonaws.com';
    $policy = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googletagmanager.com https://*.google-analytics.com https://connect.facebook.net https://cdn.callrail.com https://*.callrail.com https://maps.googleapis.com https://maps.gstatic.com https://challenges.cloudflare.com https://static.cloudflareinsights.com https://cdnjs.cloudflare.com https://tag.connectxmedia.com https://bzrcdn.openai.com https://apis.google.com https://accounts.google.com https://www.youtube.com https://analytics.tiktok.com https://snap.licdn.com",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com",
        "img-src 'self' data: blob: $s3 https://*.googleapis.com https://*.gstatic.com https://*.google.com https://*.google.ca https://*.google-analytics.com https://*.googletagmanager.com https://www.facebook.com https://*.callrail.com https://i.ytimg.com",
        "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com",
        "connect-src 'self' $s3 https://*.google-analytics.com https://*.analytics.google.com https://*.googletagmanager.com https://maps.googleapis.com https://www.facebook.com https://*.callrail.com https://app.floorstoday.ca https://cloudflareinsights.com https://tag.connectxmedia.com https://*.openai.com",
        "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://www.google.com https://www.facebook.com https://challenges.cloudflare.com https://app.floorstoday.ca https://www.googletagmanager.com",
        "media-src 'self' $s3",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self' https://app.floorstoday.ca",
        'report-uri ' . esc_url_raw(rest_url('floors-today/v1/csp-report')),
    ];
    return implode('; ', $policy);
}

function ft_sec_send_headers(bool $is_frontend): void {
    if (headers_sent()) {
        return;
    }
    if (is_ssl()) {
        // No preload until every subdomain is confirmed on HTTPS.
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=(), browsing-topics=()');

    // Embed pages (?ft_embed=...) are meant to be framed on other sites.
    $embed = isset($_GET['ft_embed']) && in_array(sanitize_key(wp_unslash($_GET['ft_embed'])), ['booking', 'contact', 'utm'], true);
    if (!$embed) {
        header('X-Frame-Options: SAMEORIGIN');
    }
    if ($is_frontend && !$embed) {
        header('Content-Security-Policy-Report-Only: ' . ft_sec_csp());
    }
}

add_action('send_headers', function () {
    if (!is_admin()) {
        ft_sec_send_headers(true);
    }
});
add_action('login_init', function () {
    ft_sec_send_headers(false);
});
add_action('admin_init', function () {
    if (!wp_doing_ajax()) {
        ft_sec_send_headers(false);
    }
});

// CSP reports: keep the last 100 (non-autoloaded option), at most 60 per IP per hour.
add_action('rest_api_init', function () {
    register_rest_route('floors-today/v1', '/csp-report', [
        'methods'             => 'POST',
        'permission_callback' => '__return_true',
        'callback'            => function (WP_REST_Request $request) {
            if (!ft_security_rate_ok('csp:' . ft_security_client_ip(), 60, HOUR_IN_SECONDS)) {
                return new WP_REST_Response(null, 204);
            }
            $raw  = substr((string) $request->get_body(), 0, 4000);
            $data = json_decode($raw, true);
            $r    = is_array($data) ? ($data['csp-report'] ?? $data) : [];
            if (!is_array($r) || empty($r)) {
                return new WP_REST_Response(null, 204);
            }
            $entry = [
                'time'     => gmdate('c'),
                'page'     => esc_url_raw(substr((string) ($r['document-uri'] ?? ''), 0, 300)),
                'blocked'  => sanitize_text_field(substr((string) ($r['blocked-uri'] ?? ''), 0, 300)),
                'directive'=> sanitize_text_field(substr((string) ($r['violated-directive'] ?? $r['effective-directive'] ?? ''), 0, 100)),
            ];
            $log   = get_option('ft_csp_reports', []);
            $log   = is_array($log) ? $log : [];
            $log[] = $entry;
            update_option('ft_csp_reports', array_slice($log, -100), false);
            return new WP_REST_Response(null, 204);
        },
    ]);
});

/* ─── 5. No readme.html / license.txt in the site root ─────────────────────
 * WordPress puts them back on every core update, so they are removed after
 * updates and checked twice a day.
 */

function ft_sec_remove_core_docs(): void {
    foreach (['readme.html', 'license.txt'] as $file) {
        $path = ABSPATH . $file;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

add_action('_core_updated_successfully', 'ft_sec_remove_core_docs');
add_action('upgrader_process_complete', function ($upgrader, $options) {
    if (($options['type'] ?? '') === 'core') {
        ft_sec_remove_core_docs();
    }
}, 10, 2);
add_action('init', function () {
    if (get_transient('ft_sec_docs_checked')) {
        return;
    }
    set_transient('ft_sec_docs_checked', 1, 12 * HOUR_IN_SECONDS);
    ft_sec_remove_core_docs();
});
