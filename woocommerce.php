<?php

declare(strict_types=1);

/**
 * The template for displaying WooCommerce pages (shop, product, cart, etc).
 *
 * WordPress' template hierarchy loads this automatically for any WooCommerce
 * page in place of archive.php/single.php — it wins over WC's own
 * archive-product.php/taxonomy-product_cat.php template-loader search
 * (see class-wc-template-loader.php::get_template_loader_files(), which
 * checks for a theme-root woocommerce.php before those), so shop/category
 * archives are routed here explicitly rather than relying on that loader.
 * Single products still go through woocommerce_content(), which pulls in
 * woocommerce/content-single-product.php for is_singular('product').
 *
 * @link https://woocommerce.com/document/woocommerce-theme-developer-handbook/
 *
 * @package sweetmunchies
 */

get_header();

if (is_shop() || is_product_taxonomy()) {
    wc_get_template_part('archive', 'product');
} else {
    woocommerce_content();
}

get_footer();
