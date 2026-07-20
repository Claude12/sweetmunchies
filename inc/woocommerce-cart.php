<?php

declare(strict_types=1);

/**
 * Cart-item-data hooks for the single product page's "Add a photo & message"
 * add-on (+$2 per unit). See woocommerce/content-single-product.php for the
 * form fields and assets/js/lib/product-add-to-cart.js for the AJAX submit.
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.Security.NonceVerification.Missing -- deliberate here: this mirrors WooCommerce core's own nonce-less add-to-cart form submission (product forms don't carry a nonce either), and the value is sanitized on read. The wc_ajax cart endpoints further down this file DO verify a nonce (see check_ajax_referer() calls below) since those are same-origin fetch() calls this theme controls, not a third-party form submission WC core has to stay compatible with.

/**
 * Flat surcharge for the "Add a photo & message" add-on, CMS-managed via
 * Theme Settings → Shop → Gift Message Price (falls back to $2 if unset).
 */
function sweetmunchies_gift_message_price(): float
{
	$price = get_field('gift_message_price', 'option');

	return is_numeric($price) ? (float) $price : 2.0;
}

/**
 * Capture the gift-message checkbox + textarea from the add-to-cart POST and
 * stash them on the cart item. A unique key is added so an identical product
 * with a different message doesn't merge into one cart line.
 */
add_filter('woocommerce_add_cart_item_data', function (array $cart_item_data): array {
	if (empty($_POST['gift_message_enabled'])) {
		return $cart_item_data;
	}

	// 500-char cap matches the textarea's maxlength (see
	// content-single-product.php) — enforced here too so a hand-crafted POST
	// can't store an oversized message in the session.
	$message = isset($_POST['gift_message']) ? mb_substr(sanitize_textarea_field(wp_unslash($_POST['gift_message'])), 0, 500) : '';

	$cart_item_data['gift_message'] = $message;
	$cart_item_data['unique_key']   = md5(microtime() . wp_rand());

	return $cart_item_data;
}, 10, 1);

/**
 * Add the +$2 surcharge per unit for cart items with a gift message.
 */
add_action('woocommerce_before_calculate_totals', function (WC_Cart $cart): void {
	if (did_action('woocommerce_before_calculate_totals') > 1) {
		return;
	}

	foreach ($cart->get_cart() as $cart_item) {
		if (empty($cart_item['gift_message'])) {
			continue;
		}

		$product = $cart_item['data'];
		$product->set_price($product->get_price() + sweetmunchies_gift_message_price());
	}
});

/**
 * Show the gift message under the line item in cart/checkout.
 */
add_filter('woocommerce_get_item_data', function (array $item_data, array $cart_item): array {
	if (empty($cart_item['gift_message'])) {
		return $item_data;
	}

	$item_data[] = array(
		'name'  => __('Photo & message added', 'sweetmunchies'),
		'value' => wp_kses_post($cart_item['gift_message']),
	);

	return $item_data;
}, 10, 2);

/**
 * Persist the gift message to order item meta so it appears in admin order
 * views and order emails.
 */
add_action('woocommerce_checkout_create_order_line_item', function (WC_Order_Item_Product $item, string $cart_item_key, array $values): void {
	if (empty($values['gift_message'])) {
		return;
	}

	$item->add_meta_data(__('Photo & message added', 'sweetmunchies'), $values['gift_message']);
}, 10, 3);

// phpcs:enable WordPress.Security.NonceVerification.Missing -- the two actions below verify their own nonce via check_ajax_referer().

/**
 * Cart-page quantity/remove AJAX actions (see page-cart.php and
 * assets/js/lib/cart-page.js). No built-in WC_AJAX action updates quantity,
 * so this mirrors the endpoint convention WC_AJAX::add_to_cart/remove_from_cart
 * already use, ending with the same get_refreshed_fragments() JSON shape so
 * the header cart-count badge stays in sync via wc-cart-fragments.js. Unlike
 * that WC core convention, these DO require a nonce (see the data-nonce
 * attribute in page-cart.php) since, unlike core's add-to-cart form, there's
 * no reason a third party would ever need to submit to these endpoints
 * without having loaded the cart page first.
 */
add_action('wc_ajax_sweetmunchies_update_cart_item', function (): void {
	check_ajax_referer('sweetmunchies_cart_action', 'nonce');

	$cart_item_key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';
	// 99 cap matches the qty inputs' max (see content-single-product.php /
	// page-cart.php) — enforced here too for hand-crafted POSTs.
	$quantity      = isset($_POST['quantity']) ? min(absint($_POST['quantity']), 99) : 0;

	if ($cart_item_key && $quantity > 0) {
		WC()->cart->set_quantity($cart_item_key, $quantity, true);
	}

	WC_AJAX::get_refreshed_fragments();
});

add_action('wc_ajax_sweetmunchies_remove_cart_item', function (): void {
	check_ajax_referer('sweetmunchies_cart_action', 'nonce');

	$cart_item_key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';

	if ($cart_item_key) {
		WC()->cart->remove_cart_item($cart_item_key);
	}

	WC_AJAX::get_refreshed_fragments();
});
