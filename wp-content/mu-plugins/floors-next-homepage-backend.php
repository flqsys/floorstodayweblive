<?php
/**
 * Plugin Name: Floors Today Next Homepage Backend
 * Description: WordPress settings and REST data for the Next.js homepage front end.
 * Version: 0.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

const FT_NEXT_HOME_OPTION = 'ft_next_homepage_settings';
const FT_NEXT_SENDY_OPTION = 'ft_next_sendy_settings';

function ft_next_homepage_defaults() {
    $public_url = untrailingslashit(ft_next_homepage_asset_url());

    return [
        'primary_color' => '#155f99',
        'secondary_color' => 'lab(76 3.16 65.32)',
        'background_color' => 'oklch(0.985 0.002 90)',
        'foreground_color' => 'oklch(0.20 0.02 30)',
        'phone' => '1-888-772-7848',
        'email' => 'info@floorstoday.com',
        'service_area' => '',
        'logo_text' => 'Floors Today',
        'logo_image' => '',
        'favicon_image' => $public_url . '/favicon.png',
        'logo_size' => '40px',
        'cta_label' => 'Free Estimate',
        'show_header' => '1',
        'show_footer' => '1',
        'facebook_url' => '',
        'instagram_url' => '',
        'linkedin_url' => '',
        'youtube_url' => '',
        'tiktok_url' => '',
        'footer_badge_image_1' => '',
        'footer_badge_image_2' => '',
        'footer_badge_image_3' => '',
        'footer_badge_image_4' => '',
        'footer_badge_image_5' => '',
        'footer_badge_image_6' => '',
        'footer_badge_height' => '60px',
        'seo_title' => 'Floors Today | Flooring Installation in Ontario',
        'seo_description' => 'Shop premium hardwood, laminate, vinyl and carpet flooring with free in-home estimates and professional installation across Ontario.',
        'seo_canonical_url' => home_url('/'),
        'seo_robots' => 'index, follow, max-image-preview:large',
        'seo_og_title' => 'Floors Today | Premium Flooring in Ontario',
        'seo_og_description' => 'Free in-home flooring estimates, transparent pricing and professional installation across Ontario.',
        'seo_og_image' => '',
        'button_radius' => '8px',
        'button_font_weight' => '700',
        'button_text_transform' => 'none',
        'button_padding_x' => '18px',
        'button_padding_y' => '12px',
        'button_hover_mix' => '88%',
        'button_border_width' => '0px',
        'button_border_style' => 'solid',
        'button_border_color' => 'transparent',
        'hero_badge' => 'LIMITED TIME: 50.50.50 SALE',
        'hero_badge_bg_color' => 'lab(76 3.16 65.32)',
        'hero_badge_text_color' => '#ffffff',
        'hero_badge_font_size' => '16px',
        'hero_badge_mobile_font_size' => '14px',
        'hero_badge_padding_x' => '16px',
        'hero_badge_padding_y' => '8px',
        'hero_title' => 'Transform Your Home with',
        'hero_highlight' => 'Premium Flooring',
        'hero_badge_animation_color_1' => 'lab(76 3.16 65.32)',
        'hero_badge_animation_color_2' => '#ffffff',
        'hero_badge_animation_location' => '90deg',
        'hero_badge_animation_speed' => '4s',
        'hero_title_font_size' => '60px',
        'hero_title_mobile_font_size' => '30px',
        'hero_text' => 'All-inclusive pricing with no hidden fees. Get a complete quote during your free in-home consultation.',
        'hero_image' => '',
        'hero_show_background' => '1',
        'hero_show_overlay' => '1',
        'hero_overlay_opacity' => '0.72',
        'form_title' => 'Get Your FREE In-Home Estimate',
        'form_subtitle' => 'No obligation. Takes just 2 minutes.',
        'process_title' => 'How It Works',
        'process_text' => 'Getting beautiful new floors has never been easier. Our simple 3-step process takes the stress out of flooring.',
        'process_bg_color_1' => 'oklch(0.985 0.002 90)',
        'process_bg_color_2' => 'oklch(0.985 0.002 90)',
        'process_bg_location' => 'to bottom',
        'comparison_title' => 'All-Inclusive Price Estimate, No Hidden Fees',
        'comparison_table_title' => 'What\'s Included',
        'comparison_text' => 'Floors Today makes it easy with one, easy to understand price, complete with all the commonly up-charged items required for your floor to be installed. You will know the full project price during your free in-home appointment, upfront, before any installation work begins.',
        'comparison_button' => 'Book An Appointment',
        'comparison_bg_color_1' => 'var(--primary)',
        'comparison_bg_color_2' => 'var(--primary)',
        'comparison_bg_location' => 'to bottom',
        'cta_title' => 'Ready to Get Started?',
        'cta_subtitle' => 'Schedule a FREE In-Home Estimate',
        'cta_text' => 'Our flooring experts will bring samples to your home, measure your space, and provide an all-inclusive price quote with no hidden fees.',
        'cta_button' => 'Schedule Now',
        'cta_bg_color_1' => 'var(--primary)',
        'cta_bg_color_2' => 'var(--primary)',
        'cta_bg_location' => 'to bottom',
        'category_title' => 'Shop By Category',
        'category_text' => 'Explore our wide selection of premium flooring options for every style and budget',
        'category_bg_color_1' => 'oklch(0.96 0.005 90)',
        'category_bg_color_2' => 'oklch(0.96 0.005 90)',
        'category_bg_location' => 'to bottom',
        'guarantee_title' => 'Low Price Guarantee',
        'guarantee_subtitle' => "We won't be beat on price!",
        'guarantee_text' => "If you find a lower price on a comparable product and installation, we'll beat any competitive offer, guaranteed! Our commitment to value means you get the best flooring at the best price, every time.",
        'guarantee_link' => 'Get More Information',
        'guarantee_image' => 'https://images.unsplash.com/photo-1600566752355-35792bedcfea?w=600&h=400&fit=crop',
        'guarantee_bg_color_1' => 'oklch(0.985 0.002 90)',
        'guarantee_bg_color_2' => 'oklch(0.985 0.002 90)',
        'guarantee_bg_location' => 'to bottom',
        'deals_badge' => 'Limited Time Offers',
        'deals_title' => 'Floors Today Coupons & Special Offers',
        'deals_text' => 'Exclusive flooring deals designed to fit your budget ??? quality floors without the hidden costs',
        'deals_body' => 'At Floors Today, we believe great flooring should be accessible without confusion or hidden costs. Along with our everyday competitive pricing, we offer limited-time promotions and special deals to help homeowners save on high-quality flooring and professional installation.',
        'deals_card_title' => '50.50.50',
        'deals_card_subtitle' => 'SALE',
        'deals_button' => 'Claim Your Savings',
        'deals_details_label' => 'View offer details',
        'deals_includes_title' => 'Your project includes',
        'deals_includes' => "Free home estimate\nSamples brought to you\nProfessional installation",
        'deals_popup_eyebrow' => 'Limited-time offer',
        'deals_popup_title' => 'Save on Your Complete Flooring Project',
        'deals_popup_intro' => 'Choose your floors at home, receive transparent project pricing, and arrange professional installation.',
        'deals_popup_button' => 'Book a Free Appointment',
        'deals_popup_steps_title' => 'How the offer works',
        'deals_popup_steps' => "Free home estimate|Book online or call {phone}.\nShop from home|Compare samples and receive your project estimate on site.\nEligible savings|Save up to 60% on select installed flooring styles.\nProfessional finish|Your flooring is installed by qualified professionals.",
        'deals_popup_terms' => '*Get 15% off plus up to an additional 45% on carpet, or 25% on hardwood, laminate and vinyl, based on project size. Applies to select styles, basic installation, standard padding and materials. Excludes upgrades, stairs, specialized removal or preparation, non-standard furniture moving, miscellaneous charges and prior purchases. Residential installations only, while supplies last. Offer ends June 29, 2026 and is subject to change.',
        'deals_popup_terms_extra' => 'Next-day installation applies only to eligible in-stock products and service areas. Installation may be completed by qualified independent professionals where applicable.',
        'deals_bg_color_1' => 'oklch(0.96 0.005 90)',
        'deals_bg_color_2' => 'oklch(0.985 0.002 90)',
        'deals_bg_location' => 'to bottom',
        'testimonials_title' => 'What Our Customers Say',
        'testimonials_text' => 'Join thousands of satisfied Ontario homeowners who trust Floors Today for their flooring needs',
        'show_testimonials' => '1',
        'testimonials_embed_code' => '',
        'testimonials_bg_color_1' => 'oklch(0.985 0.002 90)',
        'chat_embed_code' => '',
        'fb_pixel_id' => '',
        'ga4_measurement_id' => '',
        'gtm_container_id' => '',
        'recaptcha_site_key' => '',
        'testimonials_bg_color_2' => 'oklch(0.985 0.002 90)',
        'testimonials_bg_location' => 'to bottom',
        'newsletter_title' => 'Subscribe to Newsletter',
        'newsletter_popup_title' => 'Promo Terms & Conditions',
        'newsletter_text' => 'Get the latest deals and flooring tips',
        'newsletter_button' => 'Subscribe',
        'newsletter_claim_button' => 'Claim $300',
        'newsletter_details_text' => 'Complete the short form in the newsletter section to receive the latest flooring deals, project tips, and details about your $300 store credit.',
        'warranty_popup_title' => '3 Year Warranty',
        'warranty_details_content' => 'Our 3-year warranty covers manufacturing defects and installation workmanship. Contact us within the warranty period for a free inspection and repair.',
        'warranty_see_more_text' => 'See More',
        'warranty_see_more_url' => '',
        'footer_about' => "We believe in transparent pricing. That's why our all-inclusive estimates include every essential detail in delivering a seamless flooring experience with no unexpected costs.",
        'footer_about_title' => 'About Us',
        'footer_about_links' => [
            ['label' => 'About Us', 'url' => '/about-us/'],
            ['label' => 'Contact Us', 'url' => '/contact/'],
        ],
        'footer_categories_title' => 'Categories',
        'footer_help_title' => 'Help Area',
        'footer_help_links' => [
            ['label' => 'How Shop at Home Works', 'url' => '#how-it-works'],
            ['label' => 'Product Care', 'url' => '/product-care/'],
            ['label' => 'Contact', 'url' => '/contact/'],
        ],
        'footer_policies_title' => 'Our Policies',
        'footer_policy_links' => [
            ['label' => 'Terms Of Use', 'url' => '/terms-of-use/'],
            ['label' => 'FAQs', 'url' => '/faqs/'],
            ['label' => 'Privacy Policy', 'url' => '/privacy-policy/'],
            ['label' => 'Warranty Information', 'url' => '/warranty/'],
        ],
        'footer_bottom_links' => [
            ['label' => 'Careers', 'url' => '/careers/'],
            ['label' => 'Privacy Policy', 'url' => '/privacy-policy/'],
            ['label' => 'Sitemap', 'url' => '/sitemap_index.xml'],
            ['label' => 'Terms Of Use', 'url' => '/terms-of-use/'],
        ],
        'footer_copyright' => 'Floors Today Copyright {year} All Rights Reserved',
        'google_places_api_key' => '',
        'footer_bg_color_1' => 'oklch(0.20 0.02 30)',
        'footer_bg_color_2' => 'oklch(0.20 0.02 30)',
        'footer_bg_location' => 'to bottom',
        'nav_items' => [
            ['name' => 'Solid Hardwood', 'href' => '#hardwood'],
            ['name' => 'Engineered Hardwood', 'href' => '#engineered'],
            ['name' => 'Laminate', 'href' => '#laminate'],
            ['name' => 'Vinyl', 'href' => '#vinyl'],
            ['name' => 'Carpet', 'href' => '#carpet'],
        ],
        'utility_links' => [],
        'process_steps' => [
            [
                'title' => 'Schedule a Free In-Home Consult with a Flooring Expert',
                'description' => 'Book your free consultation at a time that works for you. Our experts come to your home with samples.',
                'button' => 'Get Started Now',
                'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400&h=300&fit=crop',
            ],
            [
                'title' => 'No Surprises, No Hidden Fees',
                'description' => 'A flooring expert will bring samples, help you pick the right floors, measure your rooms, and give you an All-Inclusive Price Estimate.',
                'button' => '',
                'image' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=400&h=300&fit=crop',
            ],
            [
                'title' => 'Schedule-Friendly Installation',
                'description' => 'Upon approval, you will be able to finance your purchase and schedule your professional installation.',
                'button' => '',
                'image' => 'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?w=400&h=300&fit=crop',
            ],
        ],
        'comparison_rows' => [
            'Product Cost',
            'Measuring',
            'Professional Installation',
            'Padding/Underlayment',
            'Moving Furniture',
            'Haul Away of Old Flooring',
            'Thresholds/Transitions',
            'Clean Up',
            'All-Inclusive Price Estimate',
        ],
        'categories' => [
            ['name' => 'Solid Hardwood', 'slug' => 'solid-hardwood', 'description' => 'Timeless elegance with natural wood beauty', 'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=500&fit=crop'],
            ['name' => 'Engineered Hardwood', 'slug' => 'engineered-hardwood', 'description' => 'Versatile and durable for any room', 'image' => 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=400&h=500&fit=crop'],
            ['name' => 'Laminate', 'slug' => 'laminate', 'description' => 'Affordable style with easy maintenance', 'image' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=400&h=500&fit=crop'],
            ['name' => 'Vinyl', 'slug' => 'vinyl', 'description' => 'Waterproof and pet-friendly options', 'image' => 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=400&h=500&fit=crop'],
            ['name' => 'Carpet', 'slug' => 'carpet', 'description' => 'Soft comfort for bedrooms and living areas', 'image' => 'https://images.unsplash.com/photo-1600210492493-0946911123ea?w=400&h=500&fit=crop'],
        ],
        'offers' => [
            ['title' => 'Up to 50% Off', 'description' => 'Select hardwood and engineered flooring styles'],
            ['title' => 'Free Installation', 'description' => 'On qualifying orders over $2,500'],
            ['title' => 'Price Match Plus', 'description' => "We'll beat any competitor's price by 5%"],
            ['title' => '0% Financing', 'description' => 'For 12 months on approved credit'],
        ],
        'testimonials' => [
            ['name' => 'Sarah M.', 'location' => 'Toronto, ON', 'floorType' => 'Solid Hardwood', 'text' => 'The team at Floors Today was incredible. From the initial consultation to the final installation, everything was seamless. Our new hardwood floors look absolutely stunning!'],
            ['name' => 'Michael R.', 'location' => 'Mississauga, ON', 'floorType' => 'Engineered Hardwood', 'text' => 'I was worried about hidden fees after bad experiences elsewhere, but Floors Today delivered exactly what they promised. The all-inclusive pricing is the real deal.'],
            ['name' => 'Jennifer L.', 'location' => 'Hamilton, ON', 'floorType' => 'Luxury Vinyl', 'text' => 'Best decision we made for our home renovation. The vinyl flooring is perfect for our busy family with kids and pets. Highly recommend their professional installation.'],
        ],
    ];
}

function ft_next_homepage_parse_link_string($value) {
    $items = [];
    foreach (explode("\n", str_replace("\r\n", "\n", (string) $value)) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $parts = explode('|', $line, 2);
        $label = trim($parts[0]);
        $url   = isset($parts[1]) ? trim($parts[1]) : '#';
        if ($label !== '') {
            $items[] = ['label' => $label, 'url' => $url ?: '#'];
        }
    }
    return $items;
}

function ft_next_homepage_settings() {
    $saved = get_option(FT_NEXT_HOME_OPTION, []);
    $settings = array_replace_recursive(ft_next_homepage_defaults(), is_array($saved) ? $saved : []);

    array_walk_recursive($settings, function (&$value) {
        if (is_string($value)) {
            $value = ft_next_homepage_normalize_saved_url($value);
        }
    });

    // Migrate footer link strings to array format
    foreach (['footer_about_links', 'footer_help_links', 'footer_policy_links', 'footer_bottom_links'] as $key) {
        if (is_string($settings[$key])) {
            $settings[$key] = ft_next_homepage_parse_link_string($settings[$key]);
        }
    }

    // Resolve footer link URLs to full absolute URLs so React components get correct hrefs
    foreach (['footer_about_links', 'footer_help_links', 'footer_policy_links', 'footer_bottom_links'] as $key) {
        if (is_array($settings[$key])) {
            $settings[$key] = array_map(function ($item) {
                if (is_array($item) && isset($item['url'])) {
                    $item['url'] = ft_next_header_shortcode_url((string) $item['url']);
                }
                return $item;
            }, $settings[$key]);
        }
    }

    // Parse src and id from embed code so REST API callers get the same clean values
    // as the PHP bridge injects via window.__FT_HOMEPAGE_SETTINGS__
    $embed_raw = trim($settings['testimonials_embed_code'] ?? '');
    $embed_src = '';
    $embed_id  = '';
    if ($embed_raw !== '') {
        if (preg_match('/\bsrc=["\']([^"\']+)["\']/', $embed_raw, $m)) {
            $embed_src = esc_url_raw($m[1]);
        }
        if (preg_match('/\bid=["\']([^"\']+)["\']/', $embed_raw, $m)) {
            $embed_id = sanitize_text_field($m[1]);
        }
    }
    // Add parsed values — leave testimonials_embed_code as the raw tag so the admin textarea still shows the original code
    $settings['testimonials_script_src']  = $embed_src;
    $settings['testimonials_script_id']   = $embed_id;

    // Parse chat embed code — same pattern, raw tag kept for admin display
    $chat_raw = trim($settings['chat_embed_code'] ?? '');
    $chat_src = '';
    $chat_id  = '';
    if ($chat_raw !== '') {
        if (preg_match('/\bsrc=["\']([^"\']+)["\']/', $chat_raw, $m)) {
            $chat_src = esc_url_raw($m[1]);
        }
        if (preg_match('/\bid=["\']([^"\']+)["\']/', $chat_raw, $m)) {
            $chat_id = sanitize_text_field($m[1]);
        }
    }
    $settings['chat_script_src'] = $chat_src;
    $settings['chat_script_id']  = $chat_id;

    return $settings;
}

function ft_next_homepage_frontend_url() {
    return home_url('/');
}

function ft_next_homepage_asset_url() {
    return home_url('/public/');
}

function ft_next_homepage_css_length($value, $fallback) {
    $value = trim((string) $value);

    if (preg_match('/^\d+(?:\.\d+)?(?:px|rem|em|%)$/', $value)) {
        return $value;
    }

    return $fallback;
}

function ft_next_homepage_category_slugs() {
    return [
        'solid-hardwood',
        'engineered-hardwood',
        'laminate',
        'vinyl',
        'carpet',
    ];
}

function ft_next_homepage_category_url($slug) {
    // Accept a bare slug OR a full path like /categories/solid-hardwood
    $slug = trim((string) $slug, '/');
    if (strpos($slug, '/') !== false) {
        $slug = basename($slug);
    }
    $slug = sanitize_title($slug);

    if ($slug === '' || !in_array($slug, ft_next_homepage_category_slugs(), true)) {
        return '';
    }

    return home_url('/categories/' . $slug . '/');
}

function ft_next_homepage_normalize_category_urls($value) {
    if (!is_string($value) || $value === '') {
        return $value;
    }

    foreach (ft_next_homepage_category_slugs() as $slug) {
        $placeholder = '%%FT_NEXT_CATEGORY_' . strtoupper(str_replace('-', '_', $slug)) . '%%';
        $category_url = ft_next_homepage_category_url($slug);

        $value = str_replace(
            [
                'http://localhost/floorstodaystore/a/product-category/' . $slug . '/',
                'https://localhost/floorstodaystore/a/product-category/' . $slug . '/',
                'http://localhost/floorstodayhome/a/product-category/' . $slug . '/',
                'https://localhost/floorstodayhome/a/product-category/' . $slug . '/',
                'https://floorstoday.ca/a/product-category/' . $slug . '/',
                'https://staging.floorstoday.ca/a/product-category/' . $slug . '/',
                'http://localhost/floorstodaystore/product-category/' . $slug . '/',
                'https://localhost/floorstodaystore/product-category/' . $slug . '/',
                'http://localhost/floorstodayhome/product-category/' . $slug . '/',
                'https://localhost/floorstodayhome/product-category/' . $slug . '/',
                'https://floorstoday.ca/product-category/' . $slug . '/',
                'https://staging.floorstoday.ca/product-category/' . $slug . '/',
                '/a/product-category/' . $slug . '/',
                '/product-category/' . $slug . '/',
            ],
            $placeholder,
            $value
        );

        $value = str_replace($placeholder, $category_url, $value);
    }

    return $value;
}

function ft_next_homepage_normalize_saved_url($value) {
    if (!is_string($value) || $value === '') {
        return $value;
    }

    $uploads_url = 'https://floors-today-media.s3.amazonaws.com/wp-content/uploads/';
    $value = str_replace('https//', 'https://', $value);

    if (str_starts_with($value, $uploads_url)) {
        return ft_next_homepage_normalize_category_urls($value);
    }

    $duplicate_uploads_url = 'https://floors-today-media.s3.amazonaws.comhttps://floors-today-media.s3.amazonaws.com/wp-content/uploads/';
    if (str_starts_with($value, $duplicate_uploads_url)) {
        return ft_next_homepage_normalize_category_urls($uploads_url . substr($value, strlen($duplicate_uploads_url)));
    }

    $public_url = trailingslashit(ft_next_homepage_asset_url());
    $home_url = trailingslashit(home_url());

    $replacements = [
        'http://localhost/floorstodaystore/wp-content/uploads/' => $uploads_url,
        'https://localhost/floorstodaystore/wp-content/uploads/' => $uploads_url,
        'http://localhost/floorstodayhome/wp-content/uploads/' => $uploads_url,
        'https://localhost/floorstodayhome/wp-content/uploads/' => $uploads_url,
        'https://floorstoday.ca/floorstodaystore/wp-content/uploads/' => $uploads_url,
        'https://staging.floorstoday.ca/floorstodaystore/wp-content/uploads/' => $uploads_url,
        'https://staging.floorstoday.ca/floorstodayfinal/wp-content/uploads/' => $uploads_url,
        '/floorstodaystore/wp-content/uploads/' => $uploads_url,
        '/floorstodayfinal/wp-content/uploads/' => $uploads_url,
        '/floorstodayhome/wp-content/uploads/' => $uploads_url,

        'http://localhost/floorstodaystore/public/' => $public_url,
        'https://localhost/floorstodaystore/public/' => $public_url,
        'http://localhost/floorstodayhome/public/' => $public_url,
        'https://localhost/floorstodayhome/public/' => $public_url,
        'https://floorstoday.ca/floorstodaystore/public/' => $public_url,
        'https://staging.floorstoday.ca/floorstodaystore/public/' => $public_url,
        'https://staging.floorstoday.ca/floorstodayfinal/public/' => $public_url,
        'https://staging.floorstoday.ca/public/' => $public_url,
        'https://floorstoday.ca/public/' => $public_url,
        '/floorstodaystore/public/' => $public_url,
        '/floorstodayfinal/public/' => $public_url,
        '/floorstodayhome/public/' => $public_url,
        '/floorstoday/public/' => $public_url,
        '/floorstest/' => $public_url,

        'http://localhost/floorstodaystore/' => $home_url,
        'https://localhost/floorstodaystore/' => $home_url,
        'http://localhost/floorstodayhome/' => $home_url,
        'https://localhost/floorstodayhome/' => $home_url,
    ];

    foreach ($replacements as $search => $replace) {
        if (substr($value, 0, strlen($search)) === $search) {
            return ft_next_homepage_normalize_category_urls($replace . substr($value, strlen($search)));
        }
    }

    return ft_next_homepage_normalize_category_urls($value);
}

function ft_next_homepage_runtime_asset_path() {
    $request_path = (string) wp_parse_url(wp_unslash($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    $request_base = trailingslashit($request_path ?: '/');

    return $request_base . 'public/';
}

function ft_next_homepage_normalize_site_links($html) {
    $home_path = (string) wp_parse_url(home_url('/'), PHP_URL_PATH);
    $home_path = trailingslashit($home_path !== '' ? $home_path : '/');

    return preg_replace_callback('/(<a\b[^>]*\bhref=)(["\'])(\/(?!\/)[^"\']*)\2/i', function ($matches) use ($home_path) {
        $url = $matches[3];

        if (
            str_starts_with($url, $home_path) ||
            preg_match('#^/(?:wp-admin|wp-content|wp-includes|wp-json|public|_next)(?:/|$)#i', $url)
        ) {
            return $matches[0];
        }

        $normalized_url = $url === '/' ? $home_path : $home_path . ltrim($url, '/');

        return $matches[1] . $matches[2] . esc_url($normalized_url) . $matches[2];
    }, $html);
}
function ft_next_homepage_normalize_static_html($html) {
    $html = preg_replace('#https?://localhosthttps?://localhost/#i', 'http://localhost/', $html);

    $runtime_asset_path = ft_next_homepage_runtime_asset_path();
    $uploads_url = 'https://floors-today-media.s3.amazonaws.com/wp-content/uploads/';
    $asset_placeholder = '%%FT_NEXT_PUBLIC_PATH%%';
    $uploads_placeholder = '%%FT_NEXT_UPLOADS_PATH%%';
    $s3_uploads_placeholder = '%%FT_NEXT_S3_UPLOADS_PATH%%';

    $html = str_replace(
        [
            'https://floors-today-media.s3.amazonaws.comhttps://floors-today-media.s3.amazonaws.com/wp-content/uploads/',
            'https://floors-today-media.s3.amazonaws.comhttps//floors-today-media.s3.amazonaws.com/wp-content/uploads/',
            $uploads_url,
        ],
        $s3_uploads_placeholder,
        $html
    );

    $html = str_replace(
        [
            'https://floorstoday.ca/floorstodaystore/public/',
            'https://staging.floorstoday.ca/public/',
            'https://floorstoday.ca/public/',
            '/floorstodaystore/public/',
            '/floorstodayfinal/public/',
            '/floorstodayhome/public/',
            '/floorstoday/public/',
            '/public/',
        ],
        $asset_placeholder,
        $html
    );

    $html = str_replace(
        [
            'http://localhost/floorstodaystore/wp-content/uploads/',
            'https://localhost/floorstodaystore/wp-content/uploads/',
            '/floorstodaystore/wp-content/uploads/',
            '/floorstodayfinal/wp-content/uploads/',
            '/floorstodayhome/wp-content/uploads/',
            '/wp-content/uploads/',
        ],
        $uploads_placeholder,
        $html
    );

    $html = ft_next_homepage_normalize_category_urls($html);

    $html = str_replace(
        [$asset_placeholder, $uploads_placeholder, $s3_uploads_placeholder],
        [$runtime_asset_path, $uploads_url, $uploads_url],
        $html
    );

    $runtime_parent_path = trailingslashit(dirname(untrailingslashit($runtime_asset_path)));
    $duplicated_runtime_path = $runtime_parent_path . ltrim($runtime_asset_path, '/');
    while ($duplicated_runtime_path !== $runtime_asset_path && str_contains($html, $duplicated_runtime_path)) {
        $html = str_replace($duplicated_runtime_path, $runtime_asset_path, $html);
    }

    $html = preg_replace('#https?://localhosthttps?://localhost/#i', 'http://localhost/', $html);

    $html = ft_next_homepage_normalize_site_links($html);

    return $html;
}

function ft_next_homepage_normalize_static_head_html($html) {
    return preg_replace_callback('/<head\b[^>]*>.*?<\/head>/is', function ($matches) {
        return ft_next_homepage_normalize_static_html($matches[0]);
    }, $html, 1);
}
function ft_next_homepage_attribution_script() {
    return '<script id="ft-attribution-tracker">(function(){'
        . 'if(window.ftGetAttribution)return;'
        . 'var key="ft_attribution_v1";'
        . 'var maxAge=90*24*60*60*1000;'
        . 'var socialHosts={'
        . '"facebook.com":"facebook","m.facebook.com":"facebook","l.facebook.com":"facebook","lm.facebook.com":"facebook","instagram.com":"instagram","l.instagram.com":"instagram","tiktok.com":"tiktok","vm.tiktok.com":"tiktok","linkedin.com":"linkedin","lnkd.in":"linkedin","youtube.com":"youtube","youtu.be":"youtube","pinterest.com":"pinterest","x.com":"x","twitter.com":"x"'
        . '};'
        . 'function read(){try{var raw=localStorage.getItem(key);if(!raw)return null;var data=JSON.parse(raw);if(!data.createdAt||Date.now()-data.createdAt>maxAge){localStorage.removeItem(key);return null;}return data;}catch(e){return null;}}'
        . 'function host(url){try{return new URL(url).hostname.replace(/^www\./,"").toLowerCase();}catch(e){return "";}}'
        . 'function socialSource(refHost){if(!refHost)return "";if(socialHosts[refHost])return socialHosts[refHost];var keys=Object.keys(socialHosts);for(var i=0;i<keys.length;i++){if(refHost.endsWith("."+keys[i]))return socialHosts[keys[i]];}return "";}'
        . 'function current(){var pageUrl=new URL(window.location.href);var params=pageUrl.searchParams;var ref=document.referrer||"";var refHost=host(ref);var ownHost=host(window.location.href);if(refHost===ownHost)refHost="";var utmSource=params.get("hello_social")||params.get("utm_source")||"";var data={createdAt:Date.now(),pageUrl:window.location.href,landingPage:window.location.href,referrerUrl:ref,referrerHost:refHost,utmSource:utmSource,utmMedium:params.get("utm_medium")||"",utmCampaign:params.get("utm_campaign")||"",utmContent:params.get("utm_content")||"",utmTerm:params.get("utm_term")||"",trafficSource:utmSource||socialSource(refHost)||refHost||"Direct"};return data;}'
        . 'function capture(){var existing=read();var data=current();var hasCampaign=data.utmSource||data.utmMedium||data.utmCampaign||data.utmContent||data.utmTerm||data.referrerHost;if(!existing&&hasCampaign){try{localStorage.setItem(key,JSON.stringify(data));}catch(e){}return data;}return existing||data;}'
        . 'function decorateLinks(){}'
        . 'window.ftGetAttribution=function(){return capture();};'
        . 'capture();'
        . 'if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",decorateLinks);}else{decorateLinks();}'
        . 'window.addEventListener("load",decorateLinks);'
        . 'setTimeout(decorateLinks,800);'
        . '})();</script>' . "\n";
}

add_action('wp_head', function () {
    if (!is_admin()) {
        echo ft_next_homepage_attribution_script();
    }
}, 1);

function ft_next_header_utility_links($settings) {
    $links = [];

    if (!is_array($settings['utility_links'] ?? null)) {
        return $links;
    }

    foreach ($settings['utility_links'] as $item) {
        $name = trim((string) ($item['name'] ?? ''));
        $href = ft_next_header_shortcode_url((string) ($item['href'] ?? ''));

        if ($name !== '' && $href !== '#') {
            $links[] = [
                'name' => $name,
                'href' => $href,
            ];
        }
    }

    return $links;
}
function ft_next_homepage_shell_style_vars($settings) {
    $vars = [
        '--primary' => $settings['primary_color'] ?? '',
        '--ring' => $settings['primary_color'] ?? '',
        '--chart-1' => $settings['primary_color'] ?? '',
        '--sidebar-primary' => $settings['primary_color'] ?? '',
        '--sidebar-ring' => $settings['primary_color'] ?? '',
        '--secondary' => $settings['secondary_color'] ?? '',
        '--accent' => $settings['secondary_color'] ?? '',
        '--chart-2' => $settings['secondary_color'] ?? '',
        '--background' => $settings['background_color'] ?? '',
        '--foreground' => $settings['foreground_color'] ?? '',
        '--ft-button-radius' => $settings['button_radius'] ?? '',
        '--ft-button-font-weight' => $settings['button_font_weight'] ?? '',
        '--ft-button-text-transform' => $settings['button_text_transform'] ?? '',
        '--ft-button-padding-x' => $settings['button_padding_x'] ?? '',
        '--ft-button-padding-y' => $settings['button_padding_y'] ?? '',
        '--ft-button-hover-mix' => $settings['button_hover_mix'] ?? '',
        '--ft-button-border-width' => $settings['button_border_width'] ?? '',
        '--ft-button-border-style' => $settings['button_border_style'] ?? '',
        '--ft-button-border-color' => $settings['button_border_color'] ?? '',
        '--ft-hero-badge-bg' => $settings['hero_badge_bg_color'] ?? '',
        '--ft-hero-badge-text' => $settings['hero_badge_text_color'] ?? '',
        '--ft-hero-badge-font-size' => $settings['hero_badge_font_size'] ?? '',
        '--ft-hero-badge-padding-x' => $settings['hero_badge_padding_x'] ?? '',
        '--ft-hero-badge-padding-y' => $settings['hero_badge_padding_y'] ?? '',
        '--ft-hero-badge-animation-color-1' => $settings['hero_badge_animation_color_1'] ?? '',
        '--ft-hero-badge-animation-color-2' => $settings['hero_badge_animation_color_2'] ?? '',
        '--ft-hero-badge-animation-location' => $settings['hero_badge_animation_location'] ?? '',
        '--ft-hero-badge-animation-speed' => $settings['hero_badge_animation_speed'] ?? '',
    ];

    $pairs = [];
    foreach ($vars as $name => $value) {
        if ($value === '') {
            continue;
        }
        $pairs[] = $name . ':' . $value;
    }

    return implode(';', $pairs);
}

function ft_next_homepage_runtime_bridge($settings) {
    $hero_badge_font_size = ft_next_homepage_css_length($settings['hero_badge_font_size'] ?? '', '16px');
    $hero_badge_mobile_font_size = ft_next_homepage_css_length($settings['hero_badge_mobile_font_size'] ?? '', '14px');
    $hero_badge_padding_x = ft_next_homepage_css_length($settings['hero_badge_padding_x'] ?? '', '16px');
    $hero_badge_padding_y = ft_next_homepage_css_length($settings['hero_badge_padding_y'] ?? '', '8px');
    $hero_badge_bg = (string) ($settings['hero_badge_bg_color'] ?? '#cc9c2e');
    $hero_badge_text = (string) ($settings['hero_badge_text_color'] ?? '#ffffff');
    $hero_title_font_size = ft_next_homepage_css_length($settings['hero_title_font_size'] ?? '', '60px');
    $hero_title_mobile_font_size = ft_next_homepage_css_length($settings['hero_title_mobile_font_size'] ?? '', '30px');
    $hero_background_image = ft_next_homepage_normalize_saved_url((string) ($settings['hero_image'] ?? ''));
    $category_links = [];
    $nav_items = [];
    $phone = (string) ($settings['phone'] ?? '');
    $phone_href = preg_replace('/[^0-9+]/', '', $phone);

    if (is_array($settings['categories'] ?? null)) {
        foreach ($settings['categories'] as $category) {
            $slug = sanitize_title((string) ($category['slug'] ?? ''));
            $url = ft_next_homepage_category_url($slug);

            if ($slug !== '' && $url !== '') {
                $category_links[$slug] = $url;
            }
        }
    }

    if (is_array($settings['nav_items'] ?? null)) {
        foreach ($settings['nav_items'] as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            $href = ft_next_header_shortcode_url((string) ($item['href'] ?? ''));

            if ($name !== '' && $href !== '#') {
                $nav_items[] = [
                    'name' => $name,
                    'href' => $href,
                ];
            }
        }
    }

    $utility_links = ft_next_header_utility_links($settings);

    $bridge_settings = array_merge($settings, [
        'hero_badge' => (string) ($settings['hero_badge'] ?? ''),
        'hero_badge_font_size' => $hero_badge_font_size,
        'hero_badge_mobile_font_size' => $hero_badge_mobile_font_size,
        'hero_badge_padding_x' => $hero_badge_padding_x,
        'hero_badge_padding_y' => $hero_badge_padding_y,
        'category_links' => $category_links,
        'nav_items' => $nav_items,
        'utility_links' => $utility_links,
        'phone' => $phone,
        'phone_href' => $phone_href !== '' ? 'tel:' . $phone_href : '',
        'deals_badge' => (string) ($settings['deals_badge'] ?? ''),
        'deals_card_title' => (string) ($settings['deals_card_title'] ?? ''),
        'deals_card_subtitle' => (string) ($settings['deals_card_subtitle'] ?? ''),
        'guarantee_image' => (string) ($settings['guarantee_image'] ?? ''),
        'guarantee_title' => (string) ($settings['guarantee_title'] ?? ''),
    ]);

    return ft_next_homepage_attribution_script()
        . '<style id="ft-homepage-runtime-bridge">'
        . '.ft-homepage-shell .ft-hero-badge{'
        . 'display:inline-flex!important;'
        . 'font-size:' . esc_attr($hero_badge_font_size) . '!important;'
        . 'padding-inline:' . esc_attr($hero_badge_padding_x) . '!important;'
        . 'padding-block:' . esc_attr($hero_badge_padding_y) . '!important;'
        . 'background-color:' . esc_attr($hero_badge_bg) . '!important;'
        . 'color:' . esc_attr($hero_badge_text) . '!important;'
        . 'line-height:1.2!important;width:auto!important;max-width:100%;height:auto!important;min-height:0!important;white-space:normal!important;'
        . '}'
        . '.ft-homepage-shell section[aria-labelledby="deals-heading"] [data-slot="badge"]{'
        . 'font-size:14px!important;line-height:1.35!important;white-space:normal!important;height:auto!important;max-width:100%;'
        . '}'
        . '.ft-homepage-shell [class*="max-w-[1340px]"],.ft-homepage-shell>header>.bg-primary>div,.ft-homepage-shell>header>nav,.ft-homepage-shell main>section:first-child>div.relative{max-width:1340px!important;}'
        . '.ft-homepage-shell section[aria-labelledby="process-heading"] article>div:first-child>.absolute{top:16px!important;left:16px!important;}'
        . '.ft-homepage-shell section[aria-labelledby="process-heading"] article>div:first-child>.absolute>div{'
        . 'width:auto!important;height:auto!important;min-width:0!important;min-height:0!important;'
        . 'padding:7px 13px!important;border-radius:999px!important;background:#cc9c2e!important;'
        . 'color:#fff!important;font-size:13px!important;font-weight:700!important;line-height:1!important;'
        . 'box-shadow:0 10px 24px rgba(0,0,0,.18)!important;letter-spacing:0!important;'
        . '}'
        . '.ft-homepage-shell main>section:first-child h1+p+div.grid span{font-size:17px!important;}'
        . '@media(min-width:641px){body:not(.admin-bar):has(.ft-homepage-shell>header){margin-top:0!important;padding-top:0!important;}.ft-homepage-shell>header{top:0!important;margin-top:0!important;}.ft-homepage-shell main>section:first-child h1{font-size:' . esc_attr($hero_title_font_size) . '!important;}}'
        . '.ft-homepage-shell .ft-runtime-mobile-menu{display:none;background:#fff;border-top:1px solid #e5e7eb;padding:8px 16px 16px;box-shadow:0 10px 20px rgba(0,0,0,.08);}'
        . '.ft-homepage-shell .ft-runtime-mobile-menu.is-open{display:block;}'
        . '.ft-homepage-shell .ft-runtime-mobile-menu a{display:block;padding:10px 0;color:var(--foreground);font-weight:600;text-decoration:none;}'
        . '.ft-homepage-shell .ft-runtime-mobile-menu a:hover,.ft-homepage-shell .ft-runtime-mobile-menu a:focus-visible{color:var(--primary);}'
        . '.ft-homepage-shell main>section:first-child>div.absolute.inset-0{background-image:url("' . esc_url($hero_background_image) . '")!important;background-size:cover!important;background-position:58% center!important;background-repeat:no-repeat!important;}'
        . '.ft-homepage-shell main>section:first-child>div.absolute.inset-0>img{display:none!important;}'
        . '@media(max-width:1100px){'
        . '.ft-homepage-shell main>section:first-child>div.relative>div.grid{grid-template-columns:minmax(0,1fr)!important;width:100%!important;max-width:none!important;margin-inline:0!important;}'
        . '.ft-homepage-shell main>section:first-child>div.relative>div.grid>div:first-child{width:100%!important;max-width:none!important;}'
        . '.ft-homepage-shell #estimate{width:100%!important;max-width:100%!important;justify-self:stretch!important;margin-inline:0!important;}'
        . '}'
        . '@media(min-width:1025px){.ft-homepage-shell .ft-runtime-mobile-menu{display:none!important;}}'
        . '@media(min-width:641px) and (max-width:1024px){'
        . '.ft-homepage-shell main>section:first-child h1+p+div.grid span{font-size:20px!important;}'
        . '}'
        . '@media(max-width:640px){'
        . 'body:has(.ft-homepage-shell>header){padding-top:112px!important;}'
        . '.ft-homepage-shell>header{position:fixed!important;top:0!important;left:0!important;right:0!important;z-index:1000!important;width:100%!important;}'
        . '.admin-bar .ft-homepage-shell>header{top:46px!important;}'
        . '.ft-homepage-shell>header>.bg-primary .min-h-10{min-height:46px!important;padding-block:5px!important;align-items:center!important;}'
        . '.ft-homepage-shell>header>.bg-primary .truncate{white-space:normal!important;overflow:visible!important;text-overflow:clip!important;display:-webkit-box!important;-webkit-line-clamp:2!important;-webkit-box-orient:vertical!important;line-height:1.18!important;font-size:13.5px!important;max-width:calc(100vw - 52px)!important;}'
        . '.ft-homepage-shell>header nav img{width:230px!important;max-width:230px!important;max-height:66px!important;}'
        . '.ft-homepage-shell>header nav .min-h-16{min-height:66px!important;}'
        . '.ft-homepage-shell main>section:first-child h1{font-size:' . esc_attr($hero_title_mobile_font_size) . '!important;line-height:1.08!important;}'
        . '.ft-homepage-shell main>section:first-child h1+p+div.grid span{font-size:15px!important;}'
        . '.ft-homepage-shell #estimate{width:calc(100vw - 24px)!important;max-width:calc(100vw - 24px)!important;justify-self:center!important;margin-inline:auto!important;position:relative!important;left:50%!important;transform:translateX(-50%)!important;}'
        . '.ft-homepage-shell .ft-hero-badge{display:flex!important;width:100%!important;max-width:100%!important;justify-content:flex-start;text-align:left;font-size:' . esc_attr($hero_badge_mobile_font_size) . '!important;}'
        . '.ft-homepage-shell section[aria-labelledby="deals-heading"] [data-slot="badge"]{display:inline-flex!important;width:100%;justify-content:center;text-align:center;padding:4px 10px!important;}'
        . '.ft-homepage-shell section[aria-labelledby="process-heading"] article>div:first-child>.absolute{top:12px!important;left:12px!important;}'
        . '.ft-homepage-shell section[aria-labelledby="process-heading"] article>div:first-child>.absolute>div{font-size:12px!important;padding:6px 11px!important;}'
        . '}'
        . '</style>'
        . '<script id="ft-homepage-runtime-bridge-script">(function(){'
        . 'var s=' . wp_json_encode($bridge_settings, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) . ';'
        . 'function text(el,value){if(el&&value)el.textContent=value;}'
        . 'function styleHeroBadge(){var el=document.querySelector(".ft-homepage-shell .ft-hero-badge");if(!el)return;var mobile=window.matchMedia("(max-width:640px)").matches;el.style.setProperty("display",mobile?"flex":"inline-flex","important");el.style.setProperty("font-size",mobile?s.hero_badge_mobile_font_size:s.hero_badge_font_size,"important");el.style.setProperty("width",mobile?"100%":"auto","important");el.style.setProperty("max-width","100%","important");el.style.setProperty("height","auto","important");el.style.setProperty("min-height","0","important");el.style.setProperty("padding-inline",s.hero_badge_padding_x||"16px","important");el.style.setProperty("padding-block",s.hero_badge_padding_y||"8px","important");}'
        . 'function updateHeaderLogo(){var logo=s.logo_image||"";var title=s.logo_text||"Floors Today";var mobile=window.matchMedia("(max-width:640px)").matches;var links=document.querySelectorAll(".ft-homepage-shell header a");links.forEach(function(link){if(link.querySelector("img"))link.setAttribute("aria-label",title);});document.querySelectorAll(".ft-homepage-shell header img").forEach(function(img){if(logo&&img.getAttribute("src")!==logo){img.setAttribute("src",logo);}if(logo){img.removeAttribute("srcset");img.removeAttribute("sizes");}if(img.getAttribute("alt")!==title)img.setAttribute("alt",title);var width=mobile?"230px":s.logo_size;if(width&&img.style.getPropertyValue("width")!==width)img.style.setProperty("width",width,"important");if(mobile){img.style.setProperty("max-width","230px","important");img.style.setProperty("max-height","66px","important");}});}'
        . 'function updateHeaderServiceArea(){var header=document.querySelector(".ft-homepage-shell header");if(!header)return;var value=s.service_area||"";var topbar=header.querySelector(".bg-primary")||header.firstElementChild;if(!topbar)return;var spans=[].slice.call(topbar.querySelectorAll("span"));var target=spans[0];if(target)target.textContent=value;}'
        . 'function updateGuaranteeImage(){var img=document.querySelector(".ft-homepage-shell section[aria-labelledby=\"guarantee-heading\"] img");if(!img||!s.guarantee_image)return;img.setAttribute("src",s.guarantee_image);img.removeAttribute("srcset");img.removeAttribute("sizes");if(s.guarantee_title)img.setAttribute("alt",s.guarantee_title);}'
        . 'function updateHomeContent(){var q=function(sel){return document.querySelector(sel)};text(q(".ft-homepage-shell #categories-heading"),s.category_title);text(q(".ft-homepage-shell #categories-heading+p"),s.category_text);text(q(".ft-homepage-shell #process-heading"),s.process_title);text(q(".ft-homepage-shell #process-heading+p"),s.process_text);text(q(".ft-homepage-shell #comparison-heading"),s.comparison_title);text(q(".ft-homepage-shell #comparison-heading+p"),s.comparison_text);text(q(".ft-homepage-shell #cta-heading"),s.cta_title);text(q(".ft-homepage-shell #cta-heading+p"),s.cta_subtitle);text(q(".ft-homepage-shell #cta-heading+p+p"),s.cta_text);text(q(".ft-homepage-shell #guarantee-heading"),s.guarantee_title);text(q(".ft-homepage-shell #guarantee-heading+p"),s.guarantee_subtitle);text(q(".ft-homepage-shell #guarantee-heading+p+p"),s.guarantee_text);var ga=q(".ft-homepage-shell section[aria-labelledby=\"guarantee-heading\"] a");if(ga&&s.guarantee_link){var svg=ga.querySelector("svg");ga.textContent=s.guarantee_link+" ";if(svg)ga.appendChild(svg);}text(q(".ft-homepage-shell #deals-heading"),s.deals_title);text(q(".ft-homepage-shell #deals-heading+p"),s.deals_text);text(q(".ft-homepage-shell #testimonials-heading"),s.testimonials_title);text(q(".ft-homepage-shell #testimonials-heading+p"),s.testimonials_text);(s.process_steps||[]).forEach(function(item,i){var card=document.querySelectorAll(".ft-homepage-shell section[aria-labelledby=\"process-heading\"] article")[i];if(!card)return;var img=card.querySelector("img");if(img&&item.image){img.src=item.image;img.removeAttribute("srcset");img.alt=item.title||img.alt;}text(card.querySelector("h3"),item.title);text(card.querySelector("p"),item.description);});(s.categories||[]).forEach(function(item,i){var card=document.querySelectorAll(".ft-homepage-shell section[aria-labelledby=\"categories-heading\"] a")[i];if(!card)return;var img=card.querySelector("img");if(img&&item.image){img.src=item.image;img.removeAttribute("srcset");img.alt=item.name||img.alt;}text(card.querySelector("h3"),item.name);text(card.querySelector("p"),item.description);});}'
        . 'function styleMobileForm(){var el=document.querySelector(".ft-homepage-shell #estimate");if(!el)return;var mobile=window.matchMedia("(max-width:640px)").matches;var stacked=window.matchMedia("(max-width:1100px)").matches;if(!stacked){["width","max-width","justify-self","margin-inline","position","left","transform"].forEach(function(p){el.style.removeProperty(p);});return;}if(mobile){el.style.setProperty("width","calc(100vw - 24px)","important");el.style.setProperty("max-width","calc(100vw - 24px)","important");el.style.setProperty("justify-self","center","important");el.style.setProperty("margin-inline","auto","important");el.style.setProperty("position","relative","important");el.style.setProperty("left","50%","important");el.style.setProperty("transform","translateX(-50%)","important");return;}el.style.setProperty("width","100%","important");el.style.setProperty("max-width","100%","important");el.style.setProperty("justify-self","stretch","important");el.style.setProperty("margin-inline","0","important");["position","left","transform"].forEach(function(p){el.style.removeProperty(p);});}'
        . 'function ensureDesktopUtilityLinks(){var header=document.querySelector(".ft-homepage-shell header");if(!header)return;var links=s.utility_links||[];var topbar=header.querySelector(".bg-primary")||header.firstElementChild;if(!topbar)return;var anchors=[].slice.call(topbar.querySelectorAll("a"));var parent=anchors.length?anchors[0].parentNode:null;if(!parent){var row=topbar.querySelector(".items-center.justify-between")||topbar.querySelector("div div div");if(!row)return;parent=document.createElement("div");parent.className="hidden sm:flex items-center gap-6";row.appendChild(parent);}while(parent.firstChild){parent.removeChild(parent.firstChild);}links.forEach(function(item){if(!item||!item.name||!item.href)return;var a=document.createElement("a");a.href=item.href;a.textContent=item.name;a.className="hover:underline";parent.appendChild(a);});}'
        . 'function ensureMobileMenu(){var header=document.querySelector(".ft-homepage-shell header");if(!header)return;var icon=header.querySelector("svg.lucide-menu");var button=icon?icon.closest("button"):null;if(!button)return;var panel=header.querySelector(".ft-runtime-mobile-menu");if(!panel){panel=document.createElement("nav");panel.className="ft-runtime-mobile-menu";panel.setAttribute("aria-label","Mobile navigation");panel.id="ft-runtime-mobile-menu";(s.nav_items||[]).forEach(function(item){var a=document.createElement("a");a.href=item.href;a.textContent=item.name;panel.appendChild(a);});(s.utility_links||[]).forEach(function(item){var a=document.createElement("a");a.href=item.href;a.textContent=item.name;panel.appendChild(a);});if(s.phone&&s.phone_href){var p=document.createElement("a");p.href=s.phone_href;p.textContent=s.phone;panel.appendChild(p);}header.appendChild(panel);}button.setAttribute("aria-controls","ft-runtime-mobile-menu");button.setAttribute("aria-expanded",panel.classList.contains("is-open")?"true":"false");if(header.dataset.ftRuntimeMenuBound==="1")return;header.dataset.ftRuntimeMenuBound="1";button.addEventListener("click",function(event){event.preventDefault();event.stopPropagation();var open=!panel.classList.contains("is-open");panel.classList.toggle("is-open",open);button.setAttribute("aria-expanded",open?"true":"false");},true);panel.addEventListener("click",function(event){if(event.target.closest("a")){panel.classList.remove("is-open");button.setAttribute("aria-expanded","false");}});}'
        . 'function linkCategories(){var links=s.category_links||{};Object.keys(links).forEach(function(slug){var el=document.querySelector(".ft-homepage-shell section[aria-labelledby=\"categories-heading\"] a#"+CSS.escape(slug));if(el){el.dataset.ftCategoryUrl=links[slug];el.setAttribute("href",links[slug]);el.setAttribute("aria-label","View "+(el.querySelector("h3")?el.querySelector("h3").textContent:slug));}});}'
        . 'function bindCategoryClicks(){var section=document.querySelector(".ft-homepage-shell section[aria-labelledby=\"categories-heading\"]");if(!section||section.dataset.ftClicksBound==="1")return;section.dataset.ftClicksBound="1";section.addEventListener("click",function(event){var card=event.target.closest("a[data-ft-category-url]");if(!card)return;event.preventDefault();event.stopPropagation();window.location.href=card.dataset.ftCategoryUrl;},true);}'
        . 'function watchHeaderLogo(){var header=document.querySelector(".ft-homepage-shell header");if(!header||header.dataset.ftLogoWatch==="1")return;header.dataset.ftLogoWatch="1";new MutationObserver(function(){updateHeaderLogo();}).observe(header,{childList:true,subtree:true,attributes:true,attributeFilter:["src","srcset","sizes","alt","style"]});}'
        . 'function apply(){'
        . 'text(document.querySelector(".ft-homepage-shell .ft-hero-badge"),s.hero_badge);'
        . 'styleHeroBadge();'
        . 'updateHeaderLogo();'
        . 'updateHeaderServiceArea();'
        . 'watchHeaderLogo();'
        . 'updateGuaranteeImage();'
        . 'updateHomeContent();'
        . 'styleMobileForm();'
        . 'ensureDesktopUtilityLinks();'
        . 'ensureMobileMenu();'
        . 'linkCategories();'
        . 'bindCategoryClicks();'
        . 'text(document.querySelector(".ft-homepage-shell section[aria-labelledby=\"deals-heading\"] [data-slot=\"badge\"]"),s.deals_badge);'
        . 'var sale=document.querySelector(".ft-homepage-shell .ft-sale-badge");'
        . 'text(sale,s.deals_card_subtitle);'
        . 'if(sale&&sale.previousElementSibling&&s.deals_card_title)text(sale.previousElementSibling,s.deals_card_title);'
        . 'document.querySelectorAll(".ft-homepage-shell section[aria-labelledby=\"process-heading\"] article>div:first-child>.absolute>div").forEach(function(el,i){el.textContent="Step "+(i+1);});'
        . '}'
        . 'function scheduleApply(){setTimeout(apply,250);setTimeout(apply,1000);}'
        . 'if(document.readyState==="complete"){scheduleApply();}else{window.addEventListener("load",scheduleApply,{once:true});}window.addEventListener("resize",function(){styleHeroBadge();styleMobileForm();updateHeaderLogo();});'
        . '})();</script>' . "\n";
}

function ft_next_clean_text($value) {
    return sanitize_text_field(wp_unslash($value));
}

function ft_next_clean_textarea($value) {
    return wp_kses_post(wp_unslash($value));
}

function ft_next_clean_menu_url($value) {
    $value = trim(ft_next_clean_text($value));

    if ($value === '' || $value === '#') {
        return '#';
    }

    if (
        str_starts_with($value, '#') ||
        str_starts_with($value, '/') ||
        preg_match('#^(?:https?:|mailto:|tel:)#i', $value)
    ) {
        return $value;
    }

    return '/' . ltrim($value, '/');
}

function ft_next_clean_color($value) {
    $value = trim((string) wp_unslash($value));
    return preg_match('/^[a-zA-Z0-9#.,%() \/-]+$/', $value) ? $value : '';
}

function ft_next_clean_lines($value) {
    $lines = preg_split('/\r\n|\r|\n/', (string) wp_unslash($value));
    return array_values(array_filter(array_map('sanitize_text_field', $lines)));
}

function ft_next_sendy_defaults() {
    return [
        'enabled' => '0',
        'url' => '',
        'api_key' => '',
        'list_id' => '',
    ];
}

function ft_next_sendy_settings() {
    $saved = get_option(FT_NEXT_SENDY_OPTION, []);
    return array_replace(ft_next_sendy_defaults(), is_array($saved) ? $saved : []);
}

function ft_next_sendy_subscribe($email, $name = '', $phone = '', $referrer = '') {
    $settings = ft_next_sendy_settings();

    if (($settings['enabled'] ?? '0') !== '1') {
        return new WP_Error('ft_sendy_disabled', 'Newsletter signup is not configured.', ['status' => 503]);
    }

    $sendy_url = esc_url_raw((string) ($settings['url'] ?? ''));
    $api_key = trim((string) ($settings['api_key'] ?? ''));
    $list_id = trim((string) ($settings['list_id'] ?? ''));

    if ($sendy_url === '' || $api_key === '' || $list_id === '') {
        return new WP_Error('ft_sendy_missing_settings', 'Newsletter signup is missing Sendy settings.', ['status' => 503]);
    }

    $email = sanitize_email($email);
    if ($email === '' || !is_email($email)) {
        return new WP_Error('ft_sendy_invalid_email', 'Please enter a valid email address.', ['status' => 400]);
    }

    $phone = sanitize_text_field($phone);
    $endpoint = trailingslashit($sendy_url) . 'subscribe';
    $body = [
        'api_key' => $api_key,
        'list' => $list_id,
        'email' => $email,
        'name' => sanitize_text_field($name),
        'boolean' => 'true',
        'gdpr' => 'true',
        'hp' => '',
        'referrer' => esc_url_raw($referrer ?: home_url('/')),
    ];

    if ($phone !== '') {
        $body['Phone'] = $phone;
        $body['phone'] = $phone;
    }

    $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
    if ($ip !== '') {
        $body['ipaddress'] = $ip;
    }

    $response = wp_remote_post($endpoint, [
        'timeout' => 15,
        'body' => $body,
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('ft_sendy_request_failed', 'Newsletter signup could not reach Sendy.', ['status' => 502]);
    }

    $status = (int) wp_remote_retrieve_response_code($response);
    $message = trim((string) wp_remote_retrieve_body($response));
    $normalized = strtolower($message);

    if ($status >= 200 && $status < 300 && ($normalized === 'true' || $message === '1' || $normalized === 'already subscribed.' || $normalized === 'already subscribed')) {
        return true;
    }

    return new WP_Error('ft_sendy_rejected', $message !== '' ? $message : 'Newsletter signup was rejected by Sendy.', ['status' => 400]);
}
add_action('admin_menu', function () {
    add_menu_page(
        'Next Homepage',
        'Next Homepage',
        'manage_options',
        'ft-next-homepage',
        'ft_next_homepage_render_admin',
        'dashicons-admin-home',
        58
    );
    add_submenu_page(
        'ft-next-homepage',
        'Social UTM Links',
        'Social UTM Links',
        'manage_options',
        'ft-next-homepage-utm',
        'ft_next_homepage_render_utm_admin'
    );
});

// Elementor Theme Builder previews use query-string URLs for elementor_library
// posts. WordPress canonical redirects can otherwise send the preview iframe
// back to the static homepage before Elementor renders the document.
add_filter('redirect_canonical', function ($redirect_url) {
    if (isset($_GET['elementor-preview'])) {
        return false;
    }

    return $redirect_url;
});

// Next.js's client router fetches RSC prefetch/cache-refresh payloads
// (__next*.txt, index.txt) relative to the current page URL, i.e. at the
// site root - not under the /public asset basePath, even though this site
// exports with basePath=/public. Confirmed via real 404s in production
// console logs (GET /__next._index.txt, /__next._head.txt, etc.) that
// broke without this. Serve them from public/ when requested at root.
add_action('template_redirect', function () {
    $request_path = (string) wp_parse_url(wp_unslash($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    $basename     = basename($request_path);

    if (!preg_match('/^(?:__next[._]|__PAGE__|index\.txt$)/i', $basename)) {
        return;
    }

    $file = trailingslashit(ABSPATH) . 'public/' . $basename;

    if (!is_readable($file)) {
        return;
    }

    $content = file_get_contents($file);
    if ($content === false) {
        return;
    }

    status_header(200);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('X-Robots-Tag: noindex');
    echo $content;
    exit;
}, 5);

add_action('template_redirect', function () {
    if (!is_front_page() || is_admin() || wp_doing_ajax() || wp_is_json_request() || isset($_GET['elementor-preview']) || is_preview() || is_customize_preview()) {
        return;
    }

    $homepage_file = trailingslashit(ABSPATH) . 'public/index.html';

    if (!is_readable($homepage_file)) {
        return;
    }

    $html = file_get_contents($homepage_file);

    if ($html === false) {
        return;
    }

    // Derive URLs from the current request/site instead of trusting paths baked
    // into the static Next export.
    $html = ft_next_homepage_normalize_static_html($html);

    $settings = ft_next_homepage_settings();

    // Colors/sizes must always reflect the current WP admin settings, not
    // whatever was live when the static export was last built.
    $shell_style = ft_next_homepage_shell_style_vars($settings);
    $html = preg_replace(
        '/(class="ft-homepage-shell"[^>]*\sstyle=")[^"]*(")/i',
        '$1' . str_replace('$', '\\$', esc_attr($shell_style)) . '$2',
        $html,
        1
    );

    $seo_title = $settings['seo_title'];
    $seo_description = $settings['seo_description'];
    $seo_canonical = $settings['seo_canonical_url'] ?: home_url('/');
    $seo_robots = $settings['seo_robots'];
    $seo_og_title = $settings['seo_og_title'] ?: $seo_title;
    $seo_og_description = $settings['seo_og_description'] ?: $seo_description;
    $seo_og_image = $settings['seo_og_image'] ?: $settings['hero_image'];
    // Parse src and id from the embed code and pass them to React as clean values.
    $settings_for_react = $settings;
    $embed_raw = trim($settings['testimonials_embed_code'] ?? '');
    $embed_src = '';
    $embed_id  = '';
    if ($embed_raw !== '') {
        if (preg_match('/\bsrc=["\']([^"\']+)["\']/', $embed_raw, $m)) {
            $embed_src = esc_url_raw($m[1]);
        }
        if (preg_match('/\bid=["\']([^"\']+)["\']/', $embed_raw, $m)) {
            $embed_id = sanitize_text_field($m[1]);
        }
    }
    $settings_for_react['testimonials_embed_code'] = $embed_src !== '' ? '1' : '';
    $settings_for_react['testimonials_script_src'] = $embed_src;
    $settings_for_react['testimonials_script_id']  = $embed_id;
    // Convert chat embed code to a safe flag for JSON; parsed URL/ID already in $settings_for_react via $settings
    $settings_for_react['chat_embed_code'] = ($settings['chat_script_src'] ?? '') !== '' ? '1' : '';
    // fb_pixel_id and recaptcha_site_key are plain text — already safe to pass through as-is
    $initial_settings = wp_json_encode(
        $settings_for_react,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
    );

    $html = preg_replace('/<title>.*?<\/title>/is', '<title>' . esc_html($seo_title) . '</title>', $html, 1);
    $html = preg_replace('/<meta\s+name="description"[^>]*>/i', '', $html);
    $html = preg_replace('/<meta\s+name="keywords"[^>]*>/i', '', $html);
    $html = preg_replace('/<meta\s+name="robots"[^>]*>/i', '', $html);
    $html = preg_replace('/<meta\s+name="twitter:[^"]+"[^>]*>/i', '', $html);
    $html = preg_replace('/<meta\s+property="og:[^"]+"[^>]*>/i', '', $html);
    $html = preg_replace('/<link\s+rel="canonical"[^>]*>/i', '', $html);
    $html = preg_replace('/<link(?=[^>]*\brel=["\']preload["\'])(?=[^>]*\bas=["\']image["\'])(?=[^>]*\bhref=["\'][^"\']*wp-content\/uploads\/)[^>]*>/i', '', $html);

    $ncta_html = ft_next_newsletter_cta_shortcode(['title_color' => '#000000', 'subtitle_color' => '#334155']);
    $ncta_json = wp_json_encode($ncta_html, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

    $seo_tags = "\n"
        . '<script>window.__FT_HOMEPAGE_SETTINGS__=' . $initial_settings . ';window.__FT_NCTA_HTML__=' . $ncta_json . ';</script>' . "\n"
        . ($settings['logo_image']
            ? '<link rel="preload" as="image" href="' . esc_url($settings['logo_image']) . '" fetchpriority="high">' . "\n"
            : '')
        . '<meta name="description" content="' . esc_attr($seo_description) . '">' . "\n"
        . '<meta name="robots" content="' . esc_attr($seo_robots) . '">' . "\n"
        . '<link rel="canonical" href="' . esc_url($seo_canonical) . '">' . "\n"
        . '<meta property="og:type" content="website">' . "\n"
        . '<meta property="og:locale" content="en_CA">' . "\n"
        . '<meta property="og:site_name" content="' . esc_attr($settings['logo_text']) . '">' . "\n"
        . '<meta property="og:title" content="' . esc_attr($seo_og_title) . '">' . "\n"
        . '<meta property="og:description" content="' . esc_attr($seo_og_description) . '">' . "\n"
        . '<meta property="og:url" content="' . esc_url($seo_canonical) . '">' . "\n"
        . '<meta property="og:image" content="' . esc_url($seo_og_image) . '">' . "\n"
        . '<meta name="twitter:card" content="summary_large_image">' . "\n"
        . '<meta name="twitter:title" content="' . esc_attr($seo_og_title) . '">' . "\n"
        . '<meta name="twitter:description" content="' . esc_attr($seo_og_description) . '">' . "\n"
        . '<meta name="twitter:image" content="' . esc_url($seo_og_image) . '">' . "\n";

    $gplaces_key = sanitize_text_field($settings['google_places_api_key'] ?? '');
    if ($gplaces_key) {
        $seo_tags .= '<script>window.__ftPlacesScript=true;window.__ftPlacesReady=function(){window.__ftPlacesLoaded=true;document.dispatchEvent(new CustomEvent(\'ft:places:ready\'));};</script>' . "\n";
        $seo_tags .= '<script async src="https://maps.googleapis.com/maps/api/js?key=' . esc_attr($gplaces_key) . '&libraries=places&callback=__ftPlacesReady"></script>' . "\n";
    }

    $html = str_replace('</head>', $seo_tags . ft_next_homepage_runtime_bridge($settings) . '</head>', $html);

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $settings['logo_text'],
        'description' => $seo_description,
        'url' => $seo_canonical,
        'telephone' => $settings['phone'],
        'email' => $settings['email'],
        'image' => $seo_og_image,
        'areaServed' => $settings['service_area'],
        'priceRange' => '$$',
    ];
    $schema_script = '<script type="application/ld+json" id="ft-home-schema">'
        . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        . '</script>';
    $html = preg_replace(
        '/<script[^>]*id="ft-home-schema"[^>]*>.*?<\/script>/is',
        $schema_script,
        $html,
        1
    );


    // Settings and static markup can contain URLs saved before the site moved.
    $html = ft_next_homepage_normalize_static_html($html);

    status_header(200);
    nocache_headers();
    header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
    echo $html;
    exit;
});

add_action('admin_enqueue_scripts', function () {
    $custom_admin_css = __DIR__ . '/custom.css';
    wp_enqueue_style(
        'ft-wp-admin-custom',
        plugin_dir_url(__FILE__) . 'custom.css',
        [],
        is_readable($custom_admin_css) ? (string) filemtime($custom_admin_css) : null
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_ft-next-homepage' && !str_contains((string) $hook, 'ft-next-homepage')) {
        return;
    }

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_media();

    wp_add_inline_style('wp-color-picker', '
        body.toplevel_page_ft-next-homepage,
        body.toplevel_page_ft-next-homepage #wpwrap,
        body.toplevel_page_ft-next-homepage #wpcontent,
        body.toplevel_page_ft-next-homepage #wpbody,
        body.toplevel_page_ft-next-homepage #wpbody-content {
            min-width: 0;
            max-width: 100%;
            background: #f3f5f7;
        }
        body.toplevel_page_ft-next-homepage,
        body.toplevel_page_ft-next-homepage #wpwrap {
            overflow-x: clip;
        }
        body.toplevel_page_ft-next-homepage #wpcontent,
        body.toplevel_page_ft-next-homepage #wpbody,
        body.toplevel_page_ft-next-homepage #wpbody-content {
            width: auto;
        }
        body.toplevel_page_ft-next-homepage #wpbody-content {
            float: none;
        }
        #wpcontent {
            background: #f3f5f7;
        }
        .ft-next-admin {
            max-width: none;
            width: 100%;
            margin: 20px 0 0;
            padding: 0 22px 32px 0;
            box-sizing: border-box;
            overflow-x: clip;
            color: #17202a;
            container-type: inline-size;
        }
        .ft-next-admin * {
            box-sizing: border-box;
        }
        .ft-next-admin form,
        .ft-next-admin table,
        .ft-next-admin tbody,
        .ft-next-admin tr,
        .ft-next-admin td,
        .ft-next-admin th,
        .ft-next-admin label,
        .ft-next-admin .ft-next-hero,
        .ft-next-admin .ft-next-grid,
        .ft-next-admin .ft-next-grid > *,
        .ft-next-admin .ft-next-card,
        .ft-next-admin .ft-next-card__body,
        .ft-next-admin .ft-next-repeater,
        .ft-next-admin .ft-next-repeater-item,
        .ft-next-admin .ft-next-inline-grid,
        .ft-next-admin .ft-next-style-grid,
        .ft-next-admin .ft-next-button-style-grid,
        .ft-next-admin .ft-next-actions {
            min-width: 0;
            max-width: 100%;
        }
        .ft-next-admin table {
            table-layout: fixed;
        }
        .ft-next-admin a {
            color: #155f99;
        }
        .ft-next-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, 460px);
            justify-content: space-between;
            gap: 22px;
            align-items: start;
            width: 100%;
            margin: 0 0 18px;
            padding: 20px;
            border: 1px solid #d9e2ea;
            border-radius: 8px;
            background:
                radial-gradient(circle at 0 0, rgba(184, 107, 0, .14), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #eef5fa 100%);
            box-shadow: none;
        }
        .ft-next-hero h1 {
            margin: 0 0 8px;
            font-size: 28px;
            line-height: 1.2;
            letter-spacing: 0;
            color: #111827;
        }
        .ft-next-muted {
            margin: 0;
            color: #5f6f82;
            font-size: 14px;
        }
        .ft-next-quicknav {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 18px;
        }
        .ft-next-quicknav a {
            display: inline-flex;
            align-items: center;
            min-height: 32px;
            padding: 6px 10px;
            border: 1px solid #cfd9e2;
            border-radius: 8px;
            background: rgba(255, 255, 255, .74);
            color: #243447;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
        }
        .ft-next-quicknav a:hover,
        .ft-next-quicknav a:focus {
            border-color: #155f99;
            color: #155f99;
            box-shadow: 0 0 0 2px rgba(21, 95, 153, .12);
            outline: none;
        }
        .ft-next-endpoint {
            display: grid;
            grid-template-columns: 120px minmax(0, 1fr);
            align-items: center;
            gap: 10px 12px;
            min-width: 0;
            padding: 16px;
            border-radius: 8px;
            background: #fff;
            border: 1px solid #d9e2ea;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
        }
        .ft-next-endpoint strong {
            margin: 0 !important;
        }
        .ft-next-endpoint code {
            display: block;
            white-space: normal;
            margin: 0;
            padding: 7px 10px;
            border-radius: 6px;
            background: #f5f7f9;
            color: #1d2a38;
            word-break: break-word;
        }
        .ft-next-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(300px, 360px);
            gap: 24px;
            align-items: start;
            width: 100%;
            min-width: 0;
        }
        .ft-next-grid > div:first-child {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 24px;
            align-items: start;
            min-width: 0;
        }
        .ft-next-grid > div:first-child .ft-next-card {
            margin: 0;
        }
        .ft-next-grid > div:first-child .ft-next-card,
        .ft-next-card--wide {
            grid-column: 1 / -1;
        }
        .ft-next-grid > div:first-child .ft-next-card:nth-child(n+5) input.regular-text,
        .ft-next-grid > div:first-child .ft-next-card:nth-child(n+5) textarea.large-text,
        .ft-next-grid > div:first-child .ft-next-card:nth-child(n+5) .ft-next-image-row,
        .ft-next-grid > div:first-child .ft-next-card:nth-child(n+5) .ft-next-color-row {
            max-width: none;
        }
        .ft-next-grid > div:first-child .ft-next-card:nth-child(n+5) .form-table th {
            width: 220px;
        }
        .ft-next-card {
            margin: 0 0 20px;
            width: 100%;
            border: 1px solid #d9e2ea;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 10px 28px rgba(30, 49, 72, .07);
            overflow: hidden;
            scroll-margin-top: 48px;
        }
        .ft-next-card__head {
            padding: 20px;
            border-bottom: 1px solid #edf1f5;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }
        .ft-next-card__head h2 {
            margin: 0;
            font-size: 15px;
            line-height: 1.35;
            color: #17202a;
        }
        .ft-next-card__body {
            padding: 20px;
        }
        .ft-next-card__body > h3 {
            margin: 24px 0 14px;
            font-size: 14px;
            line-height: 1.4;
            color: #17202a;
        }
        .ft-next-card__body > h3:first-child {
            margin-top: 0;
        }
        .ft-next-card__body small {
            line-height: 1.45;
        }
        .ft-next-card .form-table {
            margin: 0;
        }
        .ft-next-card .form-table th {
            width: 180px;
            padding: 14px 14px 14px 0;
            color: #314154;
            font-weight: 700;
        }
        .ft-next-card .form-table td {
            padding: 10px 0;
        }
        .ft-next-card .form-table tr.ft-next-color-field {
            display: block;
            margin-bottom: 18px;
        }
        .ft-next-card .form-table tr.ft-next-color-field:last-child {
            margin-bottom: 0;
        }
        .ft-next-card .form-table tr.ft-next-color-field th,
        .ft-next-card .form-table tr.ft-next-color-field td {
            display: block;
            width: 100%;
            padding: 0;
        }
        .ft-next-card .form-table tr.ft-next-color-field th {
            margin-bottom: 8px;
        }
        .ft-next-card input.regular-text,
        .ft-next-card textarea.large-text,
        .ft-next-card select,
        .ft-next-inline-grid input,
        .ft-next-inline-grid textarea,
        .ft-next-field-stack input,
        .ft-next-field-stack textarea,
        .ft-next-table input,
        .ft-next-table textarea {
            width: 100%;
            max-width: none;
            border-radius: 8px;
            border-color: #cbd5df;
            min-height: 38px;
            color: #1d2a38;
        }
        .ft-next-card input:not([type="checkbox"]):not([type="radio"]):not([type="button"]):not([type="submit"]),
        .ft-next-card select {
            height: 40px;
            min-height: 40px;
            padding-top: 0;
            padding-bottom: 0;
        }
        .ft-next-card textarea.large-text,
        .ft-next-inline-grid textarea,
        .ft-next-field-stack textarea,
        .ft-next-table textarea {
            min-height: 92px;
        }
        .ft-next-card input:focus,
        .ft-next-card textarea:focus,
        .ft-next-card select:focus {
            border-color: #155f99;
            box-shadow: 0 0 0 2px rgba(21, 95, 153, .15);
            outline: none;
        }
        .ft-next-image-row {
            display: flex;
            gap: 12px;
            align-items: center;
            max-width: none;
        }
        .ft-next-image-input {
            display: none !important;
        }
        .ft-next-image-preview {
            display: block;
            width: 320px;
            max-width: 100%;
            height: 190px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #d9e2ea;
            margin-top: 12px;
            background: #f5f7f9;
        }
        .ft-next-table {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #d9e2ea;
            box-shadow: none;
        }
        .ft-next-utm-table input {
            width: 100%;
            font-family: Consolas, Monaco, monospace;
            font-size: 12px;
        }
        .ft-next-utm-table td:first-child {
            width: 96px;
            font-weight: 700;
            color: #17202a;
        }
        .ft-next-utm-copy-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 38px;
            gap: 8px;
            align-items: center;
        }
        .ft-next-utm-copy {
            display: inline-flex;
            width: 38px;
            height: 38px;
            align-items: center;
            justify-content: center;
            border: 1px solid #cbd5df;
            border-radius: 8px;
            background: #fff;
            color: #155f99;
            cursor: pointer;
        }
        .ft-next-utm-copy:hover,
        .ft-next-utm-copy:focus-visible {
            border-color: #155f99;
            box-shadow: 0 0 0 2px rgba(21, 95, 153, .12);
            outline: none;
        }
        .ft-next-utm-copy.is-copied {
            color: #047857;
            border-color: #047857;
        }
        .ft-next-table th {
            background: #f8fafc;
            color: #314154;
            font-size: 12px;
            text-transform: uppercase;
        }
        .ft-next-repeater {
            display: grid;
            gap: 18px;
        }
        .ft-next-repeater--offers {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            align-items: stretch;
        }
        .ft-next-repeater--offers .ft-next-repeater-item {
            min-width: 0;
            height: 100%;
        }
        .ft-next-repeater--offers textarea {
            min-height: 96px;
        }
        .ft-next-repeater--process {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            align-items: stretch;
        }
        .ft-next-repeater--process .ft-next-repeater-item {
            grid-template-columns: 1fr;
            align-content: start;
            min-width: 0;
            height: 100%;
        }
        .ft-next-repeater--process .ft-next-media-panel,
        .ft-next-repeater--process .ft-next-image-field {
            min-width: 0;
            width: 100%;
        }
        .ft-next-repeater--process .ft-next-image-preview {
            width: 100%;
            max-width: none;
            height: auto;
            aspect-ratio: 16 / 10;
            object-fit: cover;
        }
        .ft-next-repeater--categories {
            grid-template-columns: repeat(5, minmax(0, 1fr));
            align-items: stretch;
        }
        .ft-next-repeater--categories .ft-next-repeater-item {
            grid-template-columns: 1fr;
            align-content: start;
            min-width: 0;
            height: 100%;
        }
        .ft-next-repeater--categories .ft-next-media-panel,
        .ft-next-repeater--categories .ft-next-image-field {
            min-width: 0;
            width: 100%;
        }
        .ft-next-repeater--categories .ft-next-image-preview {
            width: 100%;
            max-width: none;
            height: auto;
            aspect-ratio: 4 / 3;
            object-fit: cover;
        }
        .ft-next-testimonials-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            align-items: stretch;
        }
        .ft-next-testimonial-editor {
            display: grid;
            align-content: start;
            gap: 16px;
            min-width: 0;
            padding: 20px;
            border: 1px solid #d9e2ea;
            border-radius: 8px;
            background: #f8fafc;
        }
        .ft-next-testimonial-editor h3 {
            margin: 0;
            color: #17202a;
            font-size: 13px;
        }
        .ft-next-testimonial-editor label {
            display: grid;
            gap: 6px;
            min-width: 0;
            color: #314154;
            font-weight: 600;
        }
        .ft-next-testimonial-editor input,
        .ft-next-testimonial-editor textarea {
            width: 100%;
            max-width: none;
        }
        .ft-next-testimonial-editor textarea {
            min-height: 150px;
        }
        .ft-next-repeater-item {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(260px, .65fr);
            gap: 20px;
            padding: 20px;
            border: 1px solid #d9e2ea;
            border-radius: 8px;
            background: #f8fafc;
        }
        .ft-next-repeater-item--single {
            grid-template-columns: minmax(0, 1fr);
        }
        .ft-next-repeater-item--hero {
            grid-template-columns: minmax(0, 1fr) 320px;
            align-items: start;
        }
        .ft-next-repeater-item--hero .ft-next-media-panel,
        .ft-next-repeater-item--hero .ft-next-subgroup,
        .ft-next-repeater-item--hero .ft-next-image-field {
            min-width: 0;
            max-width: 100%;
        }
        .ft-next-hero-background-controls {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .ft-next-hero-opacity {
            display: grid;
            gap: 6px;
            font-weight: 600;
            color: #314154;
        }
        .ft-next-repeater-item--hero .ft-next-image-preview {
            width: 100%;
            max-width: 100%;
            height: auto;
            aspect-ratio: 16 / 10;
            object-fit: cover;
        }
        .ft-next-repeater-item--single .ft-next-media-panel,
        .ft-next-repeater-item--single .ft-next-image-preview {
            width: 100%;
        }
        .ft-next-repeater-item--single .ft-next-image-preview {
            max-width: 720px;
            height: auto;
            aspect-ratio: 16 / 9;
        }
        .ft-next-repeater-item h3 {
            grid-column: 1 / -1;
            margin: 0;
            font-size: 13px;
            color: #17202a;
        }
        .ft-next-field-stack {
            display: grid;
            gap: 16px;
        }
        .ft-next-field-stack label {
            display: grid;
            gap: 6px;
            font-weight: 600;
            color: #314154;
        }
        .ft-next-popup-fields {
            display: grid;
            gap: 20px;
            margin-bottom: 20px;
        }
        .ft-next-popup-fields label {
            display: grid;
            gap: 8px;
            min-width: 0;
            color: #17202a;
            font-weight: 700;
        }
        .ft-next-popup-fields textarea {
            font-weight: 400;
        }
        .ft-next-popup-fields small {
            margin-top: 2px;
            color: #5f6f82;
            font-weight: 400;
        }
        .ft-next-subgroup {
            display: grid;
            gap: 14px;
            padding: 20px;
            border: 1px solid #d9e2ea;
            border-radius: 8px;
            background: #fff;
        }
        .ft-next-subgroup h4 {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            color: #17202a;
        }
        .ft-next-inline-grid {
            display: grid;
            gap: 18px;
            margin-bottom: 20px;
        }
        .ft-next-inline-grid--2 {
            grid-template-columns: repeat(2, minmax(220px, 1fr));
        }
        .ft-next-inline-grid--3 {
            grid-template-columns: repeat(3, minmax(180px, 1fr));
        }
        .ft-next-inline-grid--4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .ft-next-inline-grid--header {
            grid-template-columns: minmax(150px, 1fr) minmax(180px, 1fr) minmax(260px, 30%) minmax(170px, 1fr);
        }
        .ft-next-inline-grid--badge-style {
            grid-template-columns: repeat(2, minmax(260px, 1fr)) 120px;
            align-items: end;
        }
        .ft-next-inline-grid--compact-values {
            grid-template-columns: repeat(2, minmax(120px, 180px));
            justify-content: start;
        }
        .ft-next-inline-grid--badge-numbers {
            grid-template-columns: repeat(3, minmax(140px, 1fr));
            align-items: end;
        }
        .ft-next-button-style-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(130px, 1fr));
            gap: 18px;
            align-items: end;
        }
        .ft-next-button-style-grid label {
            display: grid;
            gap: 6px;
            min-width: 0;
            color: #314154;
            font-weight: 600;
        }
        .ft-next-button-style-grid input,
        .ft-next-button-style-grid select {
            width: 100%;
        }
        .ft-next-style-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(180px, 1fr));
            gap: 18px;
        }
        .ft-next-style-grid label {
            display: grid;
            gap: 6px;
            min-width: 0;
            color: #314154;
            font-weight: 600;
        }
        .ft-next-inline-grid label {
            display: grid;
            gap: 6px;
            font-weight: 600;
            color: #314154;
        }
        .ft-next-inline-grid input,
        .ft-next-inline-grid textarea,
        .ft-next-field-stack input,
        .ft-next-field-stack textarea {
            font-weight: 400;
        }
        .ft-next-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 14px 16px;
            border: 1px solid #d9e2ea;
            border-radius: 8px;
            background: #f8fafc;
            font-weight: 600;
            color: #314154;
        }
        .ft-next-toggle input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .ft-next-toggle__switch {
            display: block;
            width: 48px;
            height: 26px;
            border-radius: 999px;
            background: #c3c4c7;
            position: relative;
            transition: background .18s ease;
            flex: 0 0 auto;
        }
        .ft-next-toggle__switch::after {
            content: "";
            position: absolute;
            top: 3px;
            left: 3px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,.25);
            transition: transform .18s ease;
        }
        .ft-next-toggle input:checked + .ft-next-toggle__switch {
            background: #155f99;
        }
        .ft-next-toggle input:checked + .ft-next-toggle__switch::after {
            transform: translateX(22px);
        }
        .ft-next-toggle input:focus-visible + .ft-next-toggle__switch {
            outline: 2px solid #155f99;
            outline-offset: 2px;
        }
        .ft-next-media-panel {
            display: grid;
            align-content: start;
            gap: 10px;
        }
        .ft-next-media-panel > span {
            font-weight: 600;
        }
        .ft-next-color-row {
            display: flex;
            align-items: stretch;
            max-width: none;
            width: 100%;
            min-height: 40px;
            overflow: hidden;
            border: 1px solid #cbd5df;
            border-radius: 8px;
            background: #fff;
        }
        .ft-next-color-row:focus-within {
            border-color: #155f99;
            box-shadow: 0 0 0 2px rgba(21, 95, 153, .15);
        }
        .ft-next-color-swatch {
            width: 48px !important;
            min-width: 48px !important;
            height: 40px !important;
            min-height: 40px !important;
            padding: 0 !important;
            border: 0 !important;
            border-right: 1px solid #cbd5df !important;
            border-radius: 0 !important;
            background: transparent;
            cursor: pointer;
        }
        .ft-next-color-swatch::-webkit-color-swatch-wrapper {
            padding: 0;
        }
        .ft-next-color-swatch::-webkit-color-swatch {
            border: 0;
        }
        .ft-next-color-swatch::-moz-color-swatch {
            border: 0;
        }
        .ft-next-color-value {
            flex: 1 1 auto;
            min-width: 0;
            width: 100% !important;
            height: 40px !important;
            min-height: 40px !important;
            margin: 0 !important;
            padding: 0 12px !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }
        .ft-next-sidebar {
            position: static;
            display: grid;
            gap: 20px;
            min-width: 0;
            max-width: 100%;
            align-self: start;
        }
        .ft-next-sidebar .ft-next-card {
            margin: 0;
        }
        .ft-next-sidebar-logo {
            display: grid;
            gap: 18px;
        }
        .ft-next-sidebar-logo label,
        .ft-next-sidebar-logo .ft-next-media-panel {
            display: grid;
            gap: 6px;
            min-width: 0;
            font-weight: 600;
            color: #314154;
        }
        .ft-next-sidebar-logo .ft-next-image-preview {
            width: 100%;
            max-width: 100%;
            height: 120px;
            margin-top: 4px;
            object-fit: contain;
            background: #f8fafc;
        }
        .ft-next-preview {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #d9e2ea;
            background: #fff;
            box-shadow: 0 12px 30px rgba(30, 49, 72, .08);
        }
        .ft-next-preview__top {
            padding: 20px;
            color: #fff;
            background: var(--ft-primary, #155f99);
        }
        .ft-next-preview__body {
            padding: 20px;
            background: var(--ft-bg, #f7f6f2);
            color: var(--ft-fg, #231f20);
        }
        .ft-next-preview__button {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 14px;
            border-radius: 8px;
            color: #fff;
            background: var(--ft-secondary, #b86b00);
            font-weight: 700;
        }
        .ft-next-actions {
            position: sticky;
            bottom: 0;
            z-index: 100;
            width: 100%;
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #d9e2ea;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 -8px 24px rgba(30, 49, 72, .10);
        }
        .ft-next-actions .button {
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }
        .ft-next-actions .button-primary {
            background: #155f99;
            border-color: #155f99;
        }
        @container (max-width: 1120px) {
            .ft-next-grid,
            .ft-next-hero {
                grid-template-columns: minmax(0, 1fr);
            }
            .ft-next-inline-grid--2,
            .ft-next-inline-grid--3,
            .ft-next-inline-grid--4,
            .ft-next-inline-grid--header,
            .ft-next-inline-grid--badge-style,
            .ft-next-button-style-grid,
            .ft-next-style-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .ft-next-repeater--offers,
            .ft-next-repeater--categories {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .ft-next-repeater--process,
            .ft-next-testimonials-grid,
            .ft-next-repeater-item,
            .ft-next-repeater-item--hero {
                grid-template-columns: minmax(0, 1fr);
            }
        }
        @container (max-width: 620px) {
            .ft-next-endpoint {
                grid-template-columns: minmax(0, 1fr);
            }
            .ft-next-inline-grid--2,
            .ft-next-inline-grid--3,
            .ft-next-inline-grid--4,
            .ft-next-inline-grid--header,
            .ft-next-inline-grid--badge-style,
            .ft-next-inline-grid--badge-numbers,
            .ft-next-inline-grid--compact-values,
            .ft-next-button-style-grid,
            .ft-next-style-grid,
            .ft-next-repeater--offers,
            .ft-next-repeater--categories {
                grid-template-columns: minmax(0, 1fr);
            }
        }
        @media (max-width: 1280px) {
            .ft-next-grid,
            .ft-next-hero {
                grid-template-columns: 1fr;
            }
            .ft-next-grid > div:first-child {
                grid-template-columns: 1fr;
            }
            .ft-next-grid > div:first-child .ft-next-card {
                margin: 0 0 20px;
            }
            .ft-next-endpoint,
            .ft-next-sidebar {
                min-width: 0;
                margin-top: 16px;
                position: static;
            }
            .ft-next-sidebar {
                margin-top: 0;
            }
            .ft-next-repeater-item {
                grid-template-columns: 1fr;
            }
            .ft-next-repeater-item--hero {
                grid-template-columns: 1fr;
            }
            .ft-next-repeater-item--hero .ft-next-image-preview {
                max-width: 420px;
            }
            .ft-next-inline-grid--2,
            .ft-next-inline-grid--3,
            .ft-next-inline-grid--4,
            .ft-next-inline-grid--badge-style,
            .ft-next-inline-grid--badge-numbers,
            .ft-next-inline-grid--compact-values,
            .ft-next-button-style-grid,
            .ft-next-style-grid,
            .ft-next-inline-grid--header {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 1100px) {
            .ft-next-repeater--offers {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .ft-next-repeater--process {
                grid-template-columns: 1fr;
            }
            .ft-next-repeater--categories {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .ft-next-testimonials-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 782px) {
            .ft-next-repeater--offers {
                grid-template-columns: 1fr;
            }
            .ft-next-repeater--categories {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 782px) {
            .ft-next-admin {
                padding-right: 10px;
            }
            .ft-next-hero {
                padding: 20px;
            }
            .ft-next-card .form-table th,
            .ft-next-card .form-table td {
                display: block;
                width: 100%;
                padding: 8px 0;
            }
            .ft-next-color-row {
                grid-template-columns: 1fr;
            }
            .ft-next-button-style-grid {
                grid-template-columns: 1fr;
            }
            .ft-next-style-grid {
                grid-template-columns: 1fr;
            }
            .ft-next-actions {
                align-items: stretch;
                flex-direction: column;
            }
        }
    ');

    wp_add_inline_script('wp-color-picker', "
        jQuery(function($) {
            var quickNav = $('.ft-next-quicknav');

            $('.ft-next-card').each(function(index) {
                var card = $(this);
                var title = $.trim(card.find('.ft-next-card__head h2').first().text());

                if (!title) {
                    return;
                }

                var id = 'ft-next-section-' + index;
                card.attr('id', id);
                $('<a />', {
                    href: '#' + id,
                    text: title
                }).appendTo(quickNav);
            });

            quickNav.on('click', 'a', function(event) {
                var target = $($(this).attr('href'));

                if (!target.length) {
                    return;
                }

                event.preventDefault();
                $('html, body').animate({ scrollTop: target.offset().top - 48 }, 220);
            });

            function cssToHex(value) {
                value = (value || '').trim();
                if (/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(value)) {
                    return value;
                }

                var probe = $('<span />').css({
                    color: value,
                    display: 'none'
                }).appendTo(document.body);
                var rgb = probe.css('color');
                probe.remove();
                var match = rgb && rgb.match(/^rgba?\\((\\d+),\\s*(\\d+),\\s*(\\d+)/i);
                if (!match) {
                    return '#ffffff';
                }

                return '#' + [match[1], match[2], match[3]].map(function(part) {
                    return ('0' + parseInt(part, 10).toString(16)).slice(-2);
                }).join('');
            }

            $('.ft-next-color-value').each(function() {
                var input = $(this);
                var raw = input.val();
                var swatch = input.closest('.ft-next-color-row').find('.ft-next-color-swatch');

                function updatePreview(value) {
                    document.documentElement.style.setProperty(input.data('preview-var'), value || '');
                }

                swatch.val(cssToHex(raw));
                updatePreview(raw);

                swatch.on('input change', function() {
                    input.val(swatch.val()).trigger('input');
                });

                input.on('input change', function() {
                    updatePreview(input.val());
                    swatch.val(cssToHex(input.val()));
                });
            });

            $('.ft-next-image-button').on('click', function(event) {
                event.preventDefault();
                var button = $(this);
                var row = button.closest('.ft-next-image-field');
                var input = row.find('.ft-next-image-input');
                var preview = row.find('.ft-next-image-preview');
                var frame = wp.media({
                    title: 'Select image',
                    button: { text: 'Use this image' },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    var url = attachment.url || '';
                    input.val(url).trigger('change');
                    preview.attr('src', url).show();
                });

                frame.open();
            });

            $('.ft-next-image-input').on('input change', function() {
                var input = $(this);
                var preview = input.closest('.ft-next-image-field').find('.ft-next-image-preview');
                if (input.val()) {
                    preview.attr('src', input.val()).show();
                } else {
                    preview.hide();
                }
            }).trigger('change');
        });
    ");
});

add_action('admin_post_ft_next_homepage_save', function () {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    check_admin_referer('ft_next_homepage_save');

    $current = ft_next_homepage_settings();
    $data = [];
    $text_fields = [
        'phone', 'email', 'service_area', 'logo_text', 'logo_image', 'favicon_image', 'logo_size', 'cta_label',
        'facebook_url', 'instagram_url', 'linkedin_url', 'youtube_url', 'tiktok_url',
        'footer_badge_image_1', 'footer_badge_image_2', 'footer_badge_image_3',
        'footer_badge_image_4', 'footer_badge_image_5', 'footer_badge_image_6',
        'footer_badge_height',
        'seo_title', 'seo_canonical_url', 'seo_robots', 'seo_og_title', 'seo_og_image', 'hero_badge',
        'hero_badge_font_size', 'hero_badge_mobile_font_size', 'hero_badge_padding_x', 'hero_badge_padding_y',
        'hero_title', 'hero_highlight', 'hero_title_font_size', 'hero_title_mobile_font_size',
        'hero_badge_animation_location', 'hero_badge_animation_speed', 'form_title', 'form_subtitle',
        'process_title', 'comparison_title', 'comparison_table_title', 'comparison_button',
        'cta_title', 'cta_subtitle', 'cta_button', 'hero_image', 'hero_overlay_opacity',
        'button_radius', 'button_font_weight', 'button_text_transform',
        'button_padding_x', 'button_padding_y', 'button_hover_mix',
        'button_border_width', 'button_border_style',
        'process_bg_location', 'comparison_bg_location', 'category_bg_location',
        'guarantee_bg_location', 'deals_bg_location', 'testimonials_bg_location',
        'cta_bg_location', 'footer_bg_location',
        'category_title', 'guarantee_title', 'guarantee_subtitle', 'guarantee_link',
        'guarantee_image', 'deals_badge', 'deals_title', 'deals_card_title',
        'deals_card_subtitle', 'deals_button', 'deals_details_label',
        'deals_includes_title', 'deals_popup_eyebrow',
        'deals_popup_button', 'deals_popup_steps_title', 'testimonials_title',
        'newsletter_title', 'newsletter_popup_title', 'newsletter_text', 'newsletter_button', 'newsletter_claim_button',
        'warranty_popup_title', 'warranty_see_more_text', 'warranty_see_more_url',
        'footer_about_title', 'footer_categories_title', 'footer_help_title',
        'footer_policies_title', 'footer_copyright', 'google_places_api_key',
        'fb_pixel_id', 'recaptcha_site_key',
    ];
    $textarea_fields = [
        'hero_text', 'process_text', 'comparison_text', 'cta_text', 'footer_about',
        'seo_description', 'seo_og_description',
        'category_text', 'guarantee_text', 'deals_text', 'deals_body',
        'deals_includes', 'deals_popup_title', 'deals_popup_intro', 'deals_popup_steps',
        'deals_popup_terms', 'deals_popup_terms_extra',
        'testimonials_text', 'newsletter_details_text', 'warranty_details_content',
    ];
    $color_fields = [
        'primary_color', 'secondary_color', 'background_color', 'foreground_color',
        'button_border_color', 'hero_badge_bg_color', 'hero_badge_text_color',
        'hero_badge_animation_color_1', 'hero_badge_animation_color_2',
        'process_bg_color_1', 'process_bg_color_2',
        'comparison_bg_color_1', 'comparison_bg_color_2',
        'category_bg_color_1', 'category_bg_color_2',
        'guarantee_bg_color_1', 'guarantee_bg_color_2',
        'deals_bg_color_1', 'deals_bg_color_2',
        'testimonials_bg_color_1', 'testimonials_bg_color_2',
        'cta_bg_color_1', 'cta_bg_color_2',
        'footer_bg_color_1', 'footer_bg_color_2',
    ];

    foreach ($text_fields as $field) {
        $data[$field] = isset($_POST[$field]) ? ft_next_clean_text($_POST[$field]) : $current[$field];
    }

    foreach ($textarea_fields as $field) {
        $data[$field] = isset($_POST[$field]) ? ft_next_clean_textarea($_POST[$field]) : $current[$field];
    }

    foreach ($color_fields as $field) {
        $data[$field] = isset($_POST[$field]) ? ft_next_clean_color($_POST[$field]) : $current[$field];
    }

    $data['show_header'] = isset($_POST['show_header']) ? '1' : '0';
    $data['show_footer'] = isset($_POST['show_footer']) ? '1' : '0';
    $data['show_testimonials'] = isset($_POST['show_testimonials']) ? '1' : '0';
    // Saved without tag-stripping — only admins can reach this page (verified by manage_options cap check above).
    $data['testimonials_embed_code'] = isset($_POST['testimonials_embed_code']) ? wp_unslash($_POST['testimonials_embed_code']) : ($current['testimonials_embed_code'] ?? '');
    $data['chat_embed_code'] = isset($_POST['chat_embed_code']) ? wp_unslash($_POST['chat_embed_code']) : ($current['chat_embed_code'] ?? '');
    $data['hero_show_background'] = isset($_POST['hero_show_background']) ? '1' : '0';
    $data['hero_show_overlay'] = isset($_POST['hero_show_overlay']) ? '1' : '0';

    $data['comparison_rows'] = isset($_POST['comparison_rows'])
        ? ft_next_clean_lines($_POST['comparison_rows'])
        : $current['comparison_rows'];

    $data['nav_items'] = [];
    $nav_names = $_POST['nav_name'] ?? [];
    $nav_hrefs = $_POST['nav_href'] ?? [];
    for ($i = 0; $i < 5; $i++) {
        $name = isset($nav_names[$i]) ? ft_next_clean_text($nav_names[$i]) : '';
        $href = isset($nav_hrefs[$i]) ? ft_next_clean_menu_url($nav_hrefs[$i]) : '';
        if ($name !== '') {
            $data['nav_items'][] = ['name' => $name, 'href' => $href ?: '#'];
        }
    }


    $data['utility_links'] = [];
    $utility_names = $_POST['utility_name'] ?? [];
    $utility_hrefs = $_POST['utility_href'] ?? [];
    for ($i = 0; $i < 6; $i++) {
        $name = isset($utility_names[$i]) ? ft_next_clean_text($utility_names[$i]) : '';
        $href = isset($utility_hrefs[$i]) ? ft_next_clean_menu_url($utility_hrefs[$i]) : '';
        if ($name !== '') {
            $data['utility_links'][] = ['name' => $name, 'href' => $href ?: '#'];
        }
    }

    foreach (['about' => 'footer_about_links', 'help' => 'footer_help_links', 'policy' => 'footer_policy_links', 'bottom' => 'footer_bottom_links'] as $prefix => $key) {
        $data[$key] = [];
        $labels = $_POST['footer_' . $prefix . '_label'] ?? [];
        $urls   = $_POST['footer_' . $prefix . '_url'] ?? [];
        for ($i = 0; $i < 8; $i++) {
            $label = isset($labels[$i]) ? ft_next_clean_text($labels[$i]) : '';
            $url   = isset($urls[$i]) ? ft_next_clean_menu_url($urls[$i]) : '';
            if ($label !== '') {
                $data[$key][] = ['label' => $label, 'url' => $url ?: '#'];
            }
        }
    }

    $data['process_steps'] = [];
    for ($i = 0; $i < 3; $i++) {
        $data['process_steps'][] = [
            'title' => isset($_POST['process_step_title'][$i]) ? ft_next_clean_text($_POST['process_step_title'][$i]) : $current['process_steps'][$i]['title'],
            'description' => isset($_POST['process_step_description'][$i]) ? ft_next_clean_textarea($_POST['process_step_description'][$i]) : $current['process_steps'][$i]['description'],
            'button' => isset($_POST['process_step_button'][$i]) ? ft_next_clean_text($_POST['process_step_button'][$i]) : '',
            'image' => isset($_POST['process_step_image'][$i]) ? esc_url_raw(wp_unslash($_POST['process_step_image'][$i])) : $current['process_steps'][$i]['image'],
        ];
    }

    $data['categories'] = [];
    for ($i = 0; $i < 5; $i++) {
        $name = isset($_POST['category_name'][$i]) ? ft_next_clean_text($_POST['category_name'][$i]) : '';
        if ($name === '') {
            continue;
        }
        $data['categories'][] = [
            'name' => $name,
            'slug' => isset($_POST['category_slug'][$i]) ? ft_next_clean_text($_POST['category_slug'][$i]) : sanitize_title($name),
            'description' => isset($_POST['category_description'][$i]) ? ft_next_clean_textarea($_POST['category_description'][$i]) : '',
            'image' => isset($_POST['category_image'][$i]) ? esc_url_raw(wp_unslash($_POST['category_image'][$i])) : '',
        ];
    }

    $data['offers'] = [];
    for ($i = 0; $i < 4; $i++) {
        $title = isset($_POST['offer_title'][$i]) ? ft_next_clean_text($_POST['offer_title'][$i]) : '';
        if ($title === '') {
            continue;
        }
        $data['offers'][] = [
            'title' => $title,
            'description' => isset($_POST['offer_description'][$i]) ? ft_next_clean_textarea($_POST['offer_description'][$i]) : '',
        ];
    }

    $data['testimonials'] = [];
    for ($i = 0; $i < 3; $i++) {
        $name = isset($_POST['testimonial_name'][$i]) ? ft_next_clean_text($_POST['testimonial_name'][$i]) : '';
        if ($name === '') {
            continue;
        }
        $data['testimonials'][] = [
            'name' => $name,
            'location' => isset($_POST['testimonial_location'][$i]) ? ft_next_clean_text($_POST['testimonial_location'][$i]) : '',
            'floorType' => isset($_POST['testimonial_floor_type'][$i]) ? ft_next_clean_text($_POST['testimonial_floor_type'][$i]) : '',
            'text' => isset($_POST['testimonial_text'][$i]) ? ft_next_clean_textarea($_POST['testimonial_text'][$i]) : '',
        ];
    }

    update_option(FT_NEXT_HOME_OPTION, $data);
    wp_safe_redirect(admin_url('admin.php?page=ft-next-homepage&updated=1'));
    exit;
});

function ft_next_homepage_card_open($title) {
    echo '<section class="ft-next-card"><div class="ft-next-card__head"><h2>' . esc_html($title) . '</h2></div><div class="ft-next-card__body">';
}

function ft_next_homepage_card_close() {
    echo '</div></section>';
}

function ft_next_homepage_field($settings, $field, $label, $type = 'text') {
    $value = $settings[$field] ?? '';
    if ($type === 'checkbox') {
        echo '<label class="ft-next-toggle" for="' . esc_attr($field) . '">';
        echo '<span>' . esc_html($label) . '</span>';
        echo '<span>';
        echo '<input id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" type="checkbox" value="1" ' . checked((string) $value, '1', false) . '>';
        echo '<span class="ft-next-toggle__switch" aria-hidden="true"></span>';
        echo '</span>';
        echo '</label>';
        return;
    }

    $row_class = $type === 'color' ? ' class="ft-next-color-field"' : '';
    echo '<tr' . $row_class . '><th scope="row"><label for="' . esc_attr($field) . '">' . esc_html($label) . '</label></th><td>';
    if ($type === 'textarea') {
        echo '<textarea id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" rows="4" class="large-text">' . esc_textarea($value) . '</textarea>';
    } elseif ($type === 'image') {
        echo '<div class="ft-next-image-field">';
        echo '<div class="ft-next-image-row">';
        echo '<input id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" type="text" class="regular-text ft-next-image-input" value="' . esc_attr($value) . '">';
        echo '<button type="button" class="button ft-next-image-button">Select Image</button>';
        echo '</div>';
        echo '<img class="ft-next-image-preview" src="' . esc_url($value) . '" alt="" loading="lazy">';
        echo '</div>';
    } elseif ($type === 'color') {
        $preview_var = [
            'primary_color' => '--ft-primary',
            'secondary_color' => '--ft-secondary',
            'background_color' => '--ft-bg',
            'foreground_color' => '--ft-fg',
        ][$field] ?? '';
        echo '<div class="ft-next-color-row">';
        echo '<input type="color" class="ft-next-color-swatch" aria-label="Select color">';
        echo '<input id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" type="text" aria-label="Color code" class="regular-text ft-next-color-value" data-preview-var="' . esc_attr($preview_var) . '" value="' . esc_attr($value) . '">';
        echo '</div>';
    } else {
        echo '<input id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" type="text" class="regular-text" value="' . esc_attr($value) . '">';
    }
    echo '</td></tr>';
}

function ft_next_homepage_color_control($settings, $field) {
    $value = $settings[$field] ?? '';
    echo '<div class="ft-next-color-row">';
    echo '<input type="color" class="ft-next-color-swatch" aria-label="Select color">';
    echo '<input id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" type="text" aria-label="Color code" class="regular-text ft-next-color-value" value="' . esc_attr($value) . '">';
    echo '</div>';
}

function ft_next_homepage_gradient_select($name, $value) {
    $options = [
        'to bottom' => 'Top to bottom',
        'to top' => 'Bottom to top',
        'to right' => 'Left to right',
        'to left' => 'Right to left',
        'to bottom right' => 'Top left to bottom right',
        'to bottom left' => 'Top right to bottom left',
        'to top right' => 'Bottom left to top right',
        'to top left' => 'Bottom right to top left',
        '45deg' => '45 degrees',
        '90deg' => '90 degrees',
        '135deg' => '135 degrees',
        '180deg' => '180 degrees',
    ];

    if ($value !== '' && !isset($options[$value])) {
        $options = [$value => 'Current custom value: ' . $value] + $options;
    }

    echo '<select name="' . esc_attr($name) . '">';
    foreach ($options as $option_value => $option_label) {
        echo '<option value="' . esc_attr($option_value) . '" ' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
    }
    echo '</select>';
}

function ft_next_homepage_gradient_controls($settings, $prefix) {
    echo '<div class="ft-next-inline-grid ft-next-inline-grid--3" style="margin-bottom:16px;">';
    echo '<label>Background color 1';
    ft_next_homepage_color_control($settings, $prefix . '_bg_color_1');
    echo '</label>';
    echo '<label>Background color 2';
    ft_next_homepage_color_control($settings, $prefix . '_bg_color_2');
    echo '</label>';
    echo '<label>Gradient location';
    ft_next_homepage_gradient_select(
        $prefix . '_bg_location',
        $settings[$prefix . '_bg_location'] ?? 'to bottom'
    );
    echo '</label>';
    echo '</div>';
}

function ft_next_homepage_utm_links() {
    $base_url = 'https://floorstoday.ca';
    $platforms = [
        'Facebook' => 'facebook',
        'Instagram' => 'instagram',
        'TikTok' => 'tiktok',
        'LinkedIn' => 'linkedin',
        'YouTube' => 'youtube',
        'Pinterest' => 'pinterest',
        'X / Twitter' => 'x',
        'SMS' => 'sms',
        'Newsletter' => 'newsletter',
        'Google Ads' => 'google-ads',
        'Meta Ads' => 'meta-ads',
    ];
    $links = [];

    foreach ($platforms as $label => $source) {
        $links[] = [
            'label' => $label,
            'url' => add_query_arg(
                [
                    'hello_social' => $source,
                ],
                $base_url . '/'
            ),
        ];
    }

    return $links;
}

function ft_next_homepage_render_admin() {
    $settings = ft_next_homepage_settings();
    $endpoint = rest_url('floors-today/v1/homepage');
    $frontend_url = ft_next_homepage_frontend_url();
    ?>
    <div class="wrap ft-next-admin">
        <?php if (isset($_GET['updated'])) : ?>
            <div class="notice notice-success is-dismissible"><p>Homepage settings saved.</p></div>
        <?php endif; ?>

        <div class="ft-next-hero">
            <div>
                <h1>Next Homepage</h1>
                <p class="ft-next-muted">Edit the content, colors, and settings used by the Next.js front end.</p>
                <nav class="ft-next-quicknav" aria-label="Homepage sections">
                    <a href="<?php echo esc_url($frontend_url); ?>" target="_blank" rel="noopener" style="background:#155f99;color:#fff;border-color:#155f99;">&#x1F441; View Homepage</a>
                </nav>
            </div>
            <div class="ft-next-endpoint">
                <strong>Connected front end</strong>
                <code><a href="<?php echo esc_url($frontend_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($frontend_url); ?></a></code>
                <strong style="display:block;margin-top:12px;">REST endpoint</strong>
                <code><?php echo esc_html($endpoint); ?></code>
            </div>
        </div>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('ft_next_homepage_save'); ?>
            <input type="hidden" name="action" value="ft_next_homepage_save">

            <div class="ft-next-grid">
                <div>
                    <?php ft_next_homepage_card_open('Style'); ?>
                        <div class="ft-next-style-grid">
                            <label>
                                Primary blue color
                                <?php ft_next_homepage_color_control($settings, 'primary_color'); ?>
                            </label>
                            <label>
                                Light brown accent
                                <?php ft_next_homepage_color_control($settings, 'secondary_color'); ?>
                            </label>
                            <label>
                                Page background
                                <?php ft_next_homepage_color_control($settings, 'background_color'); ?>
                            </label>
                            <label>
                                Text color
                                <?php ft_next_homepage_color_control($settings, 'foreground_color'); ?>
                            </label>
                        </div>
                    <?php ft_next_homepage_card_close(); ?>

                    <?php ft_next_homepage_card_open('Button Style'); ?>
                        <div class="ft-next-button-style-grid">
                            <label>
                                Border radius
                                <input name="button_radius" type="text" value="<?php echo esc_attr($settings['button_radius']); ?>">
                            </label>
                            <label>
                                Border width
                                <input name="button_border_width" type="text" value="<?php echo esc_attr($settings['button_border_width']); ?>">
                            </label>
                            <label>
                                Border style
                                <select name="button_border_style">
                                    <?php foreach (['solid', 'dashed', 'dotted', 'double', 'none'] as $style) : ?>
                                        <option value="<?php echo esc_attr($style); ?>" <?php selected($settings['button_border_style'], $style); ?>>
                                            <?php echo esc_html(ucfirst($style)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                Border color
                                <?php ft_next_homepage_color_control($settings, 'button_border_color'); ?>
                            </label>
                            <label>
                                Font weight
                                <input name="button_font_weight" type="text" value="<?php echo esc_attr($settings['button_font_weight']); ?>">
                            </label>
                            <label>
                                Text transform
                                <select name="button_text_transform">
                                    <?php foreach (['none', 'uppercase', 'lowercase', 'capitalize'] as $transform) : ?>
                                        <option value="<?php echo esc_attr($transform); ?>" <?php selected($settings['button_text_transform'], $transform); ?>>
                                            <?php echo esc_html(ucfirst($transform)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                Horizontal padding
                                <input name="button_padding_x" type="text" value="<?php echo esc_attr($settings['button_padding_x']); ?>">
                            </label>
                            <label>
                                Vertical padding
                                <input name="button_padding_y" type="text" value="<?php echo esc_attr($settings['button_padding_y']); ?>">
                            </label>
                            <label>
                                Hover strength
                                <input name="button_hover_mix" type="text" value="<?php echo esc_attr($settings['button_hover_mix']); ?>">
                            </label>
                        </div>
                    <?php ft_next_homepage_card_close(); ?>

                    <?php ft_next_homepage_card_open('Header'); ?>
                        <div class="ft-next-inline-grid ft-next-inline-grid--header">
                            <label>
                                Phone
                                <input name="phone" type="text" value="<?php echo esc_attr($settings['phone']); ?>">
                            </label>
                            <label>
                                Email
                                <input name="email" type="text" value="<?php echo esc_attr($settings['email']); ?>">
                            </label>
                            <label>
                                Header tagline
                                <input name="service_area" type="text" value="<?php echo esc_attr($settings['service_area']); ?>">
                            </label>
                            <label>
                                Header button label
                                <input name="cta_label" type="text" value="<?php echo esc_attr($settings['cta_label']); ?>">
                            </label>
                        </div>
                        <div class="ft-next-inline-grid ft-next-inline-grid--2">
                            <?php
                            ft_next_homepage_field($settings, 'show_header', 'Show header', 'checkbox');
                            ft_next_homepage_field($settings, 'show_footer', 'Show footer', 'checkbox');
                            ?>
                        </div>
                        <h3>Top menu</h3>
                        <table class="widefat striped ft-next-table">
                            <thead><tr><th>Name</th><th>Link</th></tr></thead>
                            <tbody>
                            <?php for ($i = 0; $i < 6; $i++) : $item = $settings['utility_links'][$i] ?? ['name' => '', 'href' => '']; ?>
                                <tr>
                                    <td><input name="utility_name[]" type="text" value="<?php echo esc_attr($item['name']); ?>"></td>
                                    <td><input name="utility_href[]" type="text" value="<?php echo esc_attr($item['href']); ?>"></td>
                                </tr>
                            <?php endfor; ?>
                            </tbody>
                        </table>

                        <h3>Main menu</h3>
                        <table class="widefat striped ft-next-table">
                            <thead><tr><th>Name</th><th>Link</th></tr></thead>
                            <tbody>
                            <?php for ($i = 0; $i < 5; $i++) : $item = $settings['nav_items'][$i] ?? ['name' => '', 'href' => '']; ?>
                                <tr>
                                    <td><input name="nav_name[]" type="text" value="<?php echo esc_attr($item['name']); ?>"></td>
                                    <td><input name="nav_href[]" type="text" value="<?php echo esc_attr($item['href']); ?>"></td>
                                </tr>
                            <?php endfor; ?>
                            </tbody>
                        </table>
                    <?php ft_next_homepage_card_close(); ?>

                    <?php ft_next_homepage_card_open('Hero'); ?>
                        <div class="ft-next-repeater">
                            <div class="ft-next-repeater-item ft-next-repeater-item--hero">
                                <h3>Hero settings</h3>
                                <div class="ft-next-field-stack">
                                    <div class="ft-next-subgroup">
                                        <h4>Main content</h4>
                                        <div class="ft-next-inline-grid ft-next-inline-grid--2" style="margin-bottom:0;">
                                            <label>
                                                Hero title
                                                <input name="hero_title" type="text" value="<?php echo esc_attr($settings['hero_title']); ?>">
                                            </label>
                                            <label>
                                                Hero highlighted text
                                                <input name="hero_highlight" type="text" value="<?php echo esc_attr($settings['hero_highlight']); ?>">
                                            </label>
                                        </div>
                                        <div class="ft-next-inline-grid ft-next-inline-grid--2" style="margin-bottom:0;">
                                            <label>
                                                Title size (desktop)
                                                <input name="hero_title_font_size" type="text" value="<?php echo esc_attr($settings['hero_title_font_size']); ?>">
                                                <span class="description">e.g. <code>60px</code> or <code>3.75rem</code></span>
                                            </label>
                                            <label>
                                                Title size (mobile)
                                                <input name="hero_title_mobile_font_size" type="text" value="<?php echo esc_attr($settings['hero_title_mobile_font_size']); ?>">
                                                <span class="description">e.g. <code>30px</code> or <code>1.875rem</code></span>
                                            </label>
                                        </div>
                                        <label>
                                            Hero paragraph
                                            <textarea name="hero_text" rows="4"><?php echo esc_textarea($settings['hero_text']); ?></textarea>
                                        </label>
                                    </div>

                                    <div class="ft-next-subgroup">
                                        <h4>Promo badge text</h4>
                                        <label>
                                            Promo badge
                                            <input name="hero_badge" type="text" value="<?php echo esc_attr($settings['hero_badge']); ?>">
                                        </label>
                                    </div>

                                    <div class="ft-next-subgroup">
                                        <h4>Promo badge style</h4>
                                        <div class="ft-next-inline-grid ft-next-inline-grid--badge-style" style="margin-bottom:0;">
                                            <label>
                                                Background color
                                                <?php ft_next_homepage_color_control($settings, 'hero_badge_bg_color'); ?>
                                            </label>
                                            <label>
                                                Text color
                                                <?php ft_next_homepage_color_control($settings, 'hero_badge_text_color'); ?>
                                            </label>
                                        </div>
                                        <div class="ft-next-inline-grid ft-next-inline-grid--badge-numbers" style="margin-bottom:0;">
                                            <label>
                                                Font size
                                                <input name="hero_badge_font_size" type="text" value="<?php echo esc_attr($settings['hero_badge_font_size']); ?>">
                                            </label>
                                            <label>
                                                Mobile font size
                                                <input name="hero_badge_mobile_font_size" type="text" value="<?php echo esc_attr($settings['hero_badge_mobile_font_size']); ?>">
                                            </label>
                                            <div class="ft-next-inline-grid ft-next-inline-grid--compact-values" style="margin-bottom:0;">
                                                <label>
                                                    Horizontal padding
                                                    <input name="hero_badge_padding_x" type="text" value="<?php echo esc_attr($settings['hero_badge_padding_x']); ?>">
                                                </label>
                                                <label>
                                                    Vertical padding
                                                    <input name="hero_badge_padding_y" type="text" value="<?php echo esc_attr($settings['hero_badge_padding_y']); ?>">
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="ft-next-subgroup">
                                        <h4>Promo badge animation</h4>
                                        <div class="ft-next-inline-grid ft-next-inline-grid--2" style="margin-bottom:0;">
                                            <label>
                                                Animation color 1
                                                <?php ft_next_homepage_color_control($settings, 'hero_badge_animation_color_1'); ?>
                                            </label>
                                            <label>
                                                Animation color 2
                                                <?php ft_next_homepage_color_control($settings, 'hero_badge_animation_color_2'); ?>
                                            </label>
                                        </div>
                                        <div class="ft-next-inline-grid ft-next-inline-grid--2" style="margin-bottom:0;">
                                            <label>
                                                Gradient location
                                                <?php ft_next_homepage_gradient_select('hero_badge_animation_location', $settings['hero_badge_animation_location']); ?>
                                            </label>
                                            <label>
                                                Animation speed
                                                <input name="hero_badge_animation_speed" type="text" value="<?php echo esc_attr($settings['hero_badge_animation_speed']); ?>">
                                            </label>
                                        </div>
                                    </div>

                                    <div class="ft-next-subgroup">
                                        <h4>Form text</h4>
                                        <div class="ft-next-inline-grid ft-next-inline-grid--2" style="margin-bottom:0;">
                                            <label>
                                                Form title
                                                <input name="form_title" type="text" value="<?php echo esc_attr($settings['form_title']); ?>">
                                            </label>
                                            <label>
                                                Form subtitle
                                                <input name="form_subtitle" type="text" value="<?php echo esc_attr($settings['form_subtitle']); ?>">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="ft-next-media-panel">
                                    <div class="ft-next-subgroup">
                                        <h4>Background image</h4>
                                        <div class="ft-next-inline-grid ft-next-hero-background-controls" style="margin-bottom:0;">
                                            <?php ft_next_homepage_field($settings, 'hero_show_background', 'Show background', 'checkbox'); ?>
                                            <?php ft_next_homepage_field($settings, 'hero_show_overlay', 'Show overlay', 'checkbox'); ?>
                                        </div>
                                        <label class="ft-next-hero-opacity">
                                            Overlay transparency
                                            <input name="hero_overlay_opacity" type="text" value="<?php echo esc_attr($settings['hero_overlay_opacity']); ?>">
                                            <span class="description">Use 0 to 1. Example: <code>0.72</code>, <code>0.35</code>, or <code>0</code>.</span>
                                        </label>
                                        <div class="ft-next-image-field">
                                            <div class="ft-next-image-row">
                                                <input name="hero_image" type="text" class="ft-next-image-input" value="<?php echo esc_attr($settings['hero_image']); ?>">
                                                <button type="button" class="button ft-next-image-button">Select</button>
                                            </div>
                                            <img class="ft-next-image-preview" src="<?php echo esc_url($settings['hero_image']); ?>" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php ft_next_homepage_card_close(); ?>

                    <?php ft_next_homepage_card_open('Section One'); ?>
                        <?php ft_next_homepage_gradient_controls($settings, 'process'); ?>
                        <div class="ft-next-field-stack" style="margin-bottom:16px;">
                            <label>
                                Section title
                                <input name="process_title" type="text" value="<?php echo esc_attr($settings['process_title']); ?>">
                            </label>
                            <label>
                                Section paragraph
                                <textarea name="process_text" rows="4"><?php echo esc_textarea($settings['process_text']); ?></textarea>
                            </label>
                        </div>
                        <div class="ft-next-repeater ft-next-repeater--process">
                            <?php foreach ($settings['process_steps'] as $index => $step) : ?>
                                <div class="ft-next-repeater-item">
                                    <h3>Step <?php echo esc_html((string) ($index + 1)); ?></h3>
                                    <div class="ft-next-field-stack">
                                        <label>
                                            Title
                                            <input name="process_step_title[]" type="text" value="<?php echo esc_attr($step['title']); ?>">
                                        </label>
                                        <label>
                                            Button label
                                            <input name="process_step_button[]" type="text" value="<?php echo esc_attr($step['button']); ?>">
                                        </label>
                                        <label>
                                            Description
                                            <textarea name="process_step_description[]" rows="4"><?php echo esc_textarea($step['description']); ?></textarea>
                                        </label>
                                    </div>
                                    <div class="ft-next-media-panel">
                                        <span>Image</span>
                                        <div class="ft-next-image-field">
                                            <div class="ft-next-image-row">
                                                <input name="process_step_image[]" type="text" class="ft-next-image-input" value="<?php echo esc_attr($step['image']); ?>">
                                                <button type="button" class="button ft-next-image-button">Select</button>
                                            </div>
                                            <img class="ft-next-image-preview" src="<?php echo esc_url($step['image']); ?>" alt="">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php ft_next_homepage_card_close(); ?>

                    <?php ft_next_homepage_card_open('Section Two'); ?>
                        <?php ft_next_homepage_gradient_controls($settings, 'comparison'); ?>
                        <div class="ft-next-field-stack" style="margin-bottom:16px;">
                            <div class="ft-next-inline-grid ft-next-inline-grid--3" style="margin-bottom:0;">
                                <label>
                                    Section title
                                    <input name="comparison_title" type="text" value="<?php echo esc_attr($settings['comparison_title']); ?>">
                                </label>
                                <label>
                                    Table title
                                    <input name="comparison_table_title" type="text" value="<?php echo esc_attr($settings['comparison_table_title']); ?>">
                                </label>
                                <label>
                                    Button label
                                    <input name="comparison_button" type="text" value="<?php echo esc_attr($settings['comparison_button']); ?>">
                                </label>
                            </div>
                            <label>
                                Section paragraph
                                <textarea name="comparison_text" rows="4"><?php echo esc_textarea($settings['comparison_text']); ?></textarea>
                            </label>
                        </div>
                        <div class="ft-next-field-stack">
                            <label for="comparison_rows">
                                Table rows
                                <textarea id="comparison_rows" name="comparison_rows" rows="10" class="large-text"><?php echo esc_textarea(implode("\n", $settings['comparison_rows'])); ?></textarea>
                                <small>One row per line. Each row gets a check mark under Floors Today.</small>
                            </label>
                        </div>
                    <?php ft_next_homepage_card_close(); ?>

                    <?php ft_next_homepage_card_open('Categories'); ?>
                        <?php ft_next_homepage_gradient_controls($settings, 'category'); ?>
                        <div class="ft-next-field-stack" style="margin-bottom:16px;">
                            <label>
                                Section title
                                <input name="category_title" type="text" value="<?php echo esc_attr($settings['category_title']); ?>">
                            </label>
                            <label>
                                Section paragraph
                                <textarea name="category_text" rows="4"><?php echo esc_textarea($settings['category_text']); ?></textarea>
                            </label>
                        </div>
                        <div class="ft-next-repeater ft-next-repeater--categories">
                            <?php for ($i = 0; $i < 5; $i++) : $cat = $settings['categories'][$i] ?? ['name' => '', 'slug' => '', 'description' => '', 'image' => '']; ?>
                                <div class="ft-next-repeater-item">
                                    <h3>Category <?php echo esc_html((string) ($i + 1)); ?></h3>
                                    <div class="ft-next-field-stack">
                                        <label>
                                            Name
                                            <input name="category_name[]" type="text" value="<?php echo esc_attr($cat['name']); ?>">
                                        </label>
                                        <label>
                                            Slug
                                            <input name="category_slug[]" type="text" value="<?php echo esc_attr($cat['slug']); ?>">
                                        </label>
                                        <label>
                                            Description
                                            <textarea name="category_description[]" rows="4"><?php echo esc_textarea($cat['description']); ?></textarea>
                                        </label>
                                    </div>
                                    <div class="ft-next-media-panel">
                                        <span>Image</span>
                                        <div class="ft-next-image-field">
                                            <div class="ft-next-image-row">
                                                <input name="category_image[]" type="text" class="ft-next-image-input" value="<?php echo esc_attr($cat['image']); ?>">
                                                <button type="button" class="button ft-next-image-button">Select</button>
                                            </div>
                                            <img class="ft-next-image-preview" src="<?php echo esc_url($cat['image']); ?>" alt="">
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php ft_next_homepage_card_close(); ?>

                    <?php ft_next_homepage_card_open('Guarantee'); ?>
                        <?php ft_next_homepage_gradient_controls($settings, 'guarantee'); ?>
                        <div class="ft-next-repeater">
                            <div class="ft-next-repeater-item">
                                <h3>Guarantee content</h3>
                                <div class="ft-next-field-stack">
                                    <div class="ft-next-inline-grid ft-next-inline-grid--2" style="margin-bottom:0;">
                                        <label>
                                            Title
                                            <input name="guarantee_title" type="text" value="<?php echo esc_attr($settings['guarantee_title']); ?>">
                                        </label>
                                        <label>
                                            Subtitle
                                            <input name="guarantee_subtitle" type="text" value="<?php echo esc_attr($settings['guarantee_subtitle']); ?>">
                                        </label>
                                    </div>
                                    <label>
                                        Text
                                        <textarea name="guarantee_text" rows="5"><?php echo esc_textarea($settings['guarantee_text']); ?></textarea>
                                    </label>
                                    <label>
                                        Link label
                                        <input name="guarantee_link" type="text" value="<?php echo esc_attr($settings['guarantee_link']); ?>">
                                    </label>
                                </div>
                                <div class="ft-next-media-panel">
                                    <span>Image</span>
                                    <div class="ft-next-image-field">
                                        <div class="ft-next-image-row">
                                            <input name="guarantee_image" type="text" class="ft-next-image-input" value="<?php echo esc_attr($settings['guarantee_image']); ?>">
                                            <button type="button" class="button ft-next-image-button">Select</button>
                                        </div>
                                        <img class="ft-next-image-preview" src="<?php echo esc_url($settings['guarantee_image']); ?>" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php ft_next_homepage_card_close(); ?>

                    <?php ft_next_homepage_card_open('Deals'); ?>
                        <?php ft_next_homepage_gradient_controls($settings, 'deals'); ?>
                        <div class="ft-next-inline-grid ft-next-inline-grid--2">
                            <label>
                                Badge
                                <input name="deals_badge" type="text" value="<?php echo esc_attr($settings['deals_badge']); ?>">
                            </label>
                            <label>
                                Page title
                                <input name="deals_title" type="text" value="<?php echo esc_attr($settings['deals_title']); ?>">
                            </label>
                        </div>

                        <div class="ft-next-field-stack" style="margin-bottom:16px;">
                            <label>
                                Intro text
                                <textarea name="deals_text" rows="3"><?php echo esc_textarea($settings['deals_text']); ?></textarea>
                            </label>
                            <label>
                                Body text
                                <textarea name="deals_body" rows="4"><?php echo esc_textarea($settings['deals_body']); ?></textarea>
                            </label>
                        </div>

                        <div class="ft-next-inline-grid ft-next-inline-grid--3">
                            <label>
                                Sale card title
                                <input name="deals_card_title" type="text" value="<?php echo esc_attr($settings['deals_card_title']); ?>">
                            </label>
                            <label>
                                Sale card subtitle
                                <input name="deals_card_subtitle" type="text" value="<?php echo esc_attr($settings['deals_card_subtitle']); ?>">
                            </label>
                            <label>
                                Button label
                                <input name="deals_button" type="text" value="<?php echo esc_attr($settings['deals_button']); ?>">
                            </label>
                        </div>

                        <div class="ft-next-inline-grid ft-next-inline-grid--2">
                            <label>
                                Details link label
                                <input name="deals_details_label" type="text" value="<?php echo esc_attr($settings['deals_details_label']); ?>">
                            </label>
                            <label>
                                Included items heading
                                <input name="deals_includes_title" type="text" value="<?php echo esc_attr($settings['deals_includes_title']); ?>">
                            </label>
                        </div>
                        <label>
                            Included items
                            <textarea name="deals_includes" rows="4" class="large-text"><?php echo esc_textarea($settings['deals_includes']); ?></textarea>
                            <small>Enter one item per line.</small>
                        </label>

                        <h3>Offer details popup</h3>
                        <div class="ft-next-inline-grid ft-next-inline-grid--2">
                            <label>
                                Eyebrow
                                <input name="deals_popup_eyebrow" type="text" value="<?php echo esc_attr($settings['deals_popup_eyebrow']); ?>">
                            </label>
                            <label>
                                Steps heading
                                <input name="deals_popup_steps_title" type="text" value="<?php echo esc_attr($settings['deals_popup_steps_title']); ?>">
                            </label>
                            <label>
                                Appointment button
                                <input name="deals_popup_button" type="text" value="<?php echo esc_attr($settings['deals_popup_button']); ?>">
                            </label>
                        </div>
                        <div class="ft-next-popup-fields">
                            <label>
                                Popup title
                                <textarea name="deals_popup_title" rows="3" class="large-text"><?php echo esc_textarea($settings['deals_popup_title']); ?></textarea>
                                <small>Use &lt;br&gt; tags for line breaks.</small>
                            </label>
                            <label>
                                Popup introduction
                                <textarea name="deals_popup_intro" rows="3" class="large-text"><?php echo esc_textarea($settings['deals_popup_intro']); ?></textarea>
                            </label>
                            <label>
                                Popup steps
                                <textarea name="deals_popup_steps" rows="6" class="large-text"><?php echo esc_textarea($settings['deals_popup_steps']); ?></textarea>
                                <small>One step per line. Use Title|Description. Use {phone} for the website phone number.</small>
                            </label>
                            <label>
                                Main offer terms
                                <textarea name="deals_popup_terms" rows="6" class="large-text"><?php echo esc_textarea($settings['deals_popup_terms']); ?></textarea>
                            </label>
                            <label>
                                Additional offer terms
                                <textarea name="deals_popup_terms_extra" rows="4" class="large-text"><?php echo esc_textarea($settings['deals_popup_terms_extra']); ?></textarea>
                            </label>
                        </div>

                        <div class="ft-next-repeater ft-next-repeater--offers">
                            <?php for ($i = 0; $i < 4; $i++) : $offer = $settings['offers'][$i] ?? ['title' => '', 'description' => '']; ?>
                                <div class="ft-next-repeater-item ft-next-repeater-item--single">
                                    <h3>Offer <?php echo esc_html((string) ($i + 1)); ?></h3>
                                    <div class="ft-next-field-stack">
                                        <label>
                                            Offer title
                                            <input name="offer_title[]" type="text" value="<?php echo esc_attr($offer['title']); ?>">
                                        </label>
                                        <label>
                                            Description
                                            <textarea name="offer_description[]" rows="3"><?php echo esc_textarea($offer['description']); ?></textarea>
                                        </label>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php ft_next_homepage_card_close(); ?>

                    <?php ft_next_homepage_card_open('Testimonials'); ?>
                        <?php ft_next_homepage_gradient_controls($settings, 'testimonials'); ?>
                        <div class="ft-next-field-stack" style="margin-bottom:16px;">
                            <div class="ft-next-inline-grid ft-next-inline-grid--2" style="margin-bottom:0;">
                                <?php ft_next_homepage_field($settings, 'show_testimonials', 'Show testimonials section', 'checkbox'); ?>
                            </div>
                            <label>
                                Section title
                                <input name="testimonials_title" type="text" value="<?php echo esc_attr($settings['testimonials_title']); ?>">
                            </label>
                            <label>
                                Section paragraph
                                <textarea name="testimonials_text" rows="4"><?php echo esc_textarea($settings['testimonials_text']); ?></textarea>
                            </label>
                            <label>
                                Embed code <small style="font-weight:400;color:#637083;">(paste a &lt;script&gt; tag to replace the testimonials with a review widget — leave empty to use the cards above)</small>
                                <textarea name="testimonials_embed_code" rows="4" placeholder="&lt;script id=&quot;...&quot; src=&quot;https://...&quot; defer&gt;&lt;/script&gt;"><?php echo esc_textarea($settings['testimonials_embed_code']); ?></textarea>
                            </label>
                        </div>
                        <div class="ft-next-testimonials-grid">
                            <?php for ($i = 0; $i < 3; $i++) : $item = $settings['testimonials'][$i] ?? ['name' => '', 'location' => '', 'floorType' => '', 'text' => '']; ?>
                                <div class="ft-next-testimonial-editor">
                                    <h3>Testimonial <?php echo esc_html((string) ($i + 1)); ?></h3>
                                    <label>
                                        Name
                                        <input name="testimonial_name[]" type="text" value="<?php echo esc_attr($item['name']); ?>">
                                    </label>
                                    <label>
                                        Location
                                        <input name="testimonial_location[]" type="text" value="<?php echo esc_attr($item['location']); ?>">
                                    </label>
                                    <label>
                                        Floor type
                                        <input name="testimonial_floor_type[]" type="text" value="<?php echo esc_attr($item['floorType']); ?>">
                                    </label>
                                    <label>
                                        Text
                                        <textarea name="testimonial_text[]" rows="6"><?php echo esc_textarea($item['text']); ?></textarea>
                                    </label>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php ft_next_homepage_card_close(); ?>

                    <?php ft_next_homepage_card_open('CTA and Footer'); ?>
                        <?php ft_next_homepage_gradient_controls($settings, 'cta'); ?>
                        <?php ft_next_homepage_gradient_controls($settings, 'footer'); ?>
                        <div class="ft-next-field-stack">
                            <div class="ft-next-inline-grid ft-next-inline-grid--2" style="margin-bottom:0;">
                                <label>
                                    CTA title
                                    <input name="cta_title" type="text" value="<?php echo esc_attr($settings['cta_title']); ?>">
                                </label>
                                <label>
                                    CTA subtitle
                                    <input name="cta_subtitle" type="text" value="<?php echo esc_attr($settings['cta_subtitle']); ?>">
                                </label>
                            </div>
                            <label>
                                CTA paragraph
                                <textarea name="cta_text" rows="4"><?php echo esc_textarea($settings['cta_text']); ?></textarea>
                            </label>
                            <div class="ft-next-inline-grid ft-next-inline-grid--2" style="margin-bottom:0;">
                                <label>
                                    CTA button label
                                    <input name="cta_button" type="text" value="<?php echo esc_attr($settings['cta_button']); ?>">
                                </label>
                                <label>
                                    Newsletter title
                                    <input name="newsletter_title" type="text" value="<?php echo esc_attr($settings['newsletter_title']); ?>">
                                </label>
                            </div>
                            <div class="ft-next-inline-grid ft-next-inline-grid--2" style="margin-bottom:0;">
                                <label>
                                    Popup title
                                    <input name="newsletter_popup_title" type="text" value="<?php echo esc_attr($settings['newsletter_popup_title']); ?>">
                                </label>
                            </div>
                            <div class="ft-next-inline-grid ft-next-inline-grid--2" style="margin-bottom:0;">
                                <label>
                                    Newsletter text
                                    <input name="newsletter_text" type="text" value="<?php echo esc_attr($settings['newsletter_text']); ?>">
                                </label>
                                <label>
                                    Newsletter button
                                    <input name="newsletter_button" type="text" value="<?php echo esc_attr($settings['newsletter_button']); ?>">
                                </label>
                            </div>
                            <div class="ft-next-inline-grid ft-next-inline-grid--2" style="margin-bottom:0;">
                                <label>
                                    Newsletter claim button
                                    <input name="newsletter_claim_button" type="text" value="<?php echo esc_attr($settings['newsletter_claim_button']); ?>">
                                </label>
                            </div>
                            <label>
                                Newsletter "See Details" popup text
                                <textarea name="newsletter_details_text" rows="3"><?php echo esc_textarea($settings['newsletter_details_text']); ?></textarea>
                            </label>
                            <h3>Warranty Details Popup</h3>
                            <p class="description">Appears as a "View warranty details" link under the trust badges. Supports HTML tags: &lt;br&gt;, &lt;p&gt;, &lt;strong&gt;, &lt;b&gt;, &lt;em&gt;.</p>
                            <label>
                                Warranty popup title
                                <input name="warranty_popup_title" type="text" value="<?php echo esc_attr($settings['warranty_popup_title']); ?>">
                            </label>
                            <label>
                                Warranty details content
                                <textarea name="warranty_details_content" rows="6"><?php echo esc_textarea($settings['warranty_details_content']); ?></textarea>
                            </label>
                            <div class="ft-next-inline-grid ft-next-inline-grid--2">
                                <label>
                                    "See More" button label
                                    <input name="warranty_see_more_text" type="text" placeholder="See More" value="<?php echo esc_attr($settings['warranty_see_more_text']); ?>">
                                </label>
                                <label>
                                    "See More" button URL (slug or full URL)
                                    <input name="warranty_see_more_url" type="text" placeholder="warranty or https://..." value="<?php echo esc_attr($settings['warranty_see_more_url']); ?>">
                                </label>
                            </div>
                            <label>
                                Footer about text
                                <textarea name="footer_about" rows="4"><?php echo esc_textarea($settings['footer_about']); ?></textarea>
                            </label>
                            <h3>Footer menus</h3>
                            <div class="ft-next-inline-grid ft-next-inline-grid--4">
                                <label>
                                    About menu title
                                    <input name="footer_about_title" type="text" value="<?php echo esc_attr($settings['footer_about_title']); ?>">
                                </label>
                                <label>
                                    Categories menu title
                                    <input name="footer_categories_title" type="text" value="<?php echo esc_attr($settings['footer_categories_title']); ?>">
                                </label>
                                <label>
                                    Help menu title
                                    <input name="footer_help_title" type="text" value="<?php echo esc_attr($settings['footer_help_title']); ?>">
                                </label>
                                <label>
                                    Policies menu title
                                    <input name="footer_policies_title" type="text" value="<?php echo esc_attr($settings['footer_policies_title']); ?>">
                                </label>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
                                <?php
                                $footer_link_cols = [
                                    'about'  => ['title' => 'About menu links',       'key' => 'footer_about_links'],
                                    'help'   => ['title' => 'Help menu links',         'key' => 'footer_help_links'],
                                    'policy' => ['title' => 'Policy menu links',       'key' => 'footer_policy_links'],
                                    'bottom' => ['title' => 'Bottom footer links',     'key' => 'footer_bottom_links'],
                                ];
                                foreach ($footer_link_cols as $prefix => $col) :
                                    $raw = $settings[$col['key']] ?? [];
                                    $links = is_array($raw) ? $raw : ft_next_homepage_parse_link_string((string) $raw);
                                ?>
                                <div>
                                    <strong style="display:block;margin-bottom:8px;font-size:13px;"><?php echo esc_html($col['title']); ?></strong>
                                    <table class="widefat striped ft-next-table" style="margin-bottom:0;">
                                        <thead><tr><th>Label</th><th>URL</th></tr></thead>
                                        <tbody>
                                        <?php for ($i = 0; $i < 8; $i++) :
                                            $item = $links[$i] ?? ['label' => '', 'url' => ''];
                                        ?>
                                            <tr>
                                                <td><input name="footer_<?php echo esc_attr($prefix); ?>_label[]" type="text" value="<?php echo esc_attr($item['label']); ?>"></td>
                                                <td><input name="footer_<?php echo esc_attr($prefix); ?>_url[]" type="text" value="<?php echo esc_attr($item['url']); ?>"></td>
                                            </tr>
                                        <?php endfor; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <p class="description">Category links use the main Header menu above. Leave rows blank to skip them.</p>
                            <label>
                                Copyright text
                                <input name="footer_copyright" type="text" value="<?php echo esc_attr($settings['footer_copyright']); ?>">
                                <small>Use {year} for the current year.</small>
                            </label>
                            <div class="ft-next-inline-grid ft-next-inline-grid--3">
                                <label>
                                    Facebook URL
                                    <input name="facebook_url" type="url" value="<?php echo esc_attr($settings['facebook_url']); ?>">
                                </label>
                                <label>
                                    Instagram URL
                                    <input name="instagram_url" type="url" value="<?php echo esc_attr($settings['instagram_url']); ?>">
                                </label>
                                <label>
                                    LinkedIn URL
                                    <input name="linkedin_url" type="url" value="<?php echo esc_attr($settings['linkedin_url']); ?>">
                                </label>
                                <label>
                                    YouTube URL
                                    <input name="youtube_url" type="url" value="<?php echo esc_attr($settings['youtube_url']); ?>">
                                </label>
                                <label>
                                    TikTok URL
                                    <input name="tiktok_url" type="url" value="<?php echo esc_attr($settings['tiktok_url']); ?>">
                                </label>
                            </div>
                            <h3>Footer trust badge logos</h3>
                            <p class="description">Up to 6 badge / certification logos shown on the right of the newsletter bar. Leave blank to hide.</p>
                            <div class="ft-next-inline-grid ft-next-inline-grid--2" style="margin-bottom:12px;">
                                <label>
                                    Badge image height
                                    <input name="footer_badge_height" type="text" value="<?php echo esc_attr($settings['footer_badge_height'] ?? '60px'); ?>" placeholder="60px">
                                    <small>e.g. 48px, 64px, 80px</small>
                                </label>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;">
                                <?php for ($bi = 1; $bi <= 6; $bi++) :
                                    $badge_field = 'footer_badge_image_' . $bi;
                                    $badge_val   = esc_attr($settings[$badge_field] ?? '');
                                ?>
                                <div>
                                    <label style="display:block;font-weight:600;margin-bottom:6px;font-size:13px;">Badge <?php echo $bi; ?></label>
                                    <div class="ft-next-image-field">
                                        <div class="ft-next-image-row">
                                            <input id="<?php echo esc_attr($badge_field); ?>" name="<?php echo esc_attr($badge_field); ?>" type="text" class="regular-text ft-next-image-input" value="<?php echo $badge_val; ?>">
                                            <button type="button" class="button ft-next-image-button">Select</button>
                                        </div>
                                        <img class="ft-next-image-preview" src="<?php echo esc_url($settings[$badge_field] ?? ''); ?>" alt="" loading="lazy"<?php echo empty($settings[$badge_field]) ? ' style="display:none"' : ''; ?>>
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php ft_next_homepage_card_close(); ?>
                </div>

                <aside class="ft-next-sidebar">
                    <?php ft_next_homepage_card_open('Home SEO'); ?>
                        <div class="ft-next-field-stack">
                            <label>
                                SEO title
                                <input name="seo_title" type="text" maxlength="70" value="<?php echo esc_attr($settings['seo_title']); ?>">
                                <small>Recommended: 50-60 characters.</small>
                            </label>
                            <label>
                                Meta description
                                <textarea name="seo_description" rows="3" maxlength="170"><?php echo esc_textarea($settings['seo_description']); ?></textarea>
                                <small>Recommended: 140-160 characters.</small>
                            </label>
                            <label>
                                Canonical URL
                                <input name="seo_canonical_url" type="url" value="<?php echo esc_attr($settings['seo_canonical_url']); ?>">
                            </label>
                            <label>
                                Robots
                                <input name="seo_robots" type="text" value="<?php echo esc_attr($settings['seo_robots']); ?>">
                            </label>
                            <label>
                                Social sharing title
                                <input name="seo_og_title" type="text" value="<?php echo esc_attr($settings['seo_og_title']); ?>">
                            </label>
                            <label>
                                Social sharing description
                                <textarea name="seo_og_description" rows="3"><?php echo esc_textarea($settings['seo_og_description']); ?></textarea>
                            </label>
                            <div class="ft-next-media-panel">
                                <span>Social sharing image</span>
                                <div class="ft-next-image-field">
                                    <div class="ft-next-image-row">
                                        <input name="seo_og_image" type="text" class="ft-next-image-input" value="<?php echo esc_attr($settings['seo_og_image']); ?>">
                                        <button type="button" class="button ft-next-image-button">Select</button>
                                    </div>
                                    <img class="ft-next-image-preview" src="<?php echo esc_url($settings['seo_og_image']); ?>" alt="">
                                </div>
                            </div>
                        </div>
                    <?php ft_next_homepage_card_close(); ?>

                    <?php ft_next_homepage_card_open('Logo'); ?>
                        <div class="ft-next-sidebar-logo">
                            <label>
                                Logo text
                                <input name="logo_text" type="text" value="<?php echo esc_attr($settings['logo_text']); ?>">
                            </label>
                            <label>
                                Logo size
                                <input name="logo_size" type="text" value="<?php echo esc_attr($settings['logo_size']); ?>">
                            </label>
                            <div class="ft-next-media-panel">
                                <span>Logo image</span>
                                <div class="ft-next-image-field">
                                    <div class="ft-next-image-row">
                                        <input name="logo_image" type="text" class="ft-next-image-input" value="<?php echo esc_attr($settings['logo_image']); ?>">
                                        <button type="button" class="button ft-next-image-button">Select</button>
                                    </div>
                                    <img class="ft-next-image-preview" src="<?php echo esc_url($settings['logo_image']); ?>" alt="">
                                </div>
                            </div>
                            <div class="ft-next-media-panel">
                                <span>Favicon</span>
                                <div class="ft-next-image-field">
                                    <div class="ft-next-image-row">
                                        <input name="favicon_image" type="text" class="ft-next-image-input" value="<?php echo esc_attr($settings['favicon_image']); ?>">
                                        <button type="button" class="button ft-next-image-button">Select</button>
                                    </div>
                                    <img class="ft-next-image-preview" src="<?php echo esc_url($settings['favicon_image']); ?>" alt="">
                                </div>
                            </div>
                        </div>
                    <?php ft_next_homepage_card_close(); ?>

                    <div class="ft-next-preview" style="<?php echo esc_attr('--ft-primary:' . $settings['primary_color'] . ';--ft-secondary:' . $settings['secondary_color'] . ';--ft-bg:' . $settings['background_color'] . ';--ft-fg:' . $settings['foreground_color'] . ';'); ?>">
                        <div class="ft-next-preview__top">
                            <strong><?php echo esc_html($settings['logo_text']); ?></strong>
                            <p style="margin:8px 0 0;"><?php echo esc_html($settings['service_area']); ?></p>
                        </div>
                        <div class="ft-next-preview__body">
                            <p style="margin:0 0 8px;font-size:12px;font-weight:700;color:var(--ft-secondary);"><?php echo esc_html($settings['hero_badge']); ?></p>
                            <h3 style="margin:0 0 8px;font-size:24px;line-height:1.15;color:var(--ft-fg);">
                                <?php echo esc_html($settings['hero_title']); ?>
                                <span style="color:var(--ft-secondary);"><?php echo esc_html($settings['hero_highlight']); ?></span>
                            </h3>
                            <p style="margin:0;color:var(--ft-fg);opacity:.78;"><?php echo esc_html($settings['hero_text']); ?></p>
                            <span class="ft-next-preview__button"><?php echo esc_html($settings['cta_label']); ?></span>
                        </div>
                    </div>

                </aside>
            </div>

            <div style="margin-top:32px;">
            <?php ft_next_homepage_card_open('Integrations'); ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:start;">
                    <div class="ft-next-field-stack" style="gap:28px;">
                        <label>
                            Google Places API Key
                            <input name="google_places_api_key" type="text" value="<?php echo esc_attr($settings['google_places_api_key'] ?? ''); ?>" placeholder="AIza..." style="font-family:monospace">
                            <span class="description">Enables live address autocomplete on the booking form. Requires the <strong>Places API</strong> enabled in your project. <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">Create API Key →</a></span>
                        </label>
                        <label>
                            Facebook Pixel ID
                            <input name="fb_pixel_id" type="text" value="<?php echo esc_attr($settings['fb_pixel_id'] ?? ''); ?>" placeholder="1234567890123456" style="font-family:monospace">
                            <span class="description">Your numeric Pixel ID — fires a PageView event on every page automatically. No need to paste the full script. <a href="https://www.facebook.com/events_manager2/list/pixel/" target="_blank" rel="noopener">Get Pixel ID →</a></span>
                        </label>
                    </div>
                    <div class="ft-next-field-stack" style="gap:28px;">
                        <label>
                            Chat Widget Embed Code
                            <textarea name="chat_embed_code" rows="4" placeholder="&lt;script id=&quot;...&quot; src=&quot;https://...&quot; defer&gt;&lt;/script&gt;" style="font-family:monospace"><?php echo esc_textarea($settings['chat_embed_code'] ?? ''); ?></textarea>
                            <span class="description">Paste the <code>&lt;script&gt;</code> tag from your chat provider. Loads on every page — homepage and all other pages. Works with Tidio, LiveChat, Intercom, and others.</span>
                        </label>
                        <label>
                            reCAPTCHA v3 Site Key
                            <input name="recaptcha_site_key" type="text" value="<?php echo esc_attr($settings['recaptcha_site_key'] ?? ''); ?>" placeholder="6Lc..." style="font-family:monospace">
                            <span class="description">Adds invisible bot protection to the homepage booking form — visitors never see a challenge. <a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener">Create reCAPTCHA v3 key →</a></span>
                        </label>
                    </div>
                </div>
            <?php ft_next_homepage_card_close(); ?>
            </div>

            <div class="ft-next-actions">
                <a class="button" href="<?php echo esc_url($frontend_url); ?>" target="_blank" rel="noopener">View Front End</a>
                <?php submit_button('Save Settings', 'primary', 'submit', false); ?>
            </div>
        </form>
    </div>
    <?php
}

function ft_next_homepage_render_utm_admin() {
    ?>
    <div class="wrap ft-next-admin">
        <div class="ft-next-hero">
            <div>
                <h1>Social UTM Links</h1>
                <p class="ft-next-muted">Copy these links into social posts so leads show the original traffic source in the inbox.</p>
                <nav class="ft-next-quicknav" aria-label="UTM sections"></nav>
            </div>
            <div class="ft-next-endpoint">
                <strong>Tracking domain</strong>
                <code>https://floorstoday.ca/</code>
                <strong style="display:block;margin-top:12px;">Lead attribution</strong>
                <code>hello_social saved for 90 days</code>
            </div>
        </div>

        <?php ft_next_homepage_card_open('Social UTM Links'); ?>
            <p class="description" style="margin-top:0;">Copy the matching link into each social profile, post, bio link, or ad.</p>
            <table class="widefat striped ft-next-table ft-next-utm-table">
                <thead><tr><th>Social</th><th>Tracking link</th></tr></thead>
                <tbody>
                <?php foreach (ft_next_homepage_utm_links() as $utm_link) : ?>
                    <tr>
                        <td><?php echo esc_html($utm_link['label']); ?></td>
                        <td>
                            <div class="ft-next-utm-copy-row">
                                <input type="text" readonly onclick="this.select();" value="<?php echo esc_attr($utm_link['url']); ?>">
                                <button type="button" class="ft-next-utm-copy" aria-label="<?php echo esc_attr('Copy ' . $utm_link['label'] . ' link'); ?>" data-copy="<?php echo esc_attr($utm_link['url']); ?>">
                                    <span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php ft_next_homepage_card_close(); ?>
    </div>
    <script>
        document.addEventListener('click', function (event) {
            var button = event.target.closest('.ft-next-utm-copy');

            if (!button) {
                return;
            }

            var value = button.getAttribute('data-copy') || '';
            var done = function () {
                button.classList.add('is-copied');
                button.innerHTML = '<span class="dashicons dashicons-yes" aria-hidden="true"></span>';
                setTimeout(function () {
                    button.classList.remove('is-copied');
                    button.innerHTML = '<span class="dashicons dashicons-admin-page" aria-hidden="true"></span>';
                }, 1400);
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(value).then(done).catch(function () {
                    var input = button.closest('.ft-next-utm-copy-row').querySelector('input');
                    input.select();
                    document.execCommand('copy');
                    done();
                });
                return;
            }

            var input = button.closest('.ft-next-utm-copy-row').querySelector('input');
            input.select();
            document.execCommand('copy');
            done();
        });
    </script>
    <?php
}

function ft_next_header_shortcode_url($url) {
    $url = trim((string) $url);

    if ($url === '' || $url === '#') {
        return '#';
    }

    if (str_starts_with($url, '#')) {
        return $url;
    }

    if (str_starts_with($url, '/')) {
        return home_url($url);
    }

    if (preg_match('#^(?:https?:|mailto:|tel:)#i', $url)) {
        return $url;
    }

    return home_url('/' . ltrim($url, '/'));
}

function ft_next_header_shortcode() {
    static $assets_printed = false;

    $settings = ft_next_homepage_settings();

    if (($settings['show_header'] ?? '1') !== '1') {
        return '';
    }

    $instance_id = wp_unique_id('ft-shortcode-header-');
    $logo_text = (string) ($settings['logo_text'] ?? get_bloginfo('name'));
    $logo_image = (string) ($settings['logo_image'] ?? '');
    $service_area = (string) ($settings['service_area'] ?? '');
    $phone = (string) ($settings['phone'] ?? '');
    $phone_href = preg_replace('/[^0-9+]/', '', $phone);
    $logo_size = trim((string) ($settings['logo_size'] ?? '250px'));
    $primary_color = (string) ($settings['primary_color'] ?? '#155f99');
    $foreground_color = (string) ($settings['foreground_color'] ?? '#1e1311');
    $nav_items = is_array($settings['nav_items'] ?? null) ? $settings['nav_items'] : [];
    $utility_links = ft_next_header_utility_links($settings);

    if (!preg_match('/^\d+(?:\.\d+)?(?:px|rem|em|%)$/', $logo_size)) {
        $logo_size = '250px';
    }

    ob_start();

    if (!$assets_printed) {
        $assets_printed = true;
        ?>
        <style id="ft-header-shortcode-styles">
            .ft-sh-header,
            .ft-sh-header * { box-sizing: border-box; }
            .ft-sh-header {
                position: sticky;
                top: 0;
                z-index: 999;
                width: 100%;
                background: #fff;
                color: var(--ft-sh-foreground);
                box-shadow: 0 1px 5px rgba(17, 24, 39, .12);
                font-family: inherit;
            }
            .admin-bar .ft-sh-header { top: 0; }
            @media (min-width: 641px) {
                body:not(.admin-bar):has(.ft-sh-header),
                body:not(.admin-bar):has(.ft-homepage-shell > header) {
                    margin-top: 0 !important;
                    padding-top: 0 !important;
                }
                body:not(.admin-bar) .ft-sh-header,
                body:not(.admin-bar) .ft-homepage-shell > header {
                    top: 0 !important;
                    margin-top: 0 !important;
                }
            }
            .ft-sh-inner {
                width: min(100% - 32px, 1340px);
                margin-inline: auto;
            }
            .ft-sh-topbar {
                background: var(--ft-sh-primary);
                color: #fff;
                font-size: 14px;
            }
            .ft-sh-topbar-inner,
            .ft-sh-main {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
            }
            .ft-sh-topbar-inner { min-height: 40px; }
            .ft-sh-location,
            .ft-sh-phone,
            .ft-sh-utility,
            .ft-sh-nav {
                display: flex;
                align-items: center;
            }
            .ft-sh-location { min-width: 0; gap: 8px; }
            .ft-sh-location span {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .ft-sh-icon {
                width: 17px;
                height: 17px;
                flex: 0 0 auto;
            }
            .ft-sh-utility { gap: 24px; }
            .ft-sh-header a {
                color: inherit;
                text-decoration: none;
            }
            .ft-sh-utility a:hover,
            .ft-sh-utility a:focus-visible { text-decoration: underline; }
            .ft-sh-main { min-height: 72px; padding-block: 4px; }
            .ft-sh-logo {
                display: flex;
                min-width: 0;
                align-items: center;
                color: var(--ft-sh-primary) !important;
                font-size: 24px;
                font-weight: 700;
            }
            .ft-sh-logo img {
                display: block;
                width: var(--ft-sh-logo-size);
                max-width: 250px;
                max-height: 64px;
                height: auto;
                object-fit: contain;
            }
            .ft-sh-nav { gap: 24px; }
            .ft-sh-nav a {
                color: var(--ft-sh-foreground);
                font-size: 16px;
                font-weight: 600;
                white-space: nowrap;
                transition: color .18s ease;
            }
            .ft-sh-nav a:hover,
            .ft-sh-nav a:focus-visible,
            .ft-sh-phone:hover,
            .ft-sh-phone:focus-visible { color: var(--ft-sh-primary); }
            .ft-sh-actions {
                display: flex;
                flex: 0 0 auto;
                align-items: center;
                gap: 16px;
            }
            .ft-sh-phone {
                gap: 8px;
                color: var(--ft-sh-foreground);
                font-size: 16px;
                font-weight: 700;
                white-space: nowrap;
            }
            .ft-sh-phone .ft-sh-icon { color: var(--ft-sh-primary); }
            .ft-sh-menu-toggle {
                display: none;
                width: 44px;
                height: 44px;
                padding: 0;
                align-items: center;
                justify-content: center;
                border: 0;
                border-radius: 6px;
                background: transparent;
                color: var(--ft-sh-foreground);
                cursor: pointer;
            }
            .ft-sh-menu-toggle:hover,
            .ft-sh-menu-toggle:focus-visible { background: #f3f5f7; }
            .ft-sh-mobile-menu {
                display: none;
                padding: 8px 16px 18px;
                border-top: 1px solid #e5e7eb;
                background: #fff;
            }
            .ft-sh-mobile-menu[hidden] { display: none !important; }
            .ft-sh-mobile-menu a {
                display: block;
                padding: 10px 0;
                color: var(--ft-sh-foreground);
                font-weight: 600;
            }
            .ft-sh-mobile-menu a:hover,
            .ft-sh-mobile-menu a:focus-visible { color: var(--ft-sh-primary); }
            .ft-sh-mobile-phone { border-top: 1px solid #e5e7eb; margin-top: 6px; }
            @media (max-width: 1100px) {
                .ft-sh-nav { display: none; }
                .ft-sh-menu-toggle { display: inline-flex; }
                .ft-sh-mobile-menu:not([hidden]) { display: block; }
            }
            @media (max-width: 782px) {
                .admin-bar .ft-sh-header { top: 0; }
            }
            @media (max-width: 640px) {
                body:has(.ft-sh-header) { padding-top: 101px; }
                .ft-sh-header {
                    position: fixed !important;
                    top: 0;
                    left: 0;
                    right: 0;
                    z-index: 1000;
                    box-shadow: 0 1px 5px rgba(17, 24, 39, .12);
                }
                .admin-bar .ft-sh-header { top: 0; }
                .ft-sh-inner { width: 100%; padding-inline: 16px; }
                .ft-sh-utility,
                .ft-sh-actions > .ft-sh-phone { display: none; }
                .ft-sh-topbar { font-size: 12px; }
                .ft-sh-topbar-inner {
                    min-height: 37px;
                    justify-content: flex-start;
                    padding-block: 0;
                }
                .ft-sh-location { gap: 8px; }
                .ft-sh-location .ft-sh-icon { width: 16px; height: 16px; }
                .ft-sh-main {
                    min-height: 64px;
                    padding-block: 0;
                    background: #fff;
                }
                .ft-sh-logo img {
                    width: 300px;
                    max-width: 180px;
                    max-height: 56px;
                }
                .ft-sh-menu-toggle {
                    display: inline-flex;
                    width: 44px;
                    height: 44px;
                    border-radius: 6px;
                    background: transparent;
                    color: #111827;
                }
                .ft-sh-menu-toggle .ft-sh-icon {
                    width: 24px;
                    height: 24px;
                }
                .ft-sh-mobile-menu {
                    padding: 8px 16px 16px;
                    box-shadow: 0 10px 20px rgba(0, 0, 0, .08);
                }
            }
        </style>
        <script id="ft-header-shortcode-script">
            document.addEventListener('click', function (event) {
                var button = event.target.closest('.ft-sh-menu-toggle');
                var link = event.target.closest('.ft-sh-mobile-menu a');

                if (button) {
                    var header = button.closest('.ft-sh-header');
                    var menu = header ? header.querySelector('.ft-sh-mobile-menu') : null;
                    if (!menu) return;
                    var opening = menu.hasAttribute('hidden');
                    menu.toggleAttribute('hidden', !opening);
                    button.setAttribute('aria-expanded', opening ? 'true' : 'false');
                }

                if (link) {
                    var mobileMenu = link.closest('.ft-sh-mobile-menu');
                    var mobileHeader = link.closest('.ft-sh-header');
                    var toggle = mobileHeader ? mobileHeader.querySelector('.ft-sh-menu-toggle') : null;
                    mobileMenu.setAttribute('hidden', '');
                    if (toggle) toggle.setAttribute('aria-expanded', 'false');
                }
            });
        </script>
        <?php
    }
    ?>
    <header
        class="ft-sh-header"
        style="<?php echo esc_attr('--ft-sh-primary:' . $primary_color . ';--ft-sh-foreground:' . $foreground_color . ';--ft-sh-logo-size:' . $logo_size . ';'); ?>"
    >
        <div class="ft-sh-topbar">
            <div class="ft-sh-inner ft-sh-topbar-inner">
                <div class="ft-sh-location">
                    <svg class="ft-sh-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 10c0 5-5.5 10.2-7.4 11.8a1 1 0 0 1-1.2 0C9.5 20.2 4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span><?php echo esc_html($service_area); ?></span>
                </div>
                <nav class="ft-sh-utility" aria-label="<?php esc_attr_e('Utility navigation', 'floors-today'); ?>">
                    <?php foreach ($utility_links as $item) : ?>
                        <a href="<?php echo esc_url($item['href']); ?>"><?php echo esc_html($item['name']); ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
        <div class="ft-sh-inner ft-sh-main">
            <a class="ft-sh-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr($logo_text); ?>">
                <?php if ($logo_image !== '') : ?>
                    <img src="<?php echo esc_url($logo_image); ?>" alt="<?php echo esc_attr($logo_text); ?>" loading="eager" decoding="async">
                <?php else : ?>
                    <span><?php echo esc_html($logo_text); ?></span>
                <?php endif; ?>
            </a>
            <nav class="ft-sh-nav" aria-label="<?php esc_attr_e('Main navigation', 'floors-today'); ?>">
                <?php foreach ($nav_items as $item) :
                    $name = trim((string) ($item['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    ?>
                    <a href="<?php echo esc_url(ft_next_header_shortcode_url($item['href'] ?? '#')); ?>"><?php echo esc_html($name); ?></a>
                <?php endforeach; ?>
            </nav>
            <div class="ft-sh-actions">
                <?php if ($phone !== '' && $phone_href !== '') : ?>
                    <a class="ft-sh-phone" href="<?php echo esc_url('tel:' . $phone_href); ?>">
                        <svg class="ft-sh-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M13.8 16.6a1 1 0 0 0 1.2-.3l.4-.5A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.5.4a1 1 0 0 0-.3 1.2 14 14 0 0 0 6.4 6.4Z"/></svg>
                        <?php echo esc_html($phone); ?>
                    </a>
                <?php endif; ?>
                <button class="ft-sh-menu-toggle" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr($instance_id); ?>">
                    <span class="screen-reader-text"><?php esc_html_e('Open menu', 'floors-today'); ?></span>
                    <svg class="ft-sh-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 5h16M4 12h16M4 19h16"/></svg>
                </button>
            </div>
        </div>
        <nav id="<?php echo esc_attr($instance_id); ?>" class="ft-sh-mobile-menu" aria-label="<?php esc_attr_e('Mobile navigation', 'floors-today'); ?>" hidden>
            <?php foreach ($nav_items as $item) :
                $name = trim((string) ($item['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                ?>
                <a href="<?php echo esc_url(ft_next_header_shortcode_url($item['href'] ?? '#')); ?>"><?php echo esc_html($name); ?></a>
            <?php endforeach; ?>
            <?php foreach ($utility_links as $item) : ?>
                <a href="<?php echo esc_url($item['href']); ?>"><?php echo esc_html($item['name']); ?></a>
            <?php endforeach; ?>
            <?php if ($phone !== '' && $phone_href !== '') : ?>
                <a class="ft-sh-mobile-phone" href="<?php echo esc_url('tel:' . $phone_href); ?>"><?php echo esc_html($phone); ?></a>
            <?php endif; ?>
        </nav>
    </header>
    <?php

    return ob_get_clean();
}

add_shortcode('floors_header', 'ft_next_header_shortcode');

function ft_next_footer_shortcode_links($value) {
    $links = [];

    if (is_array($value)) {
        foreach ($value as $item) {
            if (!is_array($item) || empty($item['label'])) {
                continue;
            }
            $links[] = [
                'label' => (string) $item['label'],
                'url'   => ft_next_header_shortcode_url((string) ($item['url'] ?? '#')),
            ];
        }
        return $links;
    }

    $lines = preg_split('/\r\n|\r|\n/', (string) $value);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '') {
            continue;
        }

        $parts = array_map('trim', explode('|', $line, 2));
        $label = $parts[0] ?? '';
        $url   = $parts[1] ?? '#';

        if ($label === '') {
            continue;
        }

        $links[] = [
            'label' => $label,
            'url'   => ft_next_header_shortcode_url($url),
        ];
    }

    return $links;
}

function ft_next_footer_social_icon($label) {
    $label = strtolower((string) $label);

    $icons = [
        'facebook' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M13.5 22v-9h3l.5-3.5h-3.5V7.3c0-1 .3-1.8 1.8-1.8H17V2.4c-.3 0-1.4-.1-2.7-.1-2.7 0-4.6 1.7-4.6 4.8v2.4H7V13h2.7v9h3.8Z"/></svg>',
        'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7.2 2h9.6A5.2 5.2 0 0 1 22 7.2v9.6a5.2 5.2 0 0 1-5.2 5.2H7.2A5.2 5.2 0 0 1 2 16.8V7.2A5.2 5.2 0 0 1 7.2 2Zm-.2 2A3 3 0 0 0 4 7v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm10.3 1.5a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z"/></svg>',
        'linkedin' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M5.3 7.8A2.3 2.3 0 1 1 5.3 3a2.3 2.3 0 0 1 0 4.7ZM3.3 9.5h4V21h-4V9.5Zm6.5 0h3.8v1.6h.1c.5-1 1.8-2.1 3.8-2.1 4 0 4.8 2.7 4.8 6.1V21h-4v-5.2c0-1.3 0-3-1.9-3s-2.1 1.4-2.1 2.9V21h-4V9.5Z"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M23 7.1a3 3 0 0 0-2.1-2.2C19 4.4 12 4.4 12 4.4s-7 0-8.9.5A3 3 0 0 0 1 7.1 31 31 0 0 0 .5 12a31 31 0 0 0 .5 4.9 3 3 0 0 0 2.1 2.2c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 0 0 2.1-2.2 31 31 0 0 0 .5-4.9 31 31 0 0 0-.5-4.9ZM9.7 15.3V8.7L15.5 12l-5.8 3.3Z"/></svg>',
        'tiktok' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16.7 2c.3 2.2 1.6 3.6 3.8 3.8v3.7a9.2 9.2 0 0 1-3.8-1.1v7.1a6.6 6.6 0 1 1-5.7-6.6v3.8a2.9 2.9 0 1 0 2 2.8V2h3.7Z"/></svg>',
    ];

    return $icons[$label] ?? '<span aria-hidden="true">' . esc_html(strtoupper(substr($label, 0, 1))) . '</span>';
}

function ft_next_footer_shortcode() {
    static $assets_printed = false;

    $settings = ft_next_homepage_settings();

    if (($settings['show_footer'] ?? '1') !== '1') {
        return '';
    }

    $logo_text = (string) ($settings['logo_text'] ?? get_bloginfo('name'));
    $logo_image = (string) ($settings['logo_image'] ?? '');
    $footer_about = (string) ($settings['footer_about'] ?? '');
    $phone = (string) ($settings['phone'] ?? '');
    $phone_href = preg_replace('/[^0-9+]/', '', $phone);
    $email = sanitize_email($settings['email'] ?? '');
    $primary_color = (string) ($settings['primary_color'] ?? '#155f99');
    $bg_color_1 = (string) ($settings['footer_bg_color_1'] ?? 'oklch(0.20 0.02 30)');
    $bg_color_2 = (string) ($settings['footer_bg_color_2'] ?? 'oklch(0.20 0.02 30)');
    $bg_location = (string) ($settings['footer_bg_location'] ?? 'to bottom');
    $copyright = str_replace('{year}', (string) date_i18n('Y'), (string) ($settings['footer_copyright'] ?? ''));
    $about_links = ft_next_footer_shortcode_links($settings['footer_about_links'] ?? '');
    $help_links = ft_next_footer_shortcode_links($settings['footer_help_links'] ?? '');
    $policy_links = ft_next_footer_shortcode_links($settings['footer_policy_links'] ?? '');
    $categories = is_array($settings['categories'] ?? null) ? $settings['categories'] : [];
    $social_links = [
        'Facebook' => (string) ($settings['facebook_url'] ?? ''),
        'Instagram' => (string) ($settings['instagram_url'] ?? ''),
        'LinkedIn' => (string) ($settings['linkedin_url'] ?? ''),
        'YouTube' => (string) ($settings['youtube_url'] ?? ''),
        'TikTok' => (string) ($settings['tiktok_url'] ?? ''),
    ];
    ob_start();

    if (!$assets_printed) {
        $assets_printed = true;
        ?>
        <style id="ft-footer-shortcode-styles">
            .ft-sh-footer,
            .ft-sh-footer * { box-sizing: border-box; }
            .ft-sh-footer {
                width: 100%;
                background: linear-gradient(var(--ft-sh-footer-bg-location), var(--ft-sh-footer-bg-1), var(--ft-sh-footer-bg-2));
                color: #fff;
                font-family: inherit;
            }
            .ft-sh-footer a {
                color: inherit;
                text-decoration: none;
            }
            .ft-sh-footer a:hover,
            .ft-sh-footer a:focus-visible {
                color: #fff;
                text-decoration: underline;
            }
            .ft-sh-footer-inner {
                width: min(100% - 32px, 1340px);
                margin-inline: auto;
            }
            .ft-sh-footer-brand {
                max-width: 520px;
            }
            .ft-sh-footer-logo {
                display: inline-flex;
                align-items: center;
                color: #fff;
                font-size: 26px;
                font-weight: 800;
            }
            .ft-sh-footer-logo img {
                display: block;
                width: 300px;
                max-width: 100%;
                max-height: 92px;
                height: auto;
                object-fit: contain;
            }
            .ft-sh-footer-about {
                margin: 20px 0 0;
                color: rgba(255, 255, 255, .72);
                font-size: 14px;
                line-height: 1.65;
            }
            .ft-sh-footer-grid {
                display: grid;
                grid-template-columns: minmax(180px, 1.1fr) repeat(4, minmax(150px, 1fr));
                gap: 30px;
                padding: 48px 0 38px;
            }
            .ft-sh-footer-col h4 {
                margin: 0 0 16px;
                color: #fff;
                font-size: 16px;
                font-weight: 800;
            }
            .ft-sh-footer-list {
                display: grid;
                gap: 11px;
                margin: 0;
                padding: 0;
                list-style: none;
            }
            .ft-sh-footer-list a,
            .ft-sh-footer-contact {
                color: rgba(255, 255, 255, .72);
                font-size: 14px;
                line-height: 1.45;
            }
            .ft-sh-footer-contact {
                display: grid;
                gap: 12px;
            }
            .ft-sh-footer-contact a,
            .ft-sh-footer-contact span {
                color: rgba(255, 255, 255, .72);
                overflow-wrap: anywhere;
            }
            .ft-sh-footer-social {
                display: flex;
                flex-wrap: wrap;
                gap: 9px;
                margin-top: 24px;
            }
            .ft-sh-footer-social a {
                display: inline-flex !important;
                width: 36px !important;
                height: 36px !important;
                min-width: 36px !important;
                min-height: 36px !important;
                flex: 0 0 36px !important;
                align-items: center;
                justify-content: center;
                border: 1px solid rgba(255, 255, 255, .25);
                border-radius: 50% !important;
                background: rgba(255, 255, 255, .10);
                color: #fff;
                font-weight: 800;
                line-height: 1;
                text-decoration: none;
                padding: 0 !important;
                box-sizing: border-box !important;
            }
            .ft-sh-footer-social a:hover,
            .ft-sh-footer-social a:focus-visible {
                border-color: var(--ft-sh-footer-secondary);
                background: var(--ft-sh-footer-secondary);
                color: #fff;
            }
            .ft-sh-footer-social svg {
                display: block;
                width: 14px !important;
                height: 14px !important;
                flex: 0 0 14px;
            }
            .ft-sh-footer-bottom {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 24px;
                padding: 24px 0;
                border-top: 1px solid rgba(255, 255, 255, .12);
                color: rgba(255, 255, 255, .66);
                font-size: 14px;
            }
            .ft-sh-footer-bottom p { margin: 0; }
            .ft-sh-footer-bottom-contact {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 24px;
            }
            .ft-sh-footer-bottom-contact a,
            .ft-sh-footer-bottom-contact span {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                color: rgba(255, 255, 255, .66);
            }
            .ft-sh-footer-bottom-icon {
                width: 16px;
                height: 16px;
                flex: 0 0 auto;
            }
            @media (max-width: 1024px) {
                .ft-sh-footer-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }
            @media (max-width: 640px) {
                .ft-sh-footer-inner { width: min(100% - 24px, 1340px); }
                /* show mobile elements, hide desktop ones */
                .ft-sh-footer-mobile { display: block; }
                .ft-sh-footer-desktop { display: none; }
                /* stack the internal footer into one centered column */
                .ft-sh-footer-grid {
                    grid-template-columns: 1fr;
                    justify-items: center;
                    gap: 0;
                    padding: 42px 0 34px;
                }
                /* hide link columns, keep brand only */
                .ft-sh-footer-col { display: none; }
                /* brand section: full width, flex column, all centered */
                .ft-sh-footer-brand {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    text-align: center;
                    width: 100%;
                    max-width: 340px;
                    margin-inline: auto;
                }
                /* logo: block so it spans full width and centers the img */
                .ft-sh-footer-logo {
                    display: flex;
                    justify-content: center;
                    width: 100%;
                }
                .ft-sh-footer-logo img { max-width: 240px; margin-inline: auto; }
                .ft-sh-footer-about { text-align: center; max-width: 330px; margin-inline: auto; }
                /* social icons row centered */
                .ft-sh-footer-social {
                    justify-content: center;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 12px;
                    margin-top: 22px;
                    width: 100%;
                    max-width: 250px;
                    margin-inline: auto;
                }
                .ft-sh-footer-social a { width: 40px !important; height: 40px !important; min-width: 40px !important; min-height: 40px !important; max-width: 40px !important; max-height: 40px !important; aspect-ratio: 1 / 1 !important; flex: 0 0 40px !important; border-radius: 999px !important; padding: 0 !important; }
                .ft-sh-footer-social svg { width: 15px !important; height: 15px !important; }
                /* bottom bar centered */
                .ft-sh-footer-bottom { flex-direction: column; align-items: center; }
                .ft-sh-footer-bottom-contact { flex-direction: column; align-items: center; gap: 12px; }
                .ft-sh-footer-bottom p { text-align: center; }
            }
        </style>
        <script id="ft-footer-shortcode-script">
            if (!window.ftNonce) window.ftNonce = <?php echo wp_json_encode(wp_create_nonce('wp_rest')); ?>;
            document.addEventListener('submit', function (event) {
                var form = event.target.closest('.ft-sh-footer-cta-form');
                if (!form) return;
                event.preventDefault();
                if (!form.reportValidity()) return;
                var claimLabel = <?php echo wp_json_encode($settings['newsletter_button'] ?? 'Claim $300'); ?>;
                var btn = form.querySelector('.ft-sh-footer-cta-claim');
                if (btn) { btn.disabled = true; btn.textContent = 'Sending…'; }
                var data = {};
                new FormData(form).forEach(function (v, k) { data[k] = v; });
                var phoneLocal = (data.phoneLocal || '').trim();
                data.phone = phoneLocal ? '+1 ' + phoneLocal : '';
                delete data.phoneLocal;
                data.source = 'Newsletter CTA';
                data.pageUrl = window.location.href;
                fetch(form.dataset.endpoint || '', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.ftNonce },
                    body: JSON.stringify(data)
                }).then(function (response) {
                    return response.json().catch(function () { return {}; }).then(function (body) {
                        return { ok: response.ok, body: body };
                    });
                }).then(function (result) {
                    var success = form.querySelector('.ft-sh-footer-cta-success');
                    if (result.ok) {
                        form.reset();
                        if (success) success.hidden = false;
                    } else {
                        window.alert((result.body && result.body.message) || 'Something went wrong. Please try again.');
                    }
                }).catch(function () {
                    window.alert('Something went wrong. Please try again.');
                }).finally(function () {
                    if (btn) { btn.disabled = false; btn.textContent = claimLabel; }
                });
            });
        </script>
        <?php
    }
    ?>
    <footer
        class="ft-sh-footer"
        style="<?php echo esc_attr('--ft-sh-footer-primary:' . $primary_color . ';--ft-sh-footer-secondary:' . ($settings['secondary_color'] ?? '#cc9c2e') . ';--ft-sh-footer-bg-1:' . $bg_color_1 . ';--ft-sh-footer-bg-2:' . $bg_color_2 . ';--ft-sh-footer-bg-location:' . $bg_location . ';'); ?>"
    >
        <div class="ft-sh-footer-inner">
            <div class="ft-sh-footer-grid">
                <div class="ft-sh-footer-brand">
                    <a class="ft-sh-footer-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr($logo_text); ?>">
                        <?php if ($logo_image !== '') : ?>
                            <img src="<?php echo esc_url($logo_image); ?>" alt="<?php echo esc_attr($logo_text); ?>" loading="lazy" decoding="async">
                        <?php else : ?>
                            <span><?php echo esc_html($logo_text); ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if ($footer_about !== '') : ?>
                        <p class="ft-sh-footer-about"><?php echo esc_html($footer_about); ?></p>
                    <?php endif; ?>
                    <div class="ft-sh-footer-social" aria-label="<?php esc_attr_e('Social links', 'floors-today'); ?>">
                        <?php foreach ($social_links as $label => $url) :
                            $url = trim($url);
                            if ($url === '') {
                                continue;
                            }
                            ?>
                            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr($label); ?>"><?php echo ft_next_footer_social_icon($label); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="ft-sh-footer-col">
                    <h4><?php echo esc_html($settings['footer_about_title'] ?? 'About Us'); ?></h4>
                    <ul class="ft-sh-footer-list">
                        <?php foreach ($about_links as $link) : ?>
                            <li><a href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['label']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="ft-sh-footer-col">
                    <h4><?php echo esc_html($settings['footer_categories_title'] ?? 'Categories'); ?></h4>
                    <ul class="ft-sh-footer-list">
                        <?php foreach ($categories as $category) :
                            $name = trim((string) ($category['name'] ?? ''));
                            $slug = trim((string) ($category['slug'] ?? ''));
                            if ($name === '') {
                                continue;
                            }
                            $url = $slug !== '' ? ft_next_homepage_category_url($slug) : '';
                            $url = $url !== '' ? $url : '#';
                            ?>
                            <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($name); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="ft-sh-footer-col">
                    <h4><?php echo esc_html($settings['footer_help_title'] ?? 'Help Area'); ?></h4>
                    <ul class="ft-sh-footer-list">
                        <?php foreach ($help_links as $link) : ?>
                            <li><a href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['label']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="ft-sh-footer-col">
                    <h4><?php echo esc_html($settings['footer_policies_title'] ?? 'Our Policies'); ?></h4>
                    <ul class="ft-sh-footer-list">
                        <?php foreach ($policy_links as $link) : ?>
                            <li><a href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['label']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="ft-sh-footer-bottom">
                <div class="ft-sh-footer-bottom-contact">
                    <?php if ($phone !== '' && $phone_href !== '') : ?>
                        <a href="<?php echo esc_url('tel:' . $phone_href); ?>"><svg class="ft-sh-footer-bottom-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M13.8 16.6a1 1 0 0 0 1.2-.3l.4-.5A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.5.4a1 1 0 0 0-.3 1.2 14 14 0 0 0 6.4 6.4Z"/></svg><?php echo esc_html($phone); ?></a>
                    <?php endif; ?>
                    <?php if ($email !== '') : ?>
                        <a href="<?php echo esc_url('mailto:' . $email); ?>"><svg class="ft-sh-footer-bottom-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m22 7-9 5.7a2 2 0 0 1-2 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg><?php echo esc_html($email); ?></a>
                    <?php endif; ?>
                </div>
                <p><?php echo esc_html($copyright); ?></p>
            </div>
        </div>
    </footer>
    <?php

    return ob_get_clean();
}

