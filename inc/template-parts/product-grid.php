<?php

declare(strict_types=1);

/**
 * Shared product grid partial used by the homepage's Featured Products and
 * Best Sellers blocks (see inc/blocks/featured-products.php,
 * inc/blocks/best-sellers.php). Renders each product through
 * woocommerce/content-product.php (the theme's ProductCard override) so the
 * card markup lives in exactly one place, re-used site-wide.
 *
 * Expects $args['products'] (WC_Product[]) — get_template_part()'s third
 * argument makes $args available here as a whole array, it does not extract
 * individual keys into their own variables.
 *
 * Pass $args['eager_first'] = true only when this grid is the page's first
 * above-the-fold content (currently just the shop/category archive — see
 * woocommerce/archive-product.php) so its first card's image loads eagerly
 * instead of lazily, since it's likely the page's LCP element. Leave it
 * false (the default) anywhere this partial renders further down the page
 * (homepage Best Sellers/Featured Products, single-product's related grid)
 * where lazy-loading the first card too is correct, not a regression.
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

$products    = $args['products'] ?? array();
$eager_first = $args['eager_first'] ?? false;

if (empty($products)) {
    return;
}
?>

<ul class="product-grid__list">
    <?php
    foreach ($products as $index => $product) {
        $post_object = get_post($product->get_id());
        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- temporary loop context for content-product.php, restored by wp_reset_postdata() below.
        $GLOBALS['post'] = $post_object;
        setup_postdata($post_object);
        set_query_var('product_card_eager', $eager_first && 0 === $index);
        wc_get_template_part('content', 'product');
    }
    wp_reset_postdata();
    ?>
</ul>
