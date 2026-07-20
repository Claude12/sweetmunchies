<?php

declare(strict_types=1);

/**
 * Single product page — full custom override (the theme owns all WC markup,
 * see inc/woocommerce.php). Reads $product directly, no woocommerce hooks.
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

global $product;

if (!is_a($product, WC_Product::class) || !$product->is_visible()) {
    return;
}

$product_id     = $product->get_id();
$badge_label    = get_field('badge_label', $product_id);
$tagline        = trim(wp_strip_all_tags($product->get_short_description()));
$whats_inside   = get_field('whats_inside', $product_id);
$gift_price         = sweetmunchies_gift_message_price();
$gift_price_display = html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES)
    . rtrim(rtrim(number_format($gift_price, 2), '0'), '.');

// Products with no price set (e.g. fully custom/made-to-order boxes) are
// quote-on-request — WooCommerce marks a simple product non-purchasable when
// its price is empty, which is the only signal available (no dedicated ACF
// field for this). Skip the cart form entirely and let the customer describe
// what they want, then hand off straight to WhatsApp.
$is_poa           = !$product->is_purchasable();
$whatsapp_number  = $is_poa ? (get_field('socials', 'option')['whatsapp'] ?? '') : '';
$poa_default_text = $is_poa
    ? sprintf(
        /* translators: %s: product name */
        __("Hi Sweet Munchies! I'm interested in the %s — could you help me with pricing?\n\nIf everything above looks correct, please hit send and we'll get back to you with a quote!", 'sweetmunchies'),
        get_the_title()
    )
    : '';

// ACF option fields return null until the options page has been saved at
// least once in wp-admin (default_value only pre-fills the edit form, it's
// not a get_field() runtime fallback) — this PHP-level fallback keeps the
// tab populated with the same copy in the meantime.
$delivery_care_text = get_field('delivery_care_text', 'option')
    ?: 'Free delivery within Mutare CBD. Home delivery outside CBD from $2. Nationwide delivery available via courier or bus — transport cost covered by the customer.';

$payment_badges       = get_field('payment_badges', 'option');
$payment_badge_labels = $payment_badges ? array_filter(wp_list_pluck($payment_badges, 'label')) : [];
$payment_methods_text = $payment_badge_labels ? implode(' / ', $payment_badge_labels) : 'EcoCash / bank transfer / cash';

$main_image_id = $product->get_image_id();
$gallery_ids   = array_values(array_unique(array_filter(array_merge(
    [$main_image_id],
    $product->get_gallery_image_ids()
))));

$related_products = function_exists('sweetmunchies_get_related_products')
    ? sweetmunchies_get_related_products($product_id, 4)
    : [];
?>

