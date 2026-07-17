<?php

declare(strict_types=1);

/**
 * WooCommerce integration.
 *
 * Only loaded when the WooCommerce plugin is active (see functions.php).
 *
 * @package sweetmunchies
 */

/**
 * The theme owns product/shop markup via woocommerce.php + this file, so the
 * plugin's own frontend stylesheet is not needed — style from assets/scss
 * instead (see assets/scss/components/_woocommerce.scss).
 */
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

/**
 * Swap WooCommerce's default content wrapper for the theme's <main> markup.
 * woocommerce.php calls woocommerce_content() between these two hooks.
 */
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('woocommerce_before_main_content', function () {
	echo '<main id="primary" class="site-main woocommerce-page">';
});

add_action('woocommerce_after_main_content', function () {
	echo '</main><!-- #main -->';
});

/**
 * Shop/archive grid columns and products-per-page. Adjust to match the
 * product grid design once it's built.
 */
add_filter('loop_shop_columns', function () {
	return 3;
});
