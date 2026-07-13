# Automated post-push health check for the live site. Read-only end to
# end - no writes, safe to run anytime, especially right after
# deploy-live.ps1 / push-local-db.ps1 / pull-live-db.ps1 / a git pull on
# the server.
#
# Built after a run of incidents that were each only caught by the user
# noticing something broken and asking for a manual investigation:
# a base-path regression (3 times), a git-pull/skip-worktree conflict,
# database corruption from a PowerShell encoding bug, and a sitewide
# Elementor CSS cache wipe from an over-broad cache-flush. This
# consolidates the checks that would have caught each of those
# immediately into one script.
#
# Usage (from next-homepage-src/):
#   ./scripts/check-live-health.ps1

$LiveHost = "16.54.143.52"
$SshUser = "ubuntu"
$SiteUser = "floorstodayfinal"
$LiveSiteRoot = "/home/floorstodayfinal/htdocs/floorstoday.ca"
$LiveUrl = "https://floorstoday.ca"

$script:failures = @()
$script:passes = 0

function Test-Check {
    param([string]$Name, [bool]$Ok, [string]$Detail = "")
    if ($Ok) {
        Write-Host "  [PASS] $Name" -ForegroundColor Green
        $script:passes++
    } else {
        Write-Host "  [FAIL] $Name $Detail" -ForegroundColor Red
        $script:failures += "$Name $Detail"
    }
}

function Get-UrlStatus($url) {
    try {
        $resp = Invoke-WebRequest -Uri $url -UseBasicParsing -Method Head -TimeoutSec 15
        return $resp.StatusCode
    } catch {
        if ($_.Exception.Response) { return [int]$_.Exception.Response.StatusCode }
        return 0
    }
}

Write-Host "== 0. Live public/ still has git skip-worktree set (root cause of past clobber incidents) ==" -ForegroundColor Cyan
$swResult = (ssh "${SshUser}@${LiveHost}" "sudo -u $SiteUser bash -c 'cd $LiveSiteRoot && total=`$(git ls-files public | wc -l); flagged=`$(git ls-files -v public | grep -c ""^S"" || true); echo `$flagged/`$total'") -join "`n"
Test-Check "all tracked public/ files have skip-worktree set" ($swResult -match '^(\d+)/(\d+)$' -and $Matches[1] -eq $Matches[2] -and [int]$Matches[2] -gt 0) "(flagged/total: $swResult)"

Write-Host "== 1. Homepage loads and settings inject ==" -ForegroundColor Cyan
$homeResp = Invoke-WebRequest -Uri "$LiveUrl/" -UseBasicParsing -TimeoutSec 20
$homeHtml = $homeResp.Content
Test-Check "Homepage returns 200" ($homeResp.StatusCode -eq 200) "(got $($homeResp.StatusCode))"
Test-Check "window.__FT_HOMEPAGE_SETTINGS__ present" ($homeHtml -match '__FT_HOMEPAGE_SETTINGS__')

Write-Host "== 2. Base path check (the 3x regression today) ==" -ForegroundColor Cyan
$turbopackMatch = [regex]::Match($homeHtml, 'turbopack-[a-zA-Z0-9._~-]+\.js')
if ($turbopackMatch.Success) {
    $chunkUrl = "$LiveUrl/public/_next/static/chunks/$($turbopackMatch.Value)"
    $chunkContent = (Invoke-WebRequest -Uri $chunkUrl -UseBasicParsing -TimeoutSec 15).Content
    $hasCorrectPath = $chunkContent -match [regex]::Escape('/public/_next/')
    $hasWrongPath = $chunkContent -match [regex]::Escape('/floorstodayfinal/public/_next/')
    Test-Check "Turbopack chunk has correct /public/_next/ path" ($hasCorrectPath -and -not $hasWrongPath) "(wrong-path found: $hasWrongPath)"
} else {
    Test-Check "Turbopack chunk found in homepage HTML" $false
}

