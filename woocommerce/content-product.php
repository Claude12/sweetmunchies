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

// One-tap "Order on WhatsApp" — a faster path than add-to-cart for shoppers
// who already know they want this exact box. Quote-on-request products
// (not purchasable) already route to their own product page WhatsApp quote
// flow via the "view product" arrow link above, so this is scoped to
// regular priced products only. Message format matches the cart page's
// (see page-cart.php) so admin sees one consistent order shape either way.
$whatsapp_number = get_field('socials', 'option')['whatsapp'] ?? '';
$whatsapp_url    = '';

if ($whatsapp_number && $product->is_purchasable()) {
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
            <?php echo wp_kses_post($product->get_image('sweetmunchies_product_card')); ?>
            <?php if ($badge_label): ?>
                <span class="product-card__badge"><?php echo esc_html($badge_label); ?></span>
            <?php endif; ?>
        </div>
        <div class="product-card__body">
            <h3 class="product-card__name"><?php echo esc_html(get_the_title()); ?></h3>
        </div>
    </a>
    <div class="product-card__footer">
        <span class="product-card__price"><?php echo $product->get_price_html() ? wp_kses_post($product->get_price_html()) : esc_html__('POA', 'sweetmunchies'); ?></span>
        <div class="product-card__actions">
            <?php if ($whatsapp_url): ?>
                <?php // esc_url() strips %0a/%0d (encoded line breaks) as an XSS/header-injection guard, which would mangle this multi-line message — esc_attr() is the correct escape here since $whatsapp_url is built entirely from trusted values (WC product data + the ACF-configured WhatsApp number), not raw user input. ?>
                <?php // Tooltip text is always visible, not hover-only — hover never fires on mobile, and mobile is where discoverability matters most here. It also gives the link its accessible name, so no separate aria-label/title is needed. ?>
                <a href="<?php echo esc_attr($whatsapp_url); ?>" class="product-card__whatsapp-link" target="_blank" rel="noopener noreferrer">
                    <span class="product-card__whatsapp-tooltip"><?php esc_html_e('Order on WhatsApp', 'sweetmunchies'); ?></span>
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/images/whatsapp-icon.svg'); ?>" alt="" width="30" height="30" decoding="async" />
                </a>
            <?php endif; ?>
            <div class="product-card__quick-add">
                <?php woocommerce_template_loop_add_to_cart(); ?>
            </div>
        </div>
    </div>
</li>
