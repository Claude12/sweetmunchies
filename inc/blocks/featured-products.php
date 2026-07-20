<?php

declare(strict_types=1);

/**
 * Block: Featured Products
 * ACF flexible content layout `featured_products` — a heading + grid of
 * WooCommerce products flagged "Featured" in wp-admin. Rendered inside the
 * have_rows()/the_row() loop in sweetmunchies_render_flexible_content()
 * (see inc/acf.php). Product cards render via
 * inc/template-parts/product-grid.php (shared with best-sellers.php).
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

$heading       = get_sub_field('heading');
$subtext       = get_sub_field('subtext');
$product_limit = (int) (get_sub_field('product_limit') ?: 4);
$view_all_link = get_sub_field('view_all_link');

$products = function_exists('sweetmunchies_get_featured_products')
    ? sweetmunchies_get_featured_products($product_limit)
    : array();

if (!$products) {
    return;
}
?>

<section class="product-grid product-grid--featured">
    <img class="product-grid__deco" src="<?php echo esc_url(get_template_directory_uri() . '/images/candy-icon.webp'); ?>" alt="" aria-hidden="true" />
    <div class="container">
        <div class="product-grid__header" animate="fade-in">
            <?php if ($heading): ?>
                <h2 class="product-grid__heading"><?php echo esc_html($heading); ?></h2>
            <?php endif; ?>
            <?php if ($subtext): ?>
                <p class="product-grid__subtext"><?php echo esc_html($subtext); ?></p>
            <?php endif; ?>
            <?php if (!empty($view_all_link['url'])): ?>
                <a href="<?php echo esc_url($view_all_link['url']); ?>" class="product-grid__view-all"
                    target="<?php echo esc_attr($view_all_link['target'] ?: '_self'); ?>">
                    <?php echo esc_html($view_all_link['title']); ?>
                </a>
            <?php endif; ?>
        </div>

        <?php get_template_part('inc/template-parts/product-grid', null, array('products' => $products)); ?>
    </div>
</section>
