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
 * @package sweetmunchies
 */

$products = $args['products'] ?? array();

if (empty($products)) {
    return;
}
?>

<ul class="product-grid__list">
    <?php
    foreach ($products as $product) {
        $post_object = get_post($product->get_id());
        setup_postdata($GLOBALS['post'] = $post_object);
        wc_get_template_part('content', 'product');
    }
    wp_reset_postdata();
    ?>
</ul>
