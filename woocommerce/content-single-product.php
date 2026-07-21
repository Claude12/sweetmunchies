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
$whatsapp_number  = get_field('socials', 'option')['whatsapp'] ?? '';

// Variable products (size options — see wp-content/themes/sweetmunchies's
// product-conversion script) show a size picker instead of one fixed price;
// the price row reads "From $X" (lowest variation price) until a size is
// picked, and both CTAs stay disabled until then so an order can't go out
// without a size attached.
$is_variable = $product->is_type('variable');
$size_variations = [];

if ($is_variable) {
    foreach ($product->get_available_variations() as $variation_data) {
        $size_slug  = $variation_data['attributes']['attribute_pa_size'] ?? '';
        $size_term  = $size_slug ? get_term_by('slug', $size_slug, 'pa_size') : false;

        $size_variations[] = [
            'variation_id' => $variation_data['variation_id'],
            'label'        => $size_term ? $size_term->name : ucfirst($size_slug),
            'price'        => $variation_data['display_price'],
        ];
    }
}
$poa_default_text = $is_poa
    ? sprintf(
        /* translators: %s: product name */
        __("Hi Sweet Munchies! I'm interested in the %s — could you help me with pricing?\n\nIf everything above looks correct, please hit send and we'll get back to you with a quote!", 'sweetmunchies'),
        get_the_title()
    )
    : '';

// One-tap "Order on WhatsApp" for regular priced products — mirrors the
// product-card quick-order link (see woocommerce/content-product.php),
// pre-filled for qty 1/no gift message; product-add-to-cart.js keeps the
// href in sync as the shopper changes quantity or adds a gift message.
$whatsapp_order_url = '';

if (!$is_poa && $whatsapp_number) {
    $price_text = html_entity_decode(wp_strip_all_tags(wc_price(wc_get_price_to_display($product))), ENT_QUOTES);
    $whatsapp_order_message = "Hi Sweet Munchies! I'd like to order:\n\n"
        . '- 1x ' . get_the_title() . ' — ' . $price_text
        . "\n\nTotal: " . $price_text
        . "\n\nI'll share my delivery details and the photo (if any) here."
        . "\n\nIf everything above looks correct, please hit send to confirm your order!";

    $whatsapp_order_url = 'https://wa.me/' . rawurlencode($whatsapp_number) . '?text=' . rawurlencode($whatsapp_order_message);
}

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
                    <?php elseif ($is_variable): ?>
                        <span class="product-page__price" data-price-display>
                            <?php esc_html_e('From', 'sweetmunchies'); ?> <?php echo wp_kses_post(wc_price(wc_get_price_to_display($product))); ?>
                        </span>
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
                        <?php if ($is_variable): ?>
                            <div class="product-page__size-picker" role="radiogroup" aria-label="<?php esc_attr_e('Size', 'sweetmunchies'); ?>">
                                <?php foreach ($size_variations as $variation): ?>
                                    <button
                                        type="button"
                                        class="product-page__size-option"
                                        data-variation-id="<?php echo esc_attr((string) $variation['variation_id']); ?>"
                                        data-price="<?php echo esc_attr((string) $variation['price']); ?>"
                                        data-size-label="<?php echo esc_attr($variation['label']); ?>"
                                        aria-pressed="false"
                                    >
                                        <?php echo esc_html($variation['label']); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <p class="product-page__size-hint" data-size-hint><?php esc_html_e('Please select a size to continue.', 'sweetmunchies'); ?></p>
                        <?php endif; ?>

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
                                <input type="number" name="quantity" class="product-page__qty-input" value="1" min="1" max="99" inputmode="numeric" aria-label="<?php esc_attr_e('Quantity', 'sweetmunchies'); ?>" />
                                <button type="button" class="product-page__qty-btn" data-qty-increase aria-label="<?php esc_attr_e('Increase quantity', 'sweetmunchies'); ?>">+</button>
                            </div>
                            <span class="product-page__total"><?php esc_html_e('Total:', 'sweetmunchies'); ?> <strong data-total-display><?php echo wp_kses_post(wc_price(wc_get_price_to_display($product))); ?></strong></span>
                        </div>

                        <button type="submit" class="product-page__add-to-cart" <?php echo $is_variable ? 'disabled' : ''; ?>>
                            <span class="product-page__add-to-cart-icon product-page__add-to-cart-icon--loading" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-dasharray="40" stroke-dashoffset="10" /></svg>
                            </span>
                            <span class="product-page__add-to-cart-icon product-page__add-to-cart-icon--added" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </span>
                            <span data-add-to-cart-label><?php esc_html_e('Add to Cart', 'sweetmunchies'); ?></span>
                        </button>

                        <?php if ($whatsapp_order_url): ?>
                            <a
                                href="<?php echo esc_attr($whatsapp_order_url); ?>"
                                class="product-page__whatsapp-order<?php echo $is_variable ? ' is-disabled' : ''; ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                data-whatsapp-order
                                data-whatsapp-number="<?php echo esc_attr($whatsapp_number); ?>"
                                data-product-name="<?php echo esc_attr(get_the_title()); ?>"
                                <?php echo $is_variable ? 'aria-disabled="true" tabindex="-1"' : ''; ?>
                            >
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.38 5.07L2 22l5.07-1.33A9.94 9.94 0 0 0 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2Zm0 18a7.9 7.9 0 0 1-4.03-1.1l-.29-.17-3 .79.8-2.93-.19-.3A7.93 7.93 0 1 1 12 20Zm4.36-5.96c-.24-.12-1.42-.7-1.64-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.18-.71-.63-1.19-1.42-1.33-1.66-.14-.24-.01-.37.11-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.19-.46-.39-.4-.54-.4-.14 0-.3-.01-.46-.01-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.6 4.12 3.64.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28Z" /></svg>
                                <?php esc_html_e('Order on WhatsApp', 'sweetmunchies'); ?>
                            </a>
                        <?php endif; ?>
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
                <img class="product-page__deco" src="<?php echo esc_url(get_template_directory_uri() . '/images/candy-icon.webp'); ?>" alt="" aria-hidden="true" />
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
            <button type="button" class="product-page__sticky-cta-button" data-sticky-add-to-cart <?php echo $is_variable ? 'disabled' : ''; ?>><?php esc_html_e('Add to Cart', 'sweetmunchies'); ?></button>
        </div>
    <?php endif; ?>
</main><!-- #main -->
