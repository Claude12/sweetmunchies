<?php

declare(strict_types=1);

/**
 * Block: Best Sellers
 * ACF flexible content layout `best_sellers` — a heading + grid of
 * manually-picked WooCommerce products (see the "Products" relationship
 * field). "View all" always points at the real shop archive, unfiltered.
 * Rendered inside the have_rows()/the_row() loop in
 * sweetmunchies_render_flexible_content() (see inc/acf.php). Product cards
 * render via inc/template-parts/product-grid.php (shared with
 * featured-products.php).
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

$heading      = get_sub_field('heading');
$subtext      = get_sub_field('subtext');
$product_ids  = get_sub_field('products') ?: array();

$products = function_exists('wc_get_product')
    ? array_filter(array_map('wc_get_product', $product_ids))
    : array();

if (!$products) {
    return;
}

$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : get_post_type_archive_link('product');
?>

<section class="product-grid product-grid--best-sellers">
    <div class="container">
        <div class="product-grid__header" animate="fade-in">
            <?php if ($heading): ?>
                <h2 class="product-grid__heading"><?php echo esc_html($heading); ?></h2>
            <?php endif; ?>
            <?php if ($subtext): ?>
                <p class="product-grid__subtext"><?php echo esc_html($subtext); ?></p>
            <?php endif; ?>
            <?php if ($shop_url): ?>
                <a href="<?php echo esc_url($shop_url); ?>" class="product-grid__view-all">
                    <?php esc_html_e('View all →', 'sweetmunchies'); ?>
                </a>
            <?php endif; ?>
        </div>

        <?php get_template_part('inc/template-parts/product-grid', null, array('products' => $products)); ?>
    </div>
</section>
