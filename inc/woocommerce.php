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

/**
 * Product queries for the homepage's Featured Products / Best Sellers blocks
 * (see inc/blocks/featured-products.php, inc/blocks/best-sellers.php).
 */
function sweetmunchies_get_featured_products(int $limit = 4): array
{
	return wc_get_products(array(
		'status'   => 'publish',
		'limit'    => $limit,
		'featured' => true,
		'orderby'  => 'menu_order',
		'order'    => 'ASC',
	));
}

/**
 * WooCommerce's cart-fragments script is normally only enqueued by the
 * classic Cart Widget, which this theme doesn't use — load it directly so
 * the header cart badge (see header.php + the fragments filter below) can
 * update live after an AJAX add-to-cart, without a full page reload.
 */
add_action('wp_enqueue_scripts', function () {
	if (function_exists('WC') && WC()->cart) {
		wp_enqueue_script('wc-cart-fragments');
	}
});

/**
 * Keep the header cart-count badge in sync with WooCommerce's own AJAX
 * add-to-cart fragments (applied client-side by wc-cart-fragments.js).
 */
add_filter('woocommerce_add_to_cart_fragments', function (array $fragments): array {
	$count = WC()->cart->get_cart_contents_count();

	ob_start();
	?>
	<span class="header__cart-count<?php echo $count > 0 ? '' : ' is-hidden'; ?>"><?php echo esc_html((string) $count); ?></span>
	<?php
	$fragments['.header__cart-count'] = ob_get_clean();

	return $fragments;
});