add_shortcode('floors_footer', 'ft_next_footer_shortcode');

// ── [floors_newsletter_cta] ──────────────────────────────────────────────────

function ft_next_newsletter_cta_shortcode( $atts = [] ) {
    static $printed = false;
    static $instance_count = 0;
    $instance_count++;
    $instance_id = $instance_count;

    $atts = shortcode_atts([
        'title_color'    => '',
        'subtitle_color' => '',
    ], $atts, 'floors_newsletter_cta');

    $settings      = ft_next_homepage_settings();
    $primary_color = sanitize_hex_color($settings['primary_color']  ?? '') ?: '#155f99';
    $secondary_color = sanitize_hex_color($settings['secondary_color'] ?? '') ?: '#cc9c2e';

    // title_color / subtitle_color can be hex (#000) or keywords (dark → #1e293b, light → #fff)
    $color_map = [ 'dark' => '#1e293b', 'black' => '#000', 'light' => '#fff', 'white' => '#fff' ];
    $raw_title = strtolower(trim($atts['title_color']));
    $raw_sub   = strtolower(trim($atts['subtitle_color']));
    $title_color    = sanitize_hex_color($atts['title_color'])
                   ?: (isset($color_map[$raw_title]) ? $color_map[$raw_title] : '');
    $subtitle_color = sanitize_hex_color($atts['subtitle_color'])
                   ?: (isset($color_map[$raw_sub]) ? $color_map[$raw_sub] : '');

    $endpoint      = rest_url('floors-integrations/v1/newsletter');
    $badge_height  = (string) ($settings['footer_badge_height'] ?? '60px');
    $badge_images  = array_values(array_filter(array_map('trim', [
        (string) ($settings['footer_badge_image_1'] ?? ''),
        (string) ($settings['footer_badge_image_2'] ?? ''),
        (string) ($settings['footer_badge_image_3'] ?? ''),
        (string) ($settings['footer_badge_image_4'] ?? ''),
        (string) ($settings['footer_badge_image_5'] ?? ''),
        (string) ($settings['footer_badge_image_6'] ?? ''),
    ])));

    ob_start();

    if (!$printed) {
        $printed = true;
        ?>
        <style id="ft-newsletter-cta-styles">
            .ft-ncta, .ft-ncta * { box-sizing: border-box; }
            .ft-ncta {
                width: 100%;
                padding: 52px 0;
                text-align: center;
                font-family: inherit;
                --ft-ncta-primary: <?php echo esc_attr($primary_color); ?>;
                --ft-ncta-secondary: <?php echo esc_attr($secondary_color); ?>;
            }
            .ft-ncta-inner {
                width: min(100% - 32px, 1200px);
                margin-inline: auto;
            }
            .ft-ncta-badges {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                align-items: flex-start;
                gap: 14px;
                margin-bottom: 30px;
            }
            .ft-ncta-badge-warranty {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 5px;
            }
            .ft-ncta-badge-warranty .ft-ncta-warranty-btn {
                all: unset;
                display: block !important;
                background: transparent !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                font-size: 13px !important;
                font-weight: 500 !important;
                color: <?php echo is_front_page() ? '#000' : '#CC9C2E'; ?> !important;
                cursor: pointer;
                text-decoration: underline !important;
                text-underline-offset: 2px;
                text-align: center;
                white-space: nowrap;
                line-height: 1.2 !important;
            }
            .ft-ncta-badge-warranty .ft-ncta-warranty-btn:hover { opacity: .7 !important; }
            .ft-ncta-badges img {
                display: block;
                height: var(--ft-ncta-badge-h, 60px) !important;
                width: auto !important;
                object-fit: contain;
            }
            .ft-ncta-title {
                margin: 0;
                color: #fff !important;
                font-size: clamp(1.5rem, 3vw, 2rem);
                font-weight: 700;
                line-height: 1.2;
            }
            .ft-ncta-subtitle {
                margin: 10px auto 28px;
                color: rgba(255, 255, 255, .82) !important;
                font-size: 15px;
                max-width: 580px;
                line-height: 1.55;
            }
            .ft-ncta-card {
                display: grid;
                grid-template-columns: 1.65fr 1fr;
                gap: 28px;
                background: #fff;
                border-radius: 16px;
                padding: 28px;
                text-align: left;
                max-width: 920px;
                margin: 0 auto;
                box-shadow: 0 8px 32px rgba(0, 0, 0, .18);
            }
            .ft-ncta-consents {
                display: flex;
                flex-direction: column;
                gap: 14px;
            }
            .ft-ncta-consent {
                display: flex !important;
                align-items: flex-start !important;
                gap: 10px !important;
                cursor: pointer;
            }
            .ft-ncta-consent,
            .ft-ncta-consent span,
            .ft-ncta-consent span * {
                color: #334155 !important;
                font-size: 16px !important;
                line-height: 1.55 !important;
                font-weight: 400 !important;
            }
            .ft-ncta-consent input[type="checkbox"] {
                width: 20px !important;
                height: 20px !important;
                min-width: 20px !important;
                flex-shrink: 0 !important;
                margin: 2px 0 0 !important;
                padding: 0 !important;
                border: 2px solid #cbd5e1 !important;
                border-radius: 5px !important;
                background: #fff !important;
                box-shadow: none !important;
                appearance: none !important;
                -webkit-appearance: none !important;
                cursor: pointer;
                transition: background .15s ease, border-color .15s ease;
            }
            .ft-ncta-consent input[type="checkbox"]:checked {
                background-color: var(--ft-ncta-primary) !important;
                border-color: var(--ft-ncta-primary) !important;
                background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 16 16' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M3 8.5l3 3 7-7' stroke='%23fff' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") !important;
                background-size: 13px 13px !important;
                background-position: center !important;
                background-repeat: no-repeat !important;
            }
            .ft-ncta-consent input[type="checkbox"]:focus-visible {
                outline: 2px solid var(--ft-ncta-primary) !important;
                outline-offset: 2px !important;
            }
            .ft-ncta-consent a {
                color: var(--ft-ncta-primary) !important;
                font-weight: 600 !important;
                text-decoration: underline !important;
            }
            .ft-ncta-fields {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }
            .ft-ncta-input-wrap {
                display: flex;
                align-items: center;
                gap: 10px;
                border: 1px solid #d1d5db;
                border-radius: 8px;
                background: #fff;
                padding: 0 14px;
                height: 48px;
                transition: border-color .18s ease;
            }
            .ft-ncta-input-wrap:focus-within { border-color: var(--ft-ncta-primary); }
            .ft-ncta-input-wrap svg {
                width: 18px;
                height: 18px;
                flex-shrink: 0;
                color: #94a3b8;
            }
            .ft-ncta-pfx {
                color: #64748b !important;
                font-size: 14px;
                flex-shrink: 0;
                font-weight: 400 !important;
            }
            .ft-ncta-input-wrap input {
                flex: 1 !important;
                border: none !important;
                outline: none !important;
                background: transparent !important;
                color: #0f172a !important;
                font-family: inherit;
                font-size: 14px !important;
                height: 100% !important;
                padding: 0 !important;
                box-shadow: none !important;
                margin: 0 !important;
                min-width: 0 !important;
                width: 100% !important;
            }
            .ft-ncta-input-wrap input::placeholder { color: #94a3b8 !important; }
            .ft-ncta-buttons {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
                margin-top: 4px;
            }
            .ft-ncta-claim,
            .ft-ncta-details {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                height: 56px !important;
                border: none !important;
                border-radius: 8px !important;
                color: #fff !important;
                font-family: inherit;
                font-size: 17px !important;
                font-weight: 700 !important;
                cursor: pointer;
                box-shadow: none !important;
                letter-spacing: 0 !important;
                text-transform: none !important;
                text-decoration: none !important;
                transition: opacity .18s ease;
                padding: 0 12px !important;
            }
            .ft-ncta-claim  { background: var(--ft-ncta-secondary) !important; }
            .ft-ncta-details { background: var(--ft-ncta-primary) !important; }
            .ft-ncta-claim:hover,
            .ft-ncta-details:hover { opacity: .88; color: #fff !important; text-decoration: none !important; }
            .ft-ncta-success-card {
                background: #fff;
                border-radius: 16px;
                padding: 52px 28px;
                max-width: 520px;
                margin: 0 auto;
                text-align: center;
                box-shadow: 0 8px 32px rgba(0,0,0,.18);
            }
            .ft-ncta-success-icon {
                width: 68px; height: 68px;
                background: #dcfce7;
                border-radius: 50%;
                display: flex; align-items: center; justify-content: center;
                margin: 0 auto 20px;
            }
            .ft-ncta-success-icon svg { width: 34px; height: 34px; color: #16a34a; stroke: #16a34a; }
            .ft-ncta-success-title { margin: 0 0 12px; font-size: 1.45rem; font-weight: 700; color: #1e293b !important; }
            .ft-ncta-success-msg { margin: 0; color: #64748b !important; font-size: 15px; line-height: 1.65; }
            .ft-ncta-popup {
                display: none;
                position: fixed;
                inset: 0;
                z-index: 999999;
                align-items: center;
                justify-content: center;
                padding: 16px;
                background: rgba(2, 6, 23, .7);
                backdrop-filter: blur(2px);
                overflow-y: auto;
            }
            .ft-ncta-popup.is-open { display: flex; }
            .ft-ncta-popup-card {
                position: relative;
                width: 100%;
                max-width: 520px;
                margin: auto;
                background: #fff;
                border-radius: 14px;
                padding: 28px 24px;
                text-align: center;
                box-shadow: 0 25px 60px rgba(0, 0, 0, .35);
            }
            .ft-ncta-popup-close {
                position: absolute !important;
                top: 12px !important;
                right: 12px !important;
                width: 36px !important;
                height: 36px !important;
                min-width: 36px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                border-radius: 999px !important;
                border: 1px solid #e2e8f0 !important;
                background: #fff !important;
                color: #334155 !important;
                font-size: 18px !important;
                line-height: 1 !important;
                padding: 0 !important;
                box-shadow: none !important;
                cursor: pointer;
            }
            .ft-ncta-popup-logo {
                display: block !important;
                max-width: 240px !important;
                width: auto !important;
                height: auto !important;
                margin: 0 auto !important;
            }
            .ft-ncta-popup-title {
                margin: 10px 0 0 !important;
                color: #000 !important;
                background: transparent !important;
                font-size: 17px !important;
                font-weight: 700 !important;
                line-height: 1.25 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            .ft-ncta-popup-text {
                margin: 10px 0 0;
                color: #475569 !important;
                font-size: 17px !important;
                line-height: 1.6;
            }
            .ft-ncta-popup-details {
                margin: 22px 0 0;
                padding: 14px 16px;
                border-radius: 10px;
                background: #f8fafc;
                color: #334155 !important;
                font-size: 17px !important;
                line-height: 1.6;
                text-align: left;
            }
            .ft-ncta-popup-details p {
                margin: 0 0 14px !important;
                color: #334155 !important;
                font-size: 17px !important;
                line-height: 1.6 !important;
            }
            .ft-ncta-popup-details p:last-child { margin-bottom: 0 !important; }
            .ft-ncta-warranty-more {
                display: inline-block;
                margin-top: 18px;
                padding: 9px 22px;
                background: var(--ft-ncta-primary, #1d4ed8);
                color: #fff !important;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 600;
                text-decoration: none !important;
            }
            .ft-ncta-warranty-more:hover { opacity: .85; }
            @media (max-width: 640px) {
                .ft-ncta-inner { width: min(100% - 24px, 1200px); }
                .ft-ncta-card { grid-template-columns: 1fr; }
                .ft-ncta-badges {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    align-items: end;
                    justify-items: center;
                    --ft-ncta-mobile-badge-size: clamp(152px, 45vw, 178px);
                    --ft-ncta-mobile-warranty-size: clamp(166px, 48vw, 190px);
                    gap: 12px 6px;
                    margin-bottom: 26px;
                }
                .ft-ncta-badge-warranty {
                    order: 1;
                    grid-column: 1 / -1;
                    gap: 0;
                    margin-top: -22px;
                    margin-bottom: -6px;
                }
                .ft-ncta-badge-warranty img {
                    width: var(--ft-ncta-mobile-warranty-size) !important;
                    height: var(--ft-ncta-mobile-warranty-size) !important;
                    max-width: 100% !important;
                    object-fit: contain;
                }
                .ft-ncta-badge-warranty .ft-ncta-warranty-btn {
                    color: #111 !important;
                    font-size: 20px !important;
                    line-height: 1.25 !important;
                    text-decoration-thickness: 1px !important;
                    text-underline-offset: 3px !important;
                    margin-top: -10px !important;
                    white-space: normal;
                }
                .ft-ncta-badges > img {
                    order: 2;
                    width: var(--ft-ncta-mobile-badge-size) !important;
                    height: var(--ft-ncta-mobile-badge-size) !important;
                    max-width: 100% !important;
                    object-fit: contain;
                }
                .ft-ncta-buttons { grid-template-columns: 1fr; }
            }
        </style>
        <script id="ft-newsletter-cta-script">
            window.ftNonce = <?php echo wp_json_encode(wp_create_nonce('wp_rest')); ?>;
            document.addEventListener('submit', function (event) {
                var form = event.target.closest('.ft-ncta-form');
                if (!form) return;
                event.preventDefault();
                if (!form.reportValidity()) return;
                var claimLabel = <?php echo wp_json_encode($settings['newsletter_claim_button'] ?? 'Claim $300'); ?>;
                var btn = form.querySelector('.ft-ncta-claim');
                if (btn) { btn.disabled = true; btn.textContent = 'Sending…'; }
                var data = {};
                new FormData(form).forEach(function (v, k) { data[k] = v; });
                var phoneLocal = (data.phoneLocal || '').trim();
                data.phone = phoneLocal ? '+1 ' + phoneLocal : '';
                delete data.phoneLocal;
                data.source = 'Newsletter CTA';
                data.pageUrl = window.location.href;
                data.referrerUrl = document.referrer || '';
                fetch(form.dataset.endpoint || '', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': (window.ftNonce || '') },
                    body: JSON.stringify(data)
                }).then(function (response) {
                    return response.json().catch(function () { return {}; }).then(function (body) {
                        return { ok: response.ok, body: body };
                    });
                }).then(function (result) {
                    if (result.ok) {
                        var name = (data.fullName || '').trim().split(' ')[0];
                        var inner = form.closest('.ft-ncta-inner');
                        var successCard = inner ? inner.querySelector('.ft-ncta-success-card') : null;
                        form.reset();
                        form.hidden = true;
                        if (successCard) {
                            if (name) {
                                var msgEl = successCard.querySelector('.ft-ncta-success-msg');
                                if (msgEl) msgEl.textContent = 'Thank you, ' + name + '! Your $300 store credit details will be sent to your email shortly.';
                            }
                            successCard.hidden = false;
                        }
                    } else {
                        window.alert((result.body && result.body.message) || 'Something went wrong. Please try again.');
                    }
                }).catch(function () {
                    window.alert('Something went wrong. Please try again.');
                }).finally(function () {
                    if (btn) { btn.disabled = false; btn.textContent = claimLabel; }
                });
            });
            document.addEventListener('click', function (event) {
                var opener = event.target.closest('.ft-ncta-details, .ft-ncta-warranty-btn');
                if (opener) {
                    event.preventDefault();
                    var popup = document.getElementById(opener.getAttribute('aria-controls'));
                    if (popup) popup.classList.add('is-open');
                    return;
                }
                var closer = event.target.closest('[data-ncta-popup-close]');
                if (closer) {
                    var popupToClose = closer.closest('.ft-ncta-popup');
                    if (popupToClose) popupToClose.classList.remove('is-open');
                    return;
                }
                if (event.target.classList && event.target.classList.contains('ft-ncta-popup')) {
                    event.target.classList.remove('is-open');
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') return;
                document.querySelectorAll('.ft-ncta-popup.is-open').forEach(function (popup) { popup.classList.remove('is-open'); });
            });
        </script>
        <?php
    }
    ?>
    <section class="ft-ncta" style="--ft-ncta-badge-h:<?php echo esc_attr($badge_height); ?>">
        <div class="ft-ncta-inner">
            <?php if (!empty($badge_images)) : ?>
            <div class="ft-ncta-badges">
                <?php foreach ($badge_images as $badge_index => $img) : ?>
                    <?php if ($badge_index === 2) : ?>
                    <div class="ft-ncta-badge-warranty">
                        <img src="<?php echo esc_url($img); ?>" alt="" loading="lazy" decoding="async">
                        <button type="button" class="ft-ncta-warranty-btn" aria-controls="ft-ncta-warranty-popup-<?php echo esc_attr($instance_id); ?>" aria-haspopup="dialog"><?php esc_html_e('View warranty details', 'floors-today'); ?></button>
                    </div>
                    <?php else : ?>
                    <img src="<?php echo esc_url($img); ?>" alt="" loading="lazy" decoding="async">
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <h2 class="ft-ncta-title"<?php echo $title_color ? ' style="color:' . esc_attr($title_color) . '!important"' : ''; ?>><?php echo esc_html($settings['newsletter_title'] ?? 'Subscribe and Get $300 Store Credit'); ?></h2>
            <p class="ft-ncta-subtitle"<?php echo $subtitle_color ? ' style="color:' . esc_attr($subtitle_color) . '!important"' : ($title_color ? ' style="color:' . esc_attr($title_color) . 'cc!important"' : ''); ?>><?php echo esc_html($settings['newsletter_text'] ?? 'Join our newsletter for exclusive flooring offers, project tips, and your $300 store credit details.'); ?></p>
            <form class="ft-ncta-card ft-ncta-form" data-endpoint="<?php echo esc_url($endpoint); ?>">
                <div class="ft-ncta-consents">
                    <label class="ft-ncta-consent">
                        <input name="privacyConsent" type="checkbox" required>
                        <span>I agree to receive promotional emails from Floors Today and have read the <a href="/privacy-policy/">Privacy Policy</a> and <a href="/terms-of-use/">Terms &amp; Conditions</a>.</span>
                    </label>
                    <label class="ft-ncta-consent">
                        <input name="smsConsent" type="checkbox" required>
                        <span>I agree to receive SMS marketing and informational messages from Floors Today at the contact information provided above. Message frequency may vary. Message &amp; data rates may apply. Reply STOP to unsubscribe or HELP for assistance.</span>
                    </label>
                    <label class="ft-ncta-consent">
                        <input name="emailConsent" type="checkbox" required>
                        <span>I agree to receive email marketing communications from Floors Today at the email address provided above. I understand Floors Today may respond to any messages or emails I send.</span>
                    </label>
                </div>
                <div class="ft-ncta-fields">
                    <div class="ft-ncta-input-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <input name="fullName" type="text" autocomplete="name" placeholder="<?php esc_attr_e('Full Name', 'floors-today'); ?>" required>
                    </div>
                    <div class="ft-ncta-input-wrap">
                        <span class="ft-ncta-pfx">+1</span>
                        <input name="phoneLocal" type="tel" autocomplete="tel" placeholder="<?php esc_attr_e('Phone Number', 'floors-today'); ?>">
                    </div>
                    <div class="ft-ncta-input-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        <input name="email" type="email" autocomplete="email" placeholder="<?php esc_attr_e('Email Address', 'floors-today'); ?>" required>
                    </div>
                    <div class="ft-ncta-buttons">
                        <button class="ft-ncta-claim" type="submit"><?php echo esc_html($settings['newsletter_claim_button'] ?? 'Claim $300'); ?></button>
                        <button class="ft-ncta-details" type="button" aria-controls="ft-ncta-popup-<?php echo esc_attr($instance_id); ?>" aria-haspopup="dialog"><?php echo esc_html($settings['newsletter_button'] ?? 'See Details'); ?></button>
                    </div>
                </div>
            </form>
            <div class="ft-ncta-success-card" hidden>
                <div class="ft-ncta-success-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                </div>
                <h3 class="ft-ncta-success-title"><?php esc_html_e("You're subscribed!", 'floors-today'); ?></h3>
                <p class="ft-ncta-success-msg"><?php esc_html_e('Thank you! Your $300 store credit details will be sent to your email shortly.', 'floors-today'); ?></p>
            </div>
        </div>
    </section>
    <div class="ft-ncta-popup" id="ft-ncta-popup-<?php echo esc_attr($instance_id); ?>" role="dialog" aria-modal="true" aria-labelledby="ft-ncta-popup-title-<?php echo esc_attr($instance_id); ?>">
        <div class="ft-ncta-popup-card">
            <button type="button" class="ft-ncta-popup-close" data-ncta-popup-close aria-label="<?php esc_attr_e('Close', 'floors-today'); ?>">&times;</button>
            <?php if (!empty($settings['logo_image'])) : ?>
                <img class="ft-ncta-popup-logo" src="<?php echo esc_url($settings['logo_image']); ?>" alt="<?php echo esc_attr($settings['logo_text'] ?? ''); ?>" loading="lazy" decoding="async">
            <?php endif; ?>
            <h2 class="ft-ncta-popup-title" id="ft-ncta-popup-title-<?php echo esc_attr($instance_id); ?>"><?php echo esc_html($settings['newsletter_popup_title'] ?? 'Promo Terms & Conditions'); ?></h2>
            <?php
            $allowed_inline = ['b' => [], 'strong' => [], 'br' => [], 'em' => []];
            $allowed_block  = $allowed_inline + ['p' => []];
            ?>
            <?php if (!empty($settings['newsletter_text'])) : ?>
                <p class="ft-ncta-popup-text"><?php echo wp_kses($settings['newsletter_text'], $allowed_inline); ?></p>
            <?php endif; ?>
            <div class="ft-ncta-popup-details"><?php echo wp_kses(wpautop($settings['newsletter_details_text'] ?? 'Complete the short form in the newsletter section to receive the latest flooring deals, project tips, and details about your $300 store credit.'), $allowed_block); ?></div>
        </div>
    </div>
    <div class="ft-ncta-popup" id="ft-ncta-warranty-popup-<?php echo esc_attr($instance_id); ?>" role="dialog" aria-modal="true" aria-labelledby="ft-ncta-warranty-popup-title-<?php echo esc_attr($instance_id); ?>">
        <div class="ft-ncta-popup-card">
            <button type="button" class="ft-ncta-popup-close" data-ncta-popup-close aria-label="<?php esc_attr_e('Close', 'floors-today'); ?>">&times;</button>
            <?php if (!empty($settings['logo_image'])) : ?>
                <img class="ft-ncta-popup-logo" src="<?php echo esc_url($settings['logo_image']); ?>" alt="<?php echo esc_attr($settings['logo_text'] ?? ''); ?>" loading="lazy" decoding="async">
            <?php endif; ?>
            <h2 class="ft-ncta-popup-title" id="ft-ncta-warranty-popup-title-<?php echo esc_attr($instance_id); ?>"><?php echo esc_html($settings['warranty_popup_title'] ?? '3 Year Warranty'); ?></h2>
            <?php
            $warranty_allowed_inline = ['b' => [], 'strong' => [], 'br' => [], 'em' => []];
            $warranty_allowed_block  = $warranty_allowed_inline + ['p' => []];
            ?>
            <div class="ft-ncta-popup-details"><?php echo wp_kses(wpautop($settings['warranty_details_content']), $warranty_allowed_block); ?></div>
            <?php
            $warranty_raw_url = trim($settings['warranty_see_more_url'] ?? '');
            if ($warranty_raw_url !== '') :
                $warranty_href = preg_match('#^https?://#i', $warranty_raw_url)
                    ? $warranty_raw_url
                    : home_url('/' . ltrim($warranty_raw_url, '/'));
            ?>
            <a href="<?php echo esc_url($warranty_href); ?>" class="ft-ncta-warranty-more" target="_blank" rel="noopener noreferrer"><?php echo esc_html($settings['warranty_see_more_text'] ?: 'See More'); ?></a>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('floors_newsletter_cta', 'ft_next_newsletter_cta_shortcode');

function ft_next_booking_form_shortcode() {
    static $styles_printed = false;
    static $places_script_printed = false;

    $settings = ft_next_homepage_settings();
    $google_places_key = sanitize_text_field($settings['google_places_api_key'] ?? '');
    $instance_id = wp_unique_id('ft-booking-form-');
    $endpoint = rest_url('floors-today/v1/inbox-leads');
    $primary_color = sanitize_hex_color($settings['primary_color'] ?? '') ?: '#155f99';
    $secondary_color = sanitize_hex_color($settings['secondary_color'] ?? '') ?: '#cc9c2e';
    $title = (string) ($settings['form_title'] ?? 'Get Your FREE In-Home Estimate');
    $subtitle = (string) ($settings['form_subtitle'] ?? 'No obligation. Takes just 2 minutes.');
    $flooring_types = ['Solid Hardwood', 'Engineered Hardwood', 'Laminate', 'Vinyl', 'Carpet', 'Not sure yet'];
    $property_types = ['Residential', 'Office Space', 'Business'];
    $property_icons = [
        'Residential' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg>',
        'Office Space' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M9 17h.01"/></svg>',
        'Business' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/><path d="M12 13v2"/></svg>',
    ];
    $start_times = ['ASAP', 'Within 1 month', '1-3 months', '3+ months', 'Just researching'];

    ob_start();

    if (!$styles_printed) {
        $styles_printed = true;
        ?>
        <style id="ft-booking-form-shortcode-styles">
            .ft-bf,
            .ft-bf * { box-sizing: border-box; }
            .ft-bf button,
            .ft-bf input,
            .ft-bf select {
                font-family: inherit;
            }
            .ft-bf button {
                appearance: none;
                -webkit-appearance: none;
                text-transform: none;
                letter-spacing: 0;
                box-shadow: none;
            }
            .ft-bf {
                display: block;
                width: 100% !important;
                max-width: 100% !important;
                margin-inline: 0 !important;
                padding: clamp(20px, 5vw, 36px);
                overflow: hidden;
                border: 1px solid rgba(255, 255, 255, .5);
                border-radius: 20px;
                background: #fff;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, .25);
                color: #0f172a;
                font-family: inherit;
                font-size: 14px;
            }
            .elementor-shortcode > .ft-bf,
            .elementor-widget-shortcode .ft-bf,
            .elementor-widget-container > .ft-bf {
                width: 100% !important;
                max-width: 100% !important;
            }
            .ft-bf__heading { margin-bottom: 0; text-align: center; }
            .ft-bf__title { margin: 0; color: #020617; font-family: Georgia, "Times New Roman", serif; font-size: 1.9rem !important; font-weight: 700; line-height: 1.12; }
            .ft-bf__subtitle { margin: 8px 0 0; color: #475569; font-size: 14px; }
            .ft-bf__progress {
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 28px 0;
            }
            .ft-bf__dot {
                display: inline-flex;
                width: 36px;
                height: 36px;
                flex: 0 0 36px;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: #f5f5f4;
                color: #78716c;
                font-size: 14px;
                font-weight: 700;
                transition: background-color .18s ease, color .18s ease;
            }
            .ft-bf__dot.is-active { background: var(--ft-bf-primary); color: #fff; }
            .ft-bf__dot.is-complete { background: #059669; color: #fff; }
            .ft-bf__line { width: clamp(32px, 10vw, 56px); height: 2px; margin: 0 10px; border-radius: 999px; background: #f5f5f4; transition: background-color .18s ease; }
            .ft-bf__line.is-complete { background: #059669; }
            .ft-bf__step[hidden] { display: none !important; }
            .ft-bf__step { animation: ft-bf-fade .22s ease both; }
            @keyframes ft-bf-fade {
                from { opacity: 0; transform: translateY(4px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .ft-bf__question { margin: 0 0 16px; color: #0f172a; font-size: 16px; font-weight: 700; }
            .ft-bf__choices { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .ft-bf__choices--flooring { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
            .ft-bf__choices--property { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
            .ft-bf button.ft-bf__choice {
                width: 100%;
                min-height: 54px;
                padding: 12px 16px;
                border: 1px solid #e7e5e4 !important;
                border-radius: 8px !important;
                background: #fff !important;
                color: #0f172a !important;
                font: inherit;
                font-weight: 600;
                text-align: left;
                white-space: normal;
                word-break: break-word;
                cursor: pointer;
                transition: border-color .18s ease, background-color .18s ease, color .18s ease, transform .12s ease;
            }
            .ft-bf button.ft-bf__choice:hover,
            .ft-bf button.ft-bf__choice:focus-visible { border-color: var(--ft-bf-primary) !important; outline: none; }
            .ft-bf button.ft-bf__choice:active { transform: translateY(1px); }
            .ft-bf button.ft-bf__choice.is-selected {
                border-color: var(--ft-bf-primary) !important;
                background: color-mix(in srgb, var(--ft-bf-primary) 7%, white) !important;
                color: var(--ft-bf-primary) !important;
            }
            .ft-bf__property { min-height: 96px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #475569; }
            .ft-bf__property-icon { display: inline-flex; align-items: center; justify-content: center; color: var(--ft-bf-primary); }
            .ft-bf button.ft-bf__property.is-selected { color: #0f172a !important; background: color-mix(in srgb, var(--ft-bf-secondary) 10%, white) !important; border-color: var(--ft-bf-secondary) !important; }
            .ft-bf__property svg { width: 24px; height: 24px; }
            .ft-bf__field { display: block; margin: 0 0 16px; }
            .ft-bf__field > span { display: block; margin-bottom: 7px; color: #475569; font-size: 14px; font-weight: 600; }
            .ft-bf__input,
            .ft-bf__select,
            .elementor-widget-container .ft-bf__input,
            .elementor-widget-container .ft-bf__select {
                display: block;
                width: 100%;
                height: 48px;
                padding: 0 14px;
                border: 1px solid #d6d3d1 !important;
                border-radius: 8px !important;
                background: #fff !important;
                color: #0f172a;
                font: inherit;
                font-size: 16px;
                box-shadow: none !important;
                transition: border-color .18s ease, box-shadow .18s ease;
            }
            .ft-bf__input:focus,
            .ft-bf__select:focus,
            .elementor-widget-container .ft-bf__input:focus,
            .elementor-widget-container .ft-bf__select:focus { border-color: var(--ft-bf-primary) !important; outline: 0; box-shadow: 0 0 0 3px color-mix(in srgb, var(--ft-bf-primary) 18%, transparent) !important; }
            .ft-bf__columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
            .ft-bf__actions { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 28px; }
            .ft-bf button.ft-bf__next,
            .ft-bf button.ft-bf__submit {
                display: inline-flex;
                min-height: 40px;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 8px 20px;
                border: 0 !important;
                border-radius: 999px !important;
                background: var(--ft-bf-primary) !important;
                color: #fff !important;
                font: inherit;
                font-weight: 700;
                cursor: pointer;
                white-space: nowrap;
                text-decoration: none !important;
                transition: background-color .18s ease, box-shadow .18s ease, transform .12s ease, opacity .18s ease;
            }
            .ft-bf button.ft-bf__next:hover,
            .ft-bf button.ft-bf__submit:hover {
                background: color-mix(in srgb, var(--ft-bf-primary) 90%, white) !important;
                color: #fff !important;
                box-shadow: 0 10px 20px rgba(35, 91, 184, .18) !important;
            }
            .ft-bf button.ft-bf__next:focus-visible,
            .ft-bf button.ft-bf__submit:focus-visible {
                outline: 0;
                box-shadow: 0 0 0 3px color-mix(in srgb, var(--ft-bf-primary) 24%, transparent);
            }
            .ft-bf button.ft-bf__next:active,
            .ft-bf button.ft-bf__submit:active { transform: translateY(1px); }
            .ft-bf button.ft-bf__submit:disabled { cursor: wait; opacity: .65; animation: ft-bf-pulse 1.1s ease-in-out infinite; }
            .ft-bf__arrow {
                display: inline-block;
                line-height: 1;
                transition: transform .18s ease;
            }
            .ft-bf button.ft-bf__next:hover .ft-bf__arrow,
            .ft-bf button.ft-bf__next:focus-visible .ft-bf__arrow,
            .ft-bf button.ft-bf__submit:hover .ft-bf__arrow,
            .ft-bf button.ft-bf__submit:focus-visible .ft-bf__arrow {
                transform: translateX(4px);
            }
            @keyframes ft-bf-pulse {
                0%, 100% { opacity: .65; }
                50% { opacity: .88; }
            }
            .ft-bf button.ft-bf__back {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 2px;
                border: 0 !important;
                border-radius: 0 !important;
                background: transparent !important;
                color: #64748b !important;
                font: inherit;
                font-weight: 600;
                cursor: pointer;
                box-shadow: none !important;
                transition: color .18s ease;
            }
            .ft-bf button.ft-bf__back:hover,
            .ft-bf button.ft-bf__back:focus-visible { color: #020617 !important; background: transparent !important; outline: none; }
            .ft-bf__error { margin: 14px 0 0; color: #dc2626; font-size: 14px; font-weight: 600; }
            .ft-bf__success { min-height: 360px; text-align: center; align-content: center; animation: ft-bf-fade .22s ease both; }
            .ft-bf__success-icon {
                display: inline-flex;
                width: 64px;
                height: 64px;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: #ecfdf5;
                color: #047857;
                font-size: 32px;
                font-weight: 700;
            }
            .ft-bf__success h3 { margin: 18px 0 8px; color: #0f172a; font-size: 24px; }
            .ft-bf__success p { margin: 0; color: #475569; }
            .ft-bf__trap { position: absolute !important; left: -10000px !important; width: 1px !important; height: 1px !important; overflow: hidden !important; }
            .ft-bf__addr-row { display: grid; grid-template-columns: 3fr 1fr; gap: 12px; }
            .ft-bf__select { appearance: none; cursor: pointer; }
            .ft-bf__sel-wrap { display: grid; grid-template-areas: "sel"; align-items: center; }
            .ft-bf__sel-wrap::after { content: ""; grid-area: sel; width: .65em; height: .42em; margin-right: 14px; justify-self: end; background-color: #94a3b8; clip-path: polygon(100% 0%, 0% 0%, 50% 100%); pointer-events: none; transition: background-color .18s ease; }
            .ft-bf__sel-wrap:focus-within::after { background-color: var(--ft-bf-primary); }
            .ft-bf__sel-wrap .ft-bf__select { grid-area: sel; padding-right: 36px; }
            .ft-bf__phone-wrap { display: flex; align-items: stretch; border: 1px solid #d6d3d1; border-radius: 8px; overflow: hidden; background: #fff; transition: border-color .18s ease, box-shadow .18s ease; }
            .ft-bf__phone-wrap:focus-within { border-color: var(--ft-bf-primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--ft-bf-primary) 18%, transparent); }
            .ft-bf__phone-pfx { display: flex; align-items: center; padding: 0 12px; background: #f5f5f4; border-right: 1px solid #d6d3d1; font-size: 15px; color: #64748b; user-select: none; flex-shrink: 0; }
            .ft-bf__phone-wrap .ft-bf__input { border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; }
            .ft-bf__input[readonly] { opacity: .65; cursor: default; background: #f8f7f6; }
            @media (max-width: 560px) {
                .ft-bf,
                .elementor-shortcode > .ft-bf,
                .elementor-widget-shortcode .ft-bf,
                .elementor-widget-container > .ft-bf { margin-left: 10px !important; margin-right: 10px !important; width: calc(100% - 20px) !important; max-width: calc(100% - 20px) !important; }
                .ft-bf__title { font-size: 25px !important; }
                .ft-bf__columns { grid-template-columns: 1fr; }
                .ft-bf__choices--property { grid-template-columns: 1fr !important; gap: 8px; }
                .ft-bf button.ft-bf__choice { padding: 11px 12px; }
                .ft-bf__property { min-height: 84px; text-align: center; }
                .ft-bf__property svg { width: 22px; height: 22px; }
            }
        </style>
        <?php
    }

    if ($google_places_key && !$places_script_printed) {
        $places_script_printed = true;
        ?>
        <script>
            if (!window.__ftPlacesScript) {
                window.__ftPlacesScript = true;
                window.__ftPlacesReady = function () {
                    window.__ftPlacesLoaded = true;
                    document.dispatchEvent(new CustomEvent('ft:places:ready'));
                };
                var s = document.createElement('script');
                s.async = true;
                s.src = 'https://maps.googleapis.com/maps/api/js?key=<?php echo esc_js($google_places_key); ?>&libraries=places&callback=__ftPlacesReady';
                document.head.appendChild(s);
            }
        </script>
        <?php
    }
    ?>
    <div
        id="<?php echo esc_attr($instance_id); ?>"
        class="ft-bf"
        data-endpoint="<?php echo esc_url($endpoint); ?>"
        style="<?php echo esc_attr('--ft-bf-primary:' . $primary_color . ';--ft-bf-secondary:' . $secondary_color . ';'); ?>"
    >
        <div class="ft-bf__heading">
            <h2 class="ft-bf__title"><?php echo esc_html($title); ?></h2>
            <p class="ft-bf__subtitle"><?php echo esc_html($subtitle); ?></p>
        </div>
        <div class="ft-bf__progress" aria-label="<?php esc_attr_e('Booking form progress', 'floors-today'); ?>">
            <span class="ft-bf__dot is-active" data-dot="1">1</span><span class="ft-bf__line" data-line="1"></span>
            <span class="ft-bf__dot" data-dot="2">2</span><span class="ft-bf__line" data-line="2"></span>
            <span class="ft-bf__dot" data-dot="3">3</span><span class="ft-bf__line" data-line="3"></span>
            <span class="ft-bf__dot" data-dot="4">4</span>
        </div>
        <form class="ft-bf__form">
            <div class="ft-bf__trap" aria-hidden="true">
                <label>Leave this field empty<input name="ftInboxTrap" type="text" tabindex="-1" autocomplete="new-password"></label>
            </div>
            <section class="ft-bf__step" data-step="1">
                <h3 class="ft-bf__question">What floors interest you?</h3>
                <div class="ft-bf__choices ft-bf__choices--flooring">
                    <?php foreach ($flooring_types as $flooring_type) : ?>
                        <button class="ft-bf__choice" type="button" data-field="flooringType" data-value="<?php echo esc_attr($flooring_type); ?>"><?php echo esc_html($flooring_type); ?></button>
                    <?php endforeach; ?>
                </div>
                <div class="ft-bf__actions"><span></span><button class="ft-bf__next" type="button">Continue <span class="ft-bf__arrow" aria-hidden="true">&rarr;</span></button></div>
            </section>
            <section class="ft-bf__step" data-step="2" hidden>
                <h3 class="ft-bf__question">Property type</h3>
                <div class="ft-bf__choices ft-bf__choices--property">
                    <?php foreach ($property_types as $property_type) : ?>
                        <button class="ft-bf__choice ft-bf__property" type="button" data-field="propertyType" data-value="<?php echo esc_attr($property_type); ?>"><span class="ft-bf__property-icon"><?php echo $property_icons[$property_type] ?? ''; ?></span><span><?php echo esc_html($property_type); ?></span></button>
                    <?php endforeach; ?>
                </div>
                <label class="ft-bf__field" style="margin-top:20px">
                    <span>When are you looking to start?</span>
                    <select class="ft-bf__select" name="startTime">
                        <?php foreach ($start_times as $start_time) : ?>
                            <option value="<?php echo esc_attr($start_time); ?>"><?php echo esc_html($start_time); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="ft-bf__actions"><button class="ft-bf__back" type="button">&larr; Back</button><button class="ft-bf__next" type="button">Continue <span class="ft-bf__arrow" aria-hidden="true">&rarr;</span></button></div>
            </section>
            <section class="ft-bf__step" data-step="3" hidden>
                <h3 class="ft-bf__question">Your contact details</h3>
                <label class="ft-bf__field"><span>Full name</span><input class="ft-bf__input" name="fullName" type="text" autocomplete="name" placeholder="Jane Doe" required></label>
                <label class="ft-bf__field"><span>Email</span><input class="ft-bf__input" name="email" type="email" autocomplete="email" placeholder="jane@email.com" required></label>
                <label class="ft-bf__field">
                    <span>Phone</span>
                    <div class="ft-bf__phone-wrap">
                        <span class="ft-bf__phone-pfx" aria-hidden="true">+1</span>
                        <input class="ft-bf__input" name="phoneLocal" type="tel" autocomplete="tel" placeholder="(416) 555-0199" required>
                    </div>
                </label>
                <div class="ft-bf__actions"><button class="ft-bf__back" type="button">&larr; Back</button><button class="ft-bf__next" type="button">Continue <span class="ft-bf__arrow" aria-hidden="true">&rarr;</span></button></div>
            </section>
            <section class="ft-bf__step" data-step="4" hidden>
                <h3 class="ft-bf__question">Where should we visit?</h3>
                <div class="ft-bf__addr-row">
                    <label class="ft-bf__field">
                        <span>Street address</span>
                        <input class="ft-bf__input ft-bf__street" name="street" type="text" autocomplete="address-line1" placeholder="123 Main St" required>
                    </label>
                    <label class="ft-bf__field">
                        <span>Unit / Apt</span>
                        <input class="ft-bf__input" name="unit" type="text" autocomplete="address-line2" placeholder="2B">
                    </label>
                </div>
                <div class="ft-bf__columns">
                    <label class="ft-bf__field">
                        <span>City</span>
                        <input class="ft-bf__input ft-bf__city" name="city" type="text" autocomplete="address-level2" placeholder="Toronto" required>
                    </label>
                    <label class="ft-bf__field">
                        <span>Province</span>
                        <div class="ft-bf__sel-wrap">
                        <select class="ft-bf__select ft-bf__province" name="province" required>
                            <option value="">Select...</option>
                            <option value="AB">Alberta</option>
                            <option value="BC">British Columbia</option>
                            <option value="MB">Manitoba</option>
                            <option value="NB">New Brunswick</option>
                            <option value="NL">Newfoundland and Labrador</option>
                            <option value="NS">Nova Scotia</option>
                            <option value="NT">Northwest Territories</option>
                            <option value="NU">Nunavut</option>
                            <option value="ON" selected>Ontario</option>
                            <option value="PE">Prince Edward Island</option>
                            <option value="QC">Quebec</option>
                            <option value="SK">Saskatchewan</option>
                            <option value="YT">Yukon</option>
                        </select>
                        </div>
                    </label>
                </div>
                <div class="ft-bf__columns">
                    <label class="ft-bf__field">
                        <span>Postal code</span>
                        <input class="ft-bf__input ft-bf__postal" name="postalCode" type="text" autocomplete="postal-code" placeholder="A1A 1A1" required>
                    </label>
                    <label class="ft-bf__field">
                        <span>Country</span>
                        <input class="ft-bf__input" name="country" type="text" value="Canada" readonly>
                    </label>
                </div>
                <div class="ft-bf__actions"><button class="ft-bf__back" type="button">&larr; Back</button><button class="ft-bf__submit" type="submit">Get My Free Estimate <span class="ft-bf__arrow" aria-hidden="true">&rarr;</span></button></div>
            </section>
            <p class="ft-bf__error" role="alert" hidden></p>
        </form>
        <div class="ft-bf__success" hidden>
            <span class="ft-bf__success-icon" aria-hidden="true">&check;</span>
            <h3>Request received</h3>
            <p>A Floors Today specialist will contact you shortly.</p>
        </div>
    </div>
    <script>
        (function () {
            var root = document.getElementById(<?php echo wp_json_encode($instance_id); ?>);
            if (!root || root.dataset.ready === '1') return;
            root.dataset.ready = '1';

            var form = root.querySelector('.ft-bf__form');
            var error = root.querySelector('.ft-bf__error');
            var state = { step: 1, flooringType: '', propertyType: '', totalSteps: 4 };

            function showError(message) {
                error.textContent = message || '';
                error.hidden = !message;
            }

            function showStep(step) {
                state.step = step;
                root.querySelectorAll('[data-step]').forEach(function (panel) {
                    panel.hidden = Number(panel.dataset.step) !== step;
                });
                root.querySelectorAll('[data-dot]').forEach(function (dot) {
                    var number = Number(dot.dataset.dot);
                    dot.classList.toggle('is-active', number === step);
                    dot.classList.toggle('is-complete', number < step);
                    dot.textContent = number < step ? '\u2713' : String(number);
                });
                root.querySelectorAll('[data-line]').forEach(function (line) {
                    line.classList.toggle('is-complete', Number(line.dataset.line) < step);
                });
                showError('');
            }

            root.addEventListener('click', function (event) {
                var choice = event.target.closest('.ft-bf__choice');
                var next = event.target.closest('.ft-bf__next');
                var back = event.target.closest('.ft-bf__back');

                if (choice) {
                    state[choice.dataset.field] = choice.dataset.value;
                    root.querySelectorAll('[data-field="' + choice.dataset.field + '"]').forEach(function (item) {
                        item.classList.toggle('is-selected', item === choice);
                    });
                }

                if (next) {
                    if (state.step === 1 && !state.flooringType) return showError('Please choose a flooring option.');
                    if (state.step === 2 && !state.propertyType) return showError('Please choose a property type.');
                    if (state.step === 3) {
                        var fullName = (root.querySelector('[name="fullName"]').value || '').trim();
                        var email = (root.querySelector('[name="email"]').value || '').trim();
                        var phoneLocal = (root.querySelector('[name="phoneLocal"]').value || '').trim();
                        if (!fullName) return showError('Please enter your full name.');
                        if (fullName.split(/\s+/).filter(Boolean).length < 2) return showError('Please enter your first and last name.');
                        if (!email) return showError('Please enter your email address.');
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return showError('Please enter a valid email address.');
                        if (!phoneLocal) return showError('Please enter your phone number.');
                    }
                    showStep(Math.min(state.totalSteps, state.step + 1));
                }

                if (back) showStep(Math.max(1, state.step - 1));
            });

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                showError('');

                if (!form.reportValidity()) return;

                var data = new FormData(form);
                var fullName = String(data.get('fullName') || '').trim();
                if (fullName.split(/\s+/).filter(Boolean).length < 2) {
                    return showError('Please enter your first and last name.');
                }
                var phoneLocal = String(data.get('phoneLocal') || '').trim();
                var phone = '+1 ' + phoneLocal;
                var street = String(data.get('street') || '').trim();
                var unit = String(data.get('unit') || '').trim();
                var city = String(data.get('city') || '').trim();
                var province = String(data.get('province') || '').trim();
                var postalCode = String(data.get('postalCode') || '').trim();
                var addressParts = [street + (unit ? ' Unit ' + unit : ''), city, province, postalCode, 'Canada'];
                var address = addressParts.filter(Boolean).join(', ');

                var submit = root.querySelector('.ft-bf__submit');
                submit.disabled = true;
                    submit.textContent = 'Sending...';

                try {
                    var pageUrl = new URL(window.location.href);
                    var referrer = document.referrer || '';
                    var referrerHost = referrer ? new URL(referrer).hostname.replace(/^www\./, '') : '';
                    var utmSource = pageUrl.searchParams.get('hello_social') || pageUrl.searchParams.get('utm_source') || '';
                    var attribution = window.ftGetAttribution ? window.ftGetAttribution() : {};
                    utmSource = attribution.utmSource || utmSource;
                    referrer = attribution.referrerUrl || referrer;
                    referrerHost = attribution.referrerHost || referrerHost;
                    var response = await fetch(root.dataset.endpoint, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            fullName: fullName,
                            email: data.get('email'),
                            phone: phone,
                            address: address,
                            street: street,
                            unit: unit,
                            city: city,
                            province: province,
                            postalCode: postalCode,
                            flooringType: state.flooringType,
                            propertyType: state.propertyType,
                            startTime: data.get('startTime'),
                            ftInboxTrap: data.get('ftInboxTrap'),
                            source: 'WordPress booking form shortcode',
                            pageUrl: window.location.href,
                            trafficSource: attribution.trafficSource || utmSource || referrerHost || 'Direct',
                            referrerUrl: referrer,
                            utmSource: utmSource,
                            utmMedium: attribution.utmMedium || pageUrl.searchParams.get('utm_medium') || '',
                            utmCampaign: attribution.utmCampaign || pageUrl.searchParams.get('utm_campaign') || '',
                            utmContent: attribution.utmContent || pageUrl.searchParams.get('utm_content') || '',
                            utmTerm: attribution.utmTerm || pageUrl.searchParams.get('utm_term') || '',
                            devicePlatform: /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent) ? 'Mobile / Tablet' : 'Desktop'
                        })
                    });
                    var result = await response.json().catch(function () { return null; });
                    if (!response.ok) throw new Error(result && result.message ? result.message : 'We could not send your request.');

                    form.hidden = true;
                    root.querySelector('.ft-bf__progress').hidden = true;
                    root.querySelector('.ft-bf__success').hidden = false;

                    if (window.XDTrack && window.XDTrack.lead) {
                        window.XDTrack.lead({
                            formId:       'booking_form',
                            formName:     'Booking Form Shortcode',
                            flooringType: state.flooringType,
                            leadSource:   attribution.trafficSource || utmSource || 'Direct',
                            utmSource:    utmSource,
                        });
                    }
                } catch (requestError) {
                    showError(requestError.message || 'We could not send your request. Please try again.');
                    submit.disabled = false;
                    submit.innerHTML = 'Get My Free Estimate <span class="ft-bf__arrow" aria-hidden="true">&rarr;</span>';
                }
            });

            function ftInitPlaces() {
                var streetInput = root.querySelector('.ft-bf__street');
                if (!streetInput || streetInput.dataset.acInit) return;
                streetInput.dataset.acInit = '1';
                var ac = new google.maps.places.Autocomplete(streetInput, {
                    types: ['address'],
                    componentRestrictions: { country: 'ca' },
                    fields: ['address_components']
                });
                ac.addListener('place_changed', function () {
                    var place = ac.getPlace();
                    if (!place || !place.address_components) return;
                    var map = {};
                    place.address_components.forEach(function (c) {
                        c.types.forEach(function (t) { map[t] = c; });
                    });
                    var streetNumber = map['street_number'] ? map['street_number'].long_name : '';
                    var route = map['route'] ? map['route'].long_name : '';
                    streetInput.value = (streetNumber + ' ' + route).trim();
                    var cityEl = root.querySelector('.ft-bf__city');
                    var provinceEl = root.querySelector('.ft-bf__province');
                    var postalEl = root.querySelector('.ft-bf__postal');
                    if (cityEl && map['locality']) cityEl.value = map['locality'].long_name;
                    if (provinceEl && map['administrative_area_level_1']) provinceEl.value = map['administrative_area_level_1'].short_name;
                    if (postalEl && map['postal_code']) postalEl.value = map['postal_code'].long_name;
                });
            }

            if (window.__ftPlacesLoaded) {
                ftInitPlaces();
            } else {
                document.addEventListener('ft:places:ready', ftInitPlaces);
            }
        }());
    </script>
    <?php

    return ob_get_clean();
}

add_shortcode('floors_booking_form', 'ft_next_booking_form_shortcode');

// Inject Facebook Pixel in <head> of every standard WordPress page.
// The React homepage injects it via useEffect instead (homepage exits before wp_head for content).
add_action('wp_head', function () {
    $settings = ft_next_homepage_settings();
    $pixel_id = sanitize_text_field($settings['fb_pixel_id'] ?? '');
    if ($pixel_id === '') return;
    ?>
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init','<?php echo esc_js($pixel_id); ?>');fbq('track','PageView');
</script>
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr($pixel_id); ?>&ev=PageView&noscript=1"/></noscript>
<!-- End Facebook Pixel Code -->
    <?php
});

// Inject chat widget script in the footer of every standard WordPress page.
// The homepage is served via template_redirect (exits early), so wp_footer never
// fires there — the React homepage handles injection via useEffect instead.
add_action('wp_footer', function () {
    $settings = ft_next_homepage_settings();
    $chat_src = $settings['chat_script_src'] ?? '';
    $chat_id  = $settings['chat_script_id'] ?? '';
    if ($chat_src === '') return;
    $id_attr = $chat_id ? ' id="' . esc_attr($chat_id) . '"' : '';
    echo '<script' . $id_attr . ' src="' . esc_url($chat_src) . '" defer></script>' . "\n";
});

add_action('rest_api_init', function () {
    register_rest_route('floors-today/v1', '/homepage', [
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function () {
            $saved = get_option(FT_NEXT_HOME_OPTION, null);

            if (!is_array($saved) || empty($saved)) {
                return new WP_Error(
                    'ft_next_homepage_missing_settings',
                    'Homepage settings have not been saved for this WordPress install.',
                    ['status' => 503]
                );
            }

            return rest_ensure_response(ft_next_homepage_settings());
        },
    ]);
});







