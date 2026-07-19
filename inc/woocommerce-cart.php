<?php

declare(strict_types=1);

/**
 * Cart-item-data hooks for the single product page's "Add a photo & message"
 * add-on (+$2 per unit). See woocommerce/content-single-product.php for the
 * form fields and assets/js/lib/product-add-to-cart.js for the AJAX submit.
 *
 * @package sweetmunchies
 */

// phpcs:disable WordPress.Security.NonceVerification.Missing -- deliberate for this file: the add-to-cart capture and the wc_ajax cart endpoints mirror WooCommerce core's own nonce-less add_to_cart/remove_from_cart AJAX actions. All mutations are scoped to the visitor's own session cart, and every input is sanitized on read.

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

	$message = isset($_POST['gift_message']) ? sanitize_textarea_field(wp_unslash($_POST['gift_message'])) : '';

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

/**
 * Cart-page quantity/remove AJAX actions (see page-cart.php and
 * assets/js/lib/cart-page.js). No built-in WC_AJAX action updates quantity,
 * so this mirrors the endpoint convention WC_AJAX::add_to_cart/remove_from_cart
 * already use — no nonce, ends with the same get_refreshed_fragments() JSON
 * shape so the header cart-count badge stays in sync via wc-cart-fragments.js.
 */
add_action('wc_ajax_sweetmunchies_update_cart_item', function (): void {
	$cart_item_key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';
	$quantity      = isset($_POST['quantity']) ? absint($_POST['quantity']) : 0;

	if ($cart_item_key && $quantity > 0) {
		WC()->cart->set_quantity($cart_item_key, $quantity, true);
	}

	WC_AJAX::get_refreshed_fragments();
});

add_action('wc_ajax_sweetmunchies_remove_cart_item', function (): void {
	$cart_item_key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';

	if ($cart_item_key) {
		WC()->cart->remove_cart_item($cart_item_key);
	}

	WC_AJAX::get_refreshed_fragments();
});
