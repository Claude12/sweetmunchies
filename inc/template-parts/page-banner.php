<?php

declare(strict_types=1);

/**
 * Interior page banner — photo + gradient overlay hero with a RankMath
 * breadcrumb strip beneath it. Standalone (not a content_sections layout),
 * called directly from page.php for every non-front page. Renders nothing
 * if no background image has been set for the page.
 *
 * Pass $args['object'] (a post ID or WP_Term) to read the ACF fields from
 * something other than the current queried post — used by
 * woocommerce/archive-product.php, where the "current post" during the
 * product loop isn't the Shop page or category term.
 *
 * Pass $args['hero'] = false to skip the image/heading section entirely
 * and render just the breadcrumb strip — used by archive-product.php so
 * the Shop page and category archives get straight to the product grid
 * without a full-height hero pushing it down. In that mode the breadcrumb
 * gets extra top clearance (--no-hero modifier, see _page-banner.scss) —
 * the header logo badge deliberately overhangs below the header bar,
 * which is invisible against a full hero photo but clips the breadcrumb
 * text without one.
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

$show_hero = $args['hero'] ?? true;

if ($show_hero) :
    $object     = $args['object'] ?? get_the_ID();
    $eyebrow    = get_field('banner_eyebrow', $object);
    $heading    = get_field('banner_heading', $object);
    $background = get_field('banner_background', $object);

    // Product category archives (WP_Term) rarely get their own banner image
    // configured — fall back to the Shop page's banner (image + heading +
    // eyebrow) wholesale so every archive still gets a hero instead of this
    // template silently rendering nothing.
    if (!$background && $object instanceof WP_Term) {
        $shop_page_id = wc_get_page_id('shop');
        $eyebrow      = get_field('banner_eyebrow', $shop_page_id);
        $heading      = get_field('banner_heading', $shop_page_id);
        $background   = get_field('banner_background', $shop_page_id);
    }

    $heading = $heading ?: ($object instanceof WP_Term ? $object->name : get_the_title($object));

    if (!$background) :
        return;
    endif;
    ?>

    <section class="page-banner" style="background-image: url('<?php echo esc_url($background['sizes']['sweetmunchies_page_banner'] ?? $background['url']); ?>');">
        <div class="page-banner__overlay"></div>
        <div class="container page-banner__content">
            <?php if ($eyebrow): ?>
                <p class="page-banner__eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <?php endif; ?>
            <h1 class="page-banner__heading"><?php echo esc_html($heading); ?></h1>
        </div>
    </section>
<?php endif; ?>

<?php if (function_exists('rank_math_the_breadcrumbs')): ?>
    <div class="container page-banner__breadcrumb<?php echo $show_hero ? '' : ' page-banner__breadcrumb--no-hero'; ?>">
        <?php rank_math_the_breadcrumbs(); ?>
    </div>
<?php endif; ?>
