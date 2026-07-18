<?php

declare(strict_types=1);

/**
 * ProductCard quick-add button — overrides WooCommerce's default loop
 * add-to-cart markup (wp-content/plugins/woocommerce/templates/loop/
 * add-to-cart.php, v9.2.0) to inject idle/loading/added icon states.
 *
 * No custom JS: WooCommerce's own wc-add-to-cart.js (enabled via
 * woocommerce_enable_ajax_add_to_cart) toggles the .loading and .added
 * classes on this anchor during the AJAX request — the three icon spans
 * below are shown/hidden purely via CSS descendant selectors on those
 * classes (see assets/scss/components/_woocommerce.scss).
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

global $product;

// $args is passed in via wc_get_template()'s include-time scope (see
// woocommerce_template_loop_add_to_cart() in WC core) — defaulted here only
// so static analysis and any direct include of this file don't warn/notice.
$args = $args ?? array();

$aria_describedby = isset($args['aria-describedby_text'])
    ? sprintf('aria-describedby="woocommerce_loop_add_to_cart_link_describedby_%s"', esc_attr($product->get_id()))
    : '';

$icons = '<span class="product-card__quick-add-icon product-card__quick-add-icon--idle" aria-hidden="true">'
    . '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>'
    . '</span>'
    . '<span class="product-card__quick-add-icon product-card__quick-add-icon--loading" aria-hidden="true">'
    . '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-dasharray="40" stroke-dashoffset="10" /></svg>'
    . '</span>'
    . '<span class="product-card__quick-add-icon product-card__quick-add-icon--added" aria-hidden="true">'
    . '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>'
    . '</span>';

echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- all dynamic parts are individually escaped above; $icons is static, theme-authored markup.
    'woocommerce_loop_add_to_cart_link',
    sprintf(
        '<a href="%s" %s data-quantity="%s" class="%s" %s><span class="screen-reader-text">%s</span>%s</a>',
        esc_url($product->add_to_cart_url()),
        $aria_describedby,
        esc_attr(isset($args['quantity']) ? $args['quantity'] : 1),
        esc_attr(isset($args['class']) ? $args['class'] : 'button'),
        isset($args['attributes']) ? wc_implode_html_attributes($args['attributes']) : '',
        esc_html($product->add_to_cart_text()),
        $icons
    ),
    $product,
    $args
);
?>
<?php if (isset($args['aria-describedby_text'])): ?>
    <span id="woocommerce_loop_add_to_cart_link_describedby_<?php echo esc_attr($product->get_id()); ?>" class="screen-reader-text">
        <?php echo esc_html($args['aria-describedby_text']); ?>
    </span>
<?php endif; ?>
