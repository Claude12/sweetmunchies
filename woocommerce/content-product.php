<?php

declare(strict_types=1);

/**
 * ProductCard — overrides WooCommerce's default loop card markup
 * (wp-content/plugins/woocommerce/templates/content-product.php, v9.4.0)
 * with the theme's own BEM structure. Used everywhere WooCommerce renders a
 * product loop item: the homepage product grids (via
 * inc/template-parts/product-grid.php), the shop archive, and related/
 * up-sell product lists.
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

global $product;

if (!is_a($product, WC_Product::class) || !$product->is_visible()) {
    return;
}

$badge_label     = get_field('badge_label', $product->get_id());

// See inc/template-parts/product-grid.php — true only for the first card of
// an above-the-fold grid (currently just the shop/category archive), so that
// one image loads eagerly instead of lazily since it's likely the page's LCP
// element; every other card keeps the default lazy-loaded behavior.
$is_eager_card   = (bool) get_query_var('product_card_eager', false);

// One-tap "Order on WhatsApp" — a faster path than add-to-cart for shoppers
// who already know they want this exact box. Quote-on-request products
// (not purchasable) already route to their own product page WhatsApp quote
// flow via the "view product" arrow link above, so this is scoped to
// single-priced products only — variable products (size options) don't have
// one fixed price to quote from the card, so shoppers pick a size on the
// product page instead, where its own WhatsApp order link takes over.
// Message format matches the cart page's (see page-cart.php) so admin sees
// one consistent order shape either way.
$whatsapp_number = get_field('socials', 'option')['whatsapp'] ?? '';
$whatsapp_url    = '';

if ($whatsapp_number && $product->is_purchasable() && !$product->is_type('variable')) {
    $price_text = html_entity_decode(wp_strip_all_tags(wc_price($product->get_price())), ENT_QUOTES);
    $whatsapp_message = "Hi Sweet Munchies! I'd like to order:\n\n"
        . '- 1x ' . $product->get_name() . ' — ' . $price_text
        . "\n\nTotal: " . $price_text
        . "\n\nI'll share my delivery details here."
        . "\n\nIf everything above looks correct, please hit send to confirm your order!";

    $whatsapp_url = 'https://wa.me/' . rawurlencode($whatsapp_number) . '?text=' . rawurlencode($whatsapp_message);
}
?>
<li <?php wc_product_class('product-card', $product); ?>>
    <a href="<?php echo esc_url(get_permalink()); ?>" class="product-card__link">
        <div class="product-card__image">
            <?php
            echo wp_kses_post($product->get_image('sweetmunchies_product_card', $is_eager_card ? array(
                'loading'       => 'eager',
                'fetchpriority' => 'high',
            ) : array()));
            ?>
            <?php if ($badge_label): ?>
                <span class="product-card__badge"><?php echo esc_html($badge_label); ?></span>
            <?php endif; ?>
        </div>
        <div class="product-card__body">
            <h3 class="product-card__name"><?php echo esc_html(get_the_title()); ?></h3>
        </div>
    </a>
    <div class="product-card__footer">
        <span class="product-card__price">
            <?php if ($product->is_type('variable')): ?>
                <?php esc_html_e('From', 'sweetmunchies'); ?> <?php echo wp_kses_post(wc_price(wc_get_price_to_display($product))); ?>
            <?php else: ?>
                <?php echo $product->get_price_html() ? wp_kses_post($product->get_price_html()) : esc_html__('POA', 'sweetmunchies'); ?>
            <?php endif; ?>
        </span>
        <div class="product-card__actions">
            <?php if ($whatsapp_url): ?>
                <?php // esc_url() strips %0a/%0d (encoded line breaks) as an XSS/header-injection guard, which would mangle this multi-line message — esc_attr() is the correct escape here since $whatsapp_url is built entirely from trusted values (WC product data + the ACF-configured WhatsApp number), not raw user input. ?>
                <?php // Tooltip is revealed on hover/touch of the card (see .product-card:hover in _woocommerce.scss), not always visible. It's opacity-hidden rather than removed from the DOM, so it still gives the link its accessible name — no separate aria-label/title is needed. ?>
                <a href="<?php echo esc_attr($whatsapp_url); ?>" class="product-card__whatsapp-link" target="_blank" rel="noopener noreferrer">
                    <span class="product-card__whatsapp-tooltip"><?php esc_html_e('Order on WhatsApp', 'sweetmunchies'); ?></span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.38 5.07L2 22l5.07-1.33A9.94 9.94 0 0 0 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2Zm0 18a7.9 7.9 0 0 1-4.03-1.1l-.29-.17-3 .79.8-2.93-.19-.3A7.93 7.93 0 1 1 12 20Zm4.36-5.96c-.24-.12-1.42-.7-1.64-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.18-.71-.63-1.19-1.42-1.33-1.66-.14-.24-.01-.37.11-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.19-.46-.39-.4-.54-.4-.14 0-.3-.01-.46-.01-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.6 4.12 3.64.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28Z" />
                    </svg>
                </a>
            <?php endif; ?>
            <div class="product-card__quick-add">
                <?php woocommerce_template_loop_add_to_cart(); ?>
            </div>
        </div>
    </div>
</li>
