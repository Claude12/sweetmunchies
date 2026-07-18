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
$servings_short  = get_field('servings_short', $product->get_id());
$treats_count    = get_field('treats_count', $product->get_id());
$meta_parts      = array_filter([$servings_short, $treats_count]);
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
            <?php if ($meta_parts): ?>
                <p class="product-card__meta"><?php echo esc_html(implode(' · ', $meta_parts)); ?></p>
            <?php endif; ?>
        </div>
    </a>
    <div class="product-card__footer">
        <span class="product-card__price"><?php echo $product->get_price_html() ? wp_kses_post($product->get_price_html()) : esc_html__('POA', 'sweetmunchies'); ?></span>
        <div class="product-card__quick-add">
            <?php woocommerce_template_loop_add_to_cart(); ?>
        </div>
    </div>
</li>