<main id="primary" class="site-main product-page">
    <?php get_template_part('inc/template-parts/page-banner', null, ['hero' => false]); ?>

    <div class="container">
        <div class="product-page__layout">
            <div class="product-page__gallery">
                <div class="product-page__main-image">
                    <?php echo wp_kses_post($product->get_image('sweetmunchies_product_hero')); ?>
                </div>
                <?php if (count($gallery_ids) > 1): ?>
                    <div class="product-page__thumbs">
                        <?php foreach ($gallery_ids as $index => $image_id): ?>
                            <button
                                type="button"
                                class="product-page__thumb<?php echo $index === 0 ? ' is-active' : ''; ?>"
                                data-full-src="<?php echo esc_url(wp_get_attachment_image_url($image_id, 'sweetmunchies_product_hero')); ?>"
                            >
                                <?php echo wp_kses_post(wp_get_attachment_image($image_id, 'sweetmunchies_product_card')); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-page__details">
                <?php if ($badge_label): ?>
                    <span class="product-page__badge"><?php echo esc_html($badge_label); ?></span>
                <?php endif; ?>

                <h1 class="product-page__title"><?php echo esc_html(get_the_title()); ?></h1>

                <?php if ($tagline): ?>
                    <p class="product-page__tagline"><?php echo esc_html($tagline); ?></p>
                <?php endif; ?>

                <div class="product-page__price-row">
                    <?php if ($is_poa): ?>
                        <span class="product-page__price product-page__price--poa"><?php esc_html_e('Price on request', 'sweetmunchies'); ?></span>
                    <?php else: ?>
                        <span class="product-page__price"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($is_poa): ?>
                    <div
                        class="product-page__poa"
                        data-whatsapp-number="<?php echo esc_attr($whatsapp_number); ?>"
                        data-product-name="<?php echo esc_attr(get_the_title()); ?>"
                    >
                        <label class="product-page__poa-label" for="poa-message-<?php echo esc_attr((string) $product_id); ?>">
                            <?php esc_html_e("Tell us what you'd like — size, flavors, theme, budget — and we'll quote you on WhatsApp.", 'sweetmunchies'); ?>
                        </label>
                        <textarea
                            id="poa-message-<?php echo esc_attr((string) $product_id); ?>"
                            class="product-page__poa-message"
                            placeholder="<?php esc_attr_e('E.g. Birthday box for a 7 year old, budget around $15...', 'sweetmunchies'); ?>"
                        ></textarea>
                        <a
                            href="<?php echo esc_url('https://wa.me/' . rawurlencode($whatsapp_number) . '?text=' . rawurlencode($poa_default_text)); ?>"
                            class="product-page__poa-button"
                            target="_blank"
                            rel="noopener noreferrer"
                            data-poa-button
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.38 5.07L2 22l5.07-1.33A9.94 9.94 0 0 0 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2Zm0 18a7.9 7.9 0 0 1-4.03-1.1l-.29-.17-3 .79.8-2.93-.19-.3A7.93 7.93 0 1 1 12 20Z" /></svg>
                            <?php esc_html_e('Chat on WhatsApp', 'sweetmunchies'); ?>
                        </a>
                    </div>
                <?php else: ?>
                    <form
                        class="product-page__form"
                        method="post"
                        data-ajax-url="<?php echo esc_url(WC_AJAX::get_endpoint('add_to_cart')); ?>"
                        data-product-id="<?php echo esc_attr((string) $product_id); ?>"
                        data-base-price="<?php echo esc_attr((string) wc_get_price_to_display($product)); ?>"
                        data-gift-price="<?php echo esc_attr((string) $gift_price); ?>"
                    >
                        <label class="product-page__gift-toggle">
                            <input type="checkbox" name="gift_message_enabled" class="product-page__gift-checkbox" value="1" />
                            <?php
                            printf(
                                /* translators: %s: gift message surcharge, e.g. $2 */
                                esc_html__('Add a photo & message (+%s)', 'sweetmunchies'),
                                esc_html($gift_price_display)
                            );
                            ?>
                        </label>
                        <textarea
                            name="gift_message"
                            class="product-page__gift-message"
                            placeholder="<?php esc_attr_e('Write your gift message here...', 'sweetmunchies'); ?>"
                            maxlength="500"
                            hidden
                        ></textarea>
                        <p class="product-page__gift-hint" hidden><?php esc_html_e("You'll be able to send the photo on WhatsApp when you confirm your order — just add your message here for now.", 'sweetmunchies'); ?></p>

                        <div class="product-page__qty-row">
                            <div class="product-page__qty-stepper">
                                <button type="button" class="product-page__qty-btn" data-qty-decrease aria-label="<?php esc_attr_e('Decrease quantity', 'sweetmunchies'); ?>">&minus;</button>
                                <input type="number" name="quantity" class="product-page__qty-input" value="1" min="1" max="99" inputmode="numeric" />
                                <button type="button" class="product-page__qty-btn" data-qty-increase aria-label="<?php esc_attr_e('Increase quantity', 'sweetmunchies'); ?>">+</button>
                            </div>
                            <span class="product-page__total"><?php esc_html_e('Total:', 'sweetmunchies'); ?> <strong data-total-display><?php echo wp_kses_post(wc_price(wc_get_price_to_display($product))); ?></strong></span>
                        </div>

                        <button type="submit" class="product-page__add-to-cart">
                            <span class="product-page__add-to-cart-icon product-page__add-to-cart-icon--loading" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-dasharray="40" stroke-dashoffset="10" /></svg>
                            </span>
                            <span class="product-page__add-to-cart-icon product-page__add-to-cart-icon--added" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </span>
                            <span data-add-to-cart-label><?php esc_html_e('Add to Cart', 'sweetmunchies'); ?></span>
                        </button>
                    </form>
                <?php endif; ?>

                <div class="product-page__trust">
                    <span>&#128666; <?php esc_html_e('Free CBD delivery', 'sweetmunchies'); ?></span>
                    <span>&#128179; <?php echo esc_html($payment_methods_text); ?></span>
                    <span>&#127873; <?php esc_html_e('Custom gift wrap', 'sweetmunchies'); ?></span>
                </div>

                <div class="product-page__tabs">
                    <div class="product-page__tab-list" role="tablist">
                        <button type="button" class="product-page__tab is-active" role="tab" id="product-tab-description" aria-controls="product-tab-panel-description" aria-selected="true" data-tab="description"><?php esc_html_e('Description', 'sweetmunchies'); ?></button>
                        <?php if ($whats_inside): ?>
                            <button type="button" class="product-page__tab" role="tab" id="product-tab-inside" aria-controls="product-tab-panel-inside" aria-selected="false" data-tab="inside"><?php esc_html_e("What's Inside", 'sweetmunchies'); ?></button>
                        <?php endif; ?>
                        <button type="button" class="product-page__tab" role="tab" id="product-tab-delivery" aria-controls="product-tab-panel-delivery" aria-selected="false" data-tab="delivery"><?php esc_html_e('Delivery & Care', 'sweetmunchies'); ?></button>
                    </div>

                    <div class="product-page__tab-panel is-active" role="tabpanel" id="product-tab-panel-description" aria-labelledby="product-tab-description" data-tab-panel="description">
                        <?php echo wp_kses_post(wc_format_content($product->get_description())); ?>
                    </div>

                    <?php if ($whats_inside): ?>
                        <div class="product-page__tab-panel" role="tabpanel" id="product-tab-panel-inside" aria-labelledby="product-tab-inside" data-tab-panel="inside">
                            <ul class="product-page__inside-list">
                                <?php foreach ($whats_inside as $row): ?>
                                    <?php if (!empty($row['item'])): ?>
                                        <li><?php echo esc_html($row['item']); ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="product-page__tab-panel" role="tabpanel" id="product-tab-panel-delivery" aria-labelledby="product-tab-delivery" data-tab-panel="delivery">
                        <p><?php echo esc_html($delivery_care_text); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($related_products): ?>
            <div class="product-page__related">
                <h2 class="product-page__related-heading"><?php esc_html_e('You may also like', 'sweetmunchies'); ?></h2>
                <?php get_template_part('inc/template-parts/product-grid', null, ['products' => $related_products]); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$is_poa): ?>
        <div class="product-page__sticky-cta" data-sticky-cta hidden>
            <div>
                <div class="product-page__sticky-cta-total" data-sticky-total><?php echo wp_kses_post(wc_price(wc_get_price_to_display($product))); ?></div>
                <div class="product-page__sticky-cta-name"><?php echo esc_html(get_the_title()); ?></div>
            </div>
            <button type="button" class="product-page__sticky-cta-button" data-sticky-add-to-cart><?php esc_html_e('Add to Cart', 'sweetmunchies'); ?></button>
        </div>
    <?php endif; ?>
</main><!-- #main -->
