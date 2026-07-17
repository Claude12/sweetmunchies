<?php
/**
 * The template for displaying WooCommerce pages (shop, product, cart, etc).
 *
 * WordPress' template hierarchy loads this automatically for any WooCommerce
 * page in place of archive.php/single.php. The <main> wrapper markup comes
 * from the woocommerce_before_main_content / woocommerce_after_main_content
 * hooks registered in inc/woocommerce.php — override individual page/loop
 * templates by copying them into the theme's woocommerce/ directory.
 *
 * @link https://woocommerce.com/document/woocommerce-theme-developer-handbook/
 *
 * @package sweetmunchies
 */

get_header();

woocommerce_content();

get_footer();