Write-Host "== 3. All Next.js chunks referenced by homepage resolve, and none leak a local WAMP path ==" -ForegroundColor Cyan
# Checks every referenced chunk's own content for "localhost/floorstodayfinal",
# not just the turbopack chunk in section 2 above - a stale committed
# fallback (homepage-settings.snapshot.json) leaked this exact pattern into
# an unrelated chunk once already, undetected, because only the turbopack
# chunk was ever content-checked.
$chunkMatches = [regex]::Matches($homeHtml, 'chunks/[a-zA-Z0-9._~-]+\.(js|css)') | ForEach-Object { $_.Value } | Select-Object -Unique
foreach ($chunk in $chunkMatches) {
    $chunkUrl = "$LiveUrl/public/_next/static/$chunk"
    try {
        $chunkResp = Invoke-WebRequest -Uri $chunkUrl -UseBasicParsing -TimeoutSec 15
        $status = $chunkResp.StatusCode
        $hasLeak = $chunkResp.Content -match 'localhost/floorstodayfinal'
    } catch {
        $status = if ($_.Exception.Response) { [int]$_.Exception.Response.StatusCode } else { 0 }
        $hasLeak = $false
    }
    Test-Check "chunk $chunk" ($status -eq 200) "(HTTP $status)"
    Test-Check "chunk $chunk has no local WAMP path leak" (-not $hasLeak)
}

Write-Host "== 4. Representative real pages load ==" -ForegroundColor Cyan
$pages = @("/", "/faqs/", "/contact/", "/about/", "/categories/engineered-hardwood/", "/our-products/12-mm-laminate-click-waterproof-4/")
$pageHtml = @{}
foreach ($p in $pages) {
    $resp = Invoke-WebRequest -Uri "$LiveUrl$p" -UseBasicParsing -TimeoutSec 20 -MaximumRedirection 5
    Test-Check "page $p" ($resp.StatusCode -eq 200) "(HTTP $($resp.StatusCode))"
    $pageHtml[$p] = $resp.Content
}

Write-Host "== 5a. Elementor CSS referenced by sampled pages resolves ==" -ForegroundColor Cyan
foreach ($p in $pages) {
    $cssRefs = [regex]::Matches($pageHtml[$p], 'elementor/css/post-[0-9]+\.css') | ForEach-Object { $_.Value } | Select-Object -Unique
    foreach ($ref in $cssRefs) {
        $status = Get-UrlStatus "$LiveUrl/wp-content/uploads/$ref"
        Test-Check "$p -> $ref" ($status -eq 200) "(HTTP $status)"
    }
}

Write-Host "== 5b. ALL published Elementor library templates (sitewide impact) ==" -ForegroundColor Cyan
$elementorCheckPhp = @'
<?php
define('WP_USE_THEMES', false);
require '__SITEROOT__/wp-load.php';
global $wpdb;
$ids = $wpdb->get_col("SELECT p.ID FROM {$wpdb->prefix}posts p INNER JOIN {$wpdb->prefix}postmeta pm ON p.ID=pm.post_id WHERE pm.meta_key='_elementor_data' AND p.post_status='publish' AND p.post_type='elementor_library'");
foreach ($ids as $id) {
    $css_file = new \Elementor\Core\Files\CSS\Post($id);
    $path = $css_file->get_path();
    echo $id . ':' . (file_exists($path) ? 'OK' : 'MISSING') . "\n";
}
'@ -replace '__SITEROOT__', $LiveSiteRoot
$remotePhpPath = "/tmp/health-check-elementor-$(Get-Date -Format 'yyyyMMddHHmmss').php"
$localPhpFile = Join-Path $env:TEMP "health-check-elementor.php"
Set-Content -Path $localPhpFile -Value $elementorCheckPhp -Encoding utf8 -NoNewline
scp -q $localPhpFile "${SshUser}@${LiveHost}:${remotePhpPath}"
$elementorResult = ssh "${SshUser}@${LiveHost}" "sudo -u $SiteUser php $remotePhpPath"
try { ssh "${SshUser}@${LiveHost}" "rm -f $remotePhpPath" 2>$null } catch {}
Remove-Item $localPhpFile -Force -ErrorAction SilentlyContinue
if ($elementorResult) {
    foreach ($line in ($elementorResult -split "`n")) {
        if ($line -match '^(\d+):(OK|MISSING)$') {
            Test-Check "elementor_library template $($Matches[1])" ($Matches[2] -eq 'OK')
        }
    }
} else {
    Test-Check "Elementor template query returned any result" $false
}

