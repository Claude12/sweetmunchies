<?php

declare(strict_types=1);

/**
 * WooCommerce cart page — full custom override (WordPress's template
 * hierarchy auto-selects this for the "cart" page slug, ahead of page.php).
 * The cart page normally renders the [woocommerce/cart] block via
 * the_content(), but page.php only renders the content_sections ACF field —
 * so without this file the cart page would render blank. Reads WC()->cart
 * directly, same bespoke-markup approach as woocommerce/content-single-product.php.
 * There is no checkout flow on this site — orders are sent to WhatsApp
 * instead (see assets/js/lib/cart-page.js).
 *
 * @package sweetmunchies
 */

get_header();

$cart = WC()->cart;

// The gift-message surcharge (see inc/woocommerce-cart.php) is applied to
// $cart_item['data'] at runtime via woocommerce_before_calculate_totals, but
// that mutation isn't persisted to the session — it only takes effect within
// whichever request last ran calculate_totals() (normally the add-to-cart
// call itself). A plain page load of this template never triggers it again,
// so prices read below would silently drop the surcharge without this call.
if ($cart) {
    $cart->calculate_totals();
}

$items = $cart ? $cart->get_cart() : array();
?>

<main id="primary" class="site-main cart-page">
	<?php get_template_part('inc/template-parts/page-banner', null, ['hero' => false]); ?>

	<div class="container">
		<?php if (! $items): ?>
			<div class="cart-page__empty">
				<h1 class="cart-page__heading">Your Cart</h1>
				<p class="cart-page__subtext"><?php esc_html_e("Your cart is empty — let's fix that.", 'sweetmunchies'); ?></p>
				<a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="cart-page__continue cart-page__continue--solid"><?php esc_html_e('Continue Shopping', 'sweetmunchies'); ?></a>
			</div>
		<?php else: ?>
			<?php
			$whatsapp              = get_field('socials', 'option')['whatsapp'] ?? '';
			$payment_badges        = get_field('payment_badges', 'option');
			$payment_badge_labels  = $payment_badges ? array_filter(wp_list_pluck($payment_badges, 'label')) : array();
			$payment_methods_text  = $payment_badge_labels ? implode(', ', $payment_badge_labels) : 'EcoCash, bank transfer, or cash on delivery';
			$lines    = array();
			$total    = 0.0;

			foreach ($items as $cart_item) {
				$product   = $cart_item['data'];
				$qty       = (int) $cart_item['quantity'];
				$unit      = (float) $product->get_price();
				$line_total = $unit * $qty;
				$total     += $line_total;

				$line = $qty . 'x ' . $product->get_name() . ' — ' . wp_strip_all_tags(wc_price($line_total));
				if (! empty($cart_item['gift_message'])) {
					$line .= ' (+ photo & message: "' . $cart_item['gift_message'] . '")';
				}
				$lines[] = $line;
			}

			$message = "Hi Sweet Munchies! I'd like to order:\n\n"
				. '- ' . implode("\n- ", $lines)
				. "\n\nTotal: " . wp_strip_all_tags(wc_price($total))
				. "\n\nI'll share my delivery details and the photo (if any) here."
				. "\n\nIf everything above looks correct, please hit send to confirm your order!";

			$whatsapp_url = $whatsapp ? 'https://wa.me/' . rawurlencode($whatsapp) . '?text=' . rawurlencode($message) : '';
			?>

			<h1 class="cart-page__heading"><?php esc_html_e('Your Cart', 'sweetmunchies'); ?></h1>
			<p class="cart-page__subtext"><?php esc_html_e("Review your boxes, then send your order straight to us on WhatsApp.", 'sweetmunchies'); ?></p>

			<div class="cart-page__items">
				<?php foreach ($items as $cart_item_key => $cart_item): ?>
					<?php
					$product = $cart_item['data'];
					$unit    = (float) $product->get_price();
					?>
					<div class="cart-page__item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" data-unit-price="<?php echo esc_attr((string) $unit); ?>" data-gift-message="<?php echo esc_attr($cart_item['gift_message'] ?? ''); ?>">
						<div class="cart-page__item-image"><?php echo wp_kses_post($product->get_image('sweetmunchies_product_card')); ?></div>

						<div class="cart-page__item-body">
							<div class="cart-page__item-top">
								<a href="<?php echo esc_url(get_permalink($cart_item['product_id'])); ?>" class="cart-page__item-name"><?php echo esc_html($product->get_name()); ?></a>
								<button type="button" class="cart-page__item-remove" data-remove-item aria-label="<?php esc_attr_e('Remove item', 'sweetmunchies'); ?>">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
								</button>
							</div>

							<?php if (! empty($cart_item['gift_message'])): ?>
								<span class="cart-page__item-tag">+ <?php esc_html_e('Photo & message', 'sweetmunchies'); ?></span>
							<?php endif; ?>

							<div class="cart-page__item-bottom">
								<div class="cart-page__qty-stepper">
									<button type="button" class="cart-page__qty-btn" data-qty-decrease aria-label="<?php esc_attr_e('Decrease quantity', 'sweetmunchies'); ?>">&minus;</button>
									<input type="number" class="cart-page__qty-input" value="<?php echo esc_attr((string) $cart_item['quantity']); ?>" min="1" max="99" inputmode="numeric" />
									<button type="button" class="cart-page__qty-btn" data-qty-increase aria-label="<?php esc_attr_e('Increase quantity', 'sweetmunchies'); ?>">+</button>
								</div>
								<span class="cart-page__item-price" data-line-total><?php echo wp_kses_post(wc_price($unit * (int) $cart_item['quantity'])); ?></span>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="cart-page__totals">
				<div class="cart-page__totals-row">
					<span><?php esc_html_e('Subtotal', 'sweetmunchies'); ?></span>
					<span data-cart-subtotal><?php echo wp_kses_post(wc_price($total)); ?></span>
				</div>
				<div class="cart-page__totals-row cart-page__totals-row--total">
					<span><?php esc_html_e('Total', 'sweetmunchies'); ?></span>
					<span data-cart-total><?php echo wp_kses_post(wc_price($total)); ?></span>
				</div>
			</div>

			<div class="cart-page__whatsapp" data-ajax-update-url="<?php echo esc_url(WC_AJAX::get_endpoint('sweetmunchies_update_cart_item')); ?>" data-ajax-remove-url="<?php echo esc_url(WC_AJAX::get_endpoint('sweetmunchies_remove_cart_item')); ?>" data-whatsapp-number="<?php echo esc_attr($whatsapp); ?>">
				<span class="cart-page__whatsapp-icon">
					<svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.38 5.07L2 22l5.07-1.33A9.94 9.94 0 0 0 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2Zm0 18a7.9 7.9 0 0 1-4.03-1.1l-.29-.17-3 .79.8-2.93-.19-.3A7.93 7.93 0 1 1 12 20Zm4.36-5.96c-.24-.12-1.42-.7-1.64-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.18-.71-.63-1.19-1.42-1.33-1.66-.14-.24-.01-.37.11-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.19-.46-.39-.4-.54-.4-.14 0-.3-.01-.46-.01-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.6 4.12 3.64.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28Z"/></svg>
				</span>
				<h2 class="cart-page__whatsapp-heading"><?php esc_html_e('Order via WhatsApp', 'sweetmunchies'); ?></h2>
				<p class="cart-page__whatsapp-text">
					<?php
					printf(
						/* translators: %s: payment methods list, e.g. "EcoCash, bank transfer, or cash on delivery" */
						esc_html__("Send us your cart and we'll confirm your delivery time and payment method — %s.", 'sweetmunchies'),
						esc_html($payment_methods_text)
					);
					?>
				</p>
				<?php // No anchor without a configured number — an href="" link would just reload the page. ?>
				<?php if ($whatsapp_url): ?>
					<a href="<?php echo esc_url($whatsapp_url); ?>" class="cart-page__whatsapp-button" data-whatsapp-link target="_blank" rel="noopener noreferrer">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.38 5.07L2 22l5.07-1.33A9.94 9.94 0 0 0 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2Zm0 18a7.9 7.9 0 0 1-4.03-1.1l-.29-.17-3 .79.8-2.93-.19-.3A7.93 7.93 0 1 1 12 20Zm4.36-5.96c-.24-.12-1.42-.7-1.64-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.18-.71-.63-1.19-1.42-1.33-1.66-.14-.24-.01-.37.11-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.19-.46-.39-.4-.54-.4-.14 0-.3-.01-.46-.01-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.6 4.12 3.64.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28Z"/></svg>
						<?php esc_html_e('Order on WhatsApp', 'sweetmunchies'); ?>
					</a>
				<?php else: ?>
					<p class="cart-page__whatsapp-text"><?php esc_html_e('WhatsApp ordering is temporarily unavailable — please contact us directly to place your order.', 'sweetmunchies'); ?></p>
				<?php endif; ?>
				<p class="cart-page__whatsapp-note"><?php esc_html_e('Full online checkout coming soon — for now every order is confirmed personally via WhatsApp.', 'sweetmunchies'); ?></p>
			</div>

			<a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="cart-page__continue cart-page__continue--outline">&larr; <?php esc_html_e('Continue Shopping', 'sweetmunchies'); ?></a>
		<?php endif; ?>
	</div>
</main><!-- #main -->

<?php
get_footer();
