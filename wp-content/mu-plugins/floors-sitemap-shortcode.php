<?php
/**
 * Plugin Name: Floors Sitemap Shortcode
 * Description: Human-readable HTML sitemap ([floors_sitemap]) listing pages, product categories, and the products archive.
 * Version: 1.0.0
 * Author: Floors Today
 */

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('floors_sitemap', 'ft_sitemap_shortcode');

// Every real page is bucketed into one of these sections by slug, so the
// sitemap reads as organized navigation rather than one flat list. Any
// published page whose slug isn't listed here still shows up, under
// "More" - new pages are never silently dropped from the sitemap. Only
// real, existing site sections are represented here (no location or
// brand pages exist on this site to list).
function ft_sitemap_page_sections() {
    return [
        'Main Pages' => ['about', 'contact', 'financing', 'faqs'],
        'Services' => ['how-shop-at-home-works'],
        'Customer Resources' => ['product-care', 'warranty', '3-year-limited-flooring-warranty', 'refund_returns', 'terms-of-use', 'privacy-policy'],
        'Account' => ['login'],
    ];
}

function ft_sitemap_section_icon($label) {
    $icons = [
        'Main Pages' => '🏠',
        'Shop Flooring' => '🪵',
        'Services' => '🛠️',
        'Customer Resources' => '📚',
        'Account' => '👤',
        'More' => '🔗',
    ];
    return $icons[$label] ?? '🔗';
}

function ft_sitemap_shortcode() {
    $pages = get_posts([
        'post_type' => 'page',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'menu_order title',
        'order' => 'ASC',
    ]);

    $categories = get_terms([
        'taxonomy' => 'categories',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    ]);
    if (is_wp_error($categories)) {
        $categories = [];
    }

    // Bucket published pages by slug into the sections above, in section
    // order - anything unmatched lands in "More" rather than disappearing.
    $sectionSlugs = ft_sitemap_page_sections();
    $bucketed = array_fill_keys(array_keys($sectionSlugs), []);
    $bucketed['More'] = [];
    foreach ($pages as $page) {
        $placed = false;
        foreach ($sectionSlugs as $label => $slugs) {
            if (in_array($page->post_name, $slugs, true)) {
                $bucketed[$label][] = $page;
                $placed = true;
                break;
            }
        }
        if (!$placed) {
            $bucketed['More'][] = $page;
        }
    }

    ft_sitemap_assets();

    ob_start();
    ?>
    <div class="ft-sitemap">
        <?php foreach ($bucketed as $label => $sectionPages) :
            if ($label === 'Main Pages') {
                // Home always leads this section; it isn't a WP "page" post.
                ?>
                <section class="ft-sitemap__section">
                    <h2 class="ft-sitemap__heading"><span class="ft-sitemap__icon" aria-hidden="true"><?php echo ft_sitemap_section_icon($label); ?></span><?php echo esc_html($label); ?></h2>
                    <ul class="ft-sitemap__list">
                        <li><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
                        <?php foreach ($sectionPages as $page) : ?>
                            <li><a href="<?php echo esc_url(get_permalink($page)); ?>"><?php echo esc_html(get_the_title($page)); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
                <?php
                continue;
            }

            if ($label === 'Services') {
                ?>
                <section class="ft-sitemap__section">
                    <h2 class="ft-sitemap__heading"><span class="ft-sitemap__icon" aria-hidden="true"><?php echo ft_sitemap_section_icon($label); ?></span><?php echo esc_html($label); ?></h2>
                    <ul class="ft-sitemap__list">
                        <li><a href="<?php echo esc_url(home_url('/#estimate')); ?>">Book a Free Estimate</a></li>
                        <?php foreach ($sectionPages as $page) : ?>
                            <li><a href="<?php echo esc_url(get_permalink($page)); ?>"><?php echo esc_html(get_the_title($page)); ?></a></li>
                        <?php endforeach; ?>
                        <li><a href="https://app.flooringliquidators.ca/" target="_blank" rel="noopener noreferrer">Employee Support ↗</a></li>
                    </ul>
                </section>
                <?php
                continue;
            }

            if (empty($sectionPages)) {
                continue;
            }
            ?>
            <section class="ft-sitemap__section">
                <h2 class="ft-sitemap__heading"><span class="ft-sitemap__icon" aria-hidden="true"><?php echo ft_sitemap_section_icon($label); ?></span><?php echo esc_html($label); ?></h2>
                <ul class="ft-sitemap__list">
                    <?php foreach ($sectionPages as $page) : ?>
                        <li><a href="<?php echo esc_url(get_permalink($page)); ?>"><?php echo esc_html(get_the_title($page)); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endforeach; ?>

        <?php if (!empty($categories)) : ?>
        <section class="ft-sitemap__section">
            <h2 class="ft-sitemap__heading"><span class="ft-sitemap__icon" aria-hidden="true">🪵</span>Shop Flooring</h2>
            <ul class="ft-sitemap__list">
                <li><a href="<?php echo esc_url(home_url('/our-products/')); ?>">Shop All Flooring</a></li>
                <?php foreach ($categories as $category) : ?>
                    <li>
                        <a href="<?php echo esc_url(home_url('/categories/' . $category->slug . '/')); ?>">
                            <?php echo esc_html($category->name); ?>
                        </a>
                        <span class="ft-sitemap__count"><?php echo (int) $category->count; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function ft_sitemap_assets() {
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    $css = '
        .ft-sitemap, .ft-sitemap * { box-sizing: border-box; }
        .ft-sitemap { display:grid; gap:36px; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); max-width:1100px; margin:0 auto; padding:8px 0; font-family:Arial, Helvetica, sans-serif; }
        .ft-sitemap__section { min-width:0; }
        .ft-sitemap__heading { display:flex; align-items:center; gap:10px; margin:0 0 16px; color:#235bb8; font-size:20px; font-weight:700; line-height:1.2; }
        .ft-sitemap__icon { font-size:20px; line-height:1; }
        .ft-sitemap__list { list-style:none; margin:0; padding:0; display:grid; gap:10px; }
        .ft-sitemap__list li { display:flex; align-items:baseline; justify-content:space-between; gap:8px; }
        .ft-sitemap__list a { color:#374151; font-size:15px; line-height:1.5; text-decoration:none; }
        .ft-sitemap__list a:hover, .ft-sitemap__list a:focus-visible { color:#235bb8; text-decoration:underline; }
        .ft-sitemap__count { flex-shrink:0; color:#9ca3af; font-size:13px; }
        @media (max-width:640px) { .ft-sitemap { gap:28px; } }
    ';

    wp_register_style('ft-sitemap', false, [], '1.0.0');
    wp_enqueue_style('ft-sitemap');
    wp_add_inline_style('ft-sitemap', $css);
}