Write-Host "== 6. Config / .htaccess integrity ==" -ForegroundColor Cyan
# ssh output comes back as an array of lines, not one string - -match on
# an array returns an array of per-line results, not a single boolean.
# Join into one string first.
$publicHtaccess = (ssh "${SshUser}@${LiveHost}" "sudo -u $SiteUser cat $LiveSiteRoot/public/.htaccess 2>&1") -join "`n"
Test-Check "public/.htaccess exists" ($publicHtaccess -notmatch 'No such file')
Test-Check "public/.htaccess blocks .log files" ($publicHtaccess -match '\\\.log\$')
Test-Check "public/.htaccess does NOT block __next/index.txt (regression check)" ($publicHtaccess -notmatch '__next\[')

$rootHtaccess = (ssh "${SshUser}@${LiveHost}" "sudo -u $SiteUser cat $LiveSiteRoot/.htaccess 2>&1") -join "`n"
Test-Check "root .htaccess exists" ($rootHtaccess -notmatch 'No such file')
Test-Check "root .htaccess has WordPress rewrite rules" ($rootHtaccess -match 'RewriteEngine On')

$wpConfigCheck = ssh "${SshUser}@${LiveHost}" "sudo -u $SiteUser bash -c 'grep DB_NAME $LiveSiteRoot/wp-config.php && grep DB_HOST $LiveSiteRoot/wp-config.php' 2>&1"
Test-Check "wp-config.php present with readable DB_NAME/DB_HOST" ($wpConfigCheck -match 'DB_NAME' -and $wpConfigCheck -match 'DB_HOST')

Write-Host "== 7. No recent PHP fatal errors ==" -ForegroundColor Cyan
$recentErrors = ssh "${SshUser}@${LiveHost}" "sudo tail -50 /home/floorstodayfinal/logs/php/error.log 2>/dev/null"
$fiveMinAgo = (Get-Date).ToUniversalTime().AddMinutes(-5)
$recentFatal = $false
foreach ($line in ($recentErrors -split "`n")) {
    if ($line -match '^\[(\d{2})-(\w{3})-(\d{4}) (\d{2}):(\d{2}):(\d{2}) UTC\].*Fatal error') {
        try {
            $ts = [datetime]::ParseExact("$($Matches[1])-$($Matches[2])-$($Matches[3]) $($Matches[4]):$($Matches[5]):$($Matches[6])", "dd-MMM-yyyy HH:mm:ss", [System.Globalization.CultureInfo]::InvariantCulture)
            if ($ts -gt $fiveMinAgo) { $recentFatal = $true }
        } catch {}
    }
}
Test-Check "No PHP fatal errors in the last 5 minutes" (-not $recentFatal)

Write-Host "== 8. Database settings integrity ==" -ForegroundColor Cyan
$dbCheckPhp = @'
<?php
define('WP_USE_THEMES', false);
require '__SITEROOT__/wp-load.php';
$settings = get_option('ft_next_homepage_settings');
if ($settings === false) {
    echo "CORRUPTED\n";
} else {
    echo "OK:" . count($settings) . "\n";
}
'@ -replace '__SITEROOT__', $LiveSiteRoot
$remoteDbPhpPath = "/tmp/health-check-db-$(Get-Date -Format 'yyyyMMddHHmmss').php"
$localDbPhpFile = Join-Path $env:TEMP "health-check-db.php"
Set-Content -Path $localDbPhpFile -Value $dbCheckPhp -Encoding utf8 -NoNewline
scp -q $localDbPhpFile "${SshUser}@${LiveHost}:${remoteDbPhpPath}"
$dbResult = ssh "${SshUser}@${LiveHost}" "sudo -u $SiteUser php $remoteDbPhpPath"
try { ssh "${SshUser}@${LiveHost}" "rm -f $remoteDbPhpPath" 2>$null } catch {}
Remove-Item $localDbPhpFile -Force -ErrorAction SilentlyContinue
Test-Check "ft_next_homepage_settings unserializes correctly" ($dbResult -match '^OK:(\d+)') "(got: $dbResult)"

Write-Host ""
Write-Host "==============================================================" -ForegroundColor Cyan
if ($script:failures.Count -eq 0) {
    Write-Host " ALL CLEAR - $($script:passes) checks passed, 0 failures" -ForegroundColor Green
    Write-Host "==============================================================" -ForegroundColor Cyan
    exit 0
} else {
    Write-Host " $($script:failures.Count) CHECK(S) FAILED ($($script:passes) passed):" -ForegroundColor Red
    foreach ($f in $script:failures) { Write-Host "   - $f" -ForegroundColor Red }
    Write-Host "==============================================================" -ForegroundColor Cyan
    exit 1
}
