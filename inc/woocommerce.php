<?php

declare(strict_types=1);

/**
 * WooCommerce integration.
 *
 * Only loaded when the WooCommerce plugin is active (see functions.php).
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

/**
 * The theme owns product/shop markup via woocommerce.php + this file, so the
 * plugin's own frontend stylesheet is not needed — style from assets/scss
 * instead (see assets/scss/components/_woocommerce.scss).
 */
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

/**
 * WooCommerce Blocks unconditionally enqueues its `wc-blocks-style` stylesheet
 * on every front-end request (Notices::enqueue_notice_styles(), hooked to
 * wp_head with no page check) — "just in case" a WC block needs it. On this
 * site only the Cart and Checkout pages actually contain WC block markup
 * (`wp:woocommerce/cart`, `wp:woocommerce/checkout`) — confirmed via a DB
 * query across every published page/post — so everywhere else this is pure
 * render-blocking dead weight (flagged by Lighthouse). Dequeue it except on
 * the two pages that need it, and on any future page that adds a WC block.
 * Priority 11 runs right after the priority-10 hook that enqueues it.
 */
add_action('wp_head', function () {
	if (is_cart() || is_checkout()) {
		return;
	}

	if (is_singular()) {
		$post = get_queried_object();
		if ($post instanceof WP_Post && str_contains((string) $post->post_content, 'wp:woocommerce/')) {
			return;
		}
	}

	wp_dequeue_style('wc-blocks-style');
	wp_deregister_style('wc-blocks-style');
}, 11);

/**
 * There is no on-site checkout or account area — orders are confirmed over
 * WhatsApp (see page-cart.php). WooCommerce still requires its Checkout and
 * My Account pages to exist, but page.php only renders ACF content sections,
 * so both would render blank — send anyone who lands on them to the cart.
 */
add_action('template_redirect', function () {
	if (is_checkout() || is_account_page()) {
		wp_safe_redirect(wc_get_page_permalink('cart'));
		exit;
	}
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
 *
 * @param int $limit Maximum number of products to return.
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
 * Related products for the single product page's "You may also like" grid
 * (see woocommerce/content-single-product.php).
 *
 * @param int $product_id Product to find related products for.
 * @param int $limit      Maximum number of products to return.
 */
function sweetmunchies_get_related_products(int $product_id, int $limit = 4): array
{
	$related_ids = wc_get_related_products($product_id, $limit);

	if (!$related_ids) {
		return array();
	}

	return wc_get_products(array(
		'status'  => 'publish',
		'include' => $related_ids,
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
