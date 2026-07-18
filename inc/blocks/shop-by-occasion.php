<?php

declare(strict_types=1);

/**
 * Block: Shop by Occasion
 * ACF flexible content layout `shop_by_occasion` — a horizontally-scrolling
 * row of cards, each linking to a WooCommerce product category archive.
 * Rendered inside the have_rows()/the_row() loop in
 * sweetmunchies_render_flexible_content() (see inc/acf.php).
 *
 * @package sweetmunchies
 */

$heading   = get_sub_field('heading');
$subtext   = get_sub_field('subtext');
$occasions = get_sub_field('occasions');

if (!$occasions) {
    return;
}
?>

<section class="shop-by-occasion">
    <div class="container">
        <div class="shop-by-occasion__header" animate="fade-in">
            <?php if ($heading): ?>
                <h2 class="shop-by-occasion__heading"><?php echo esc_html($heading); ?></h2>
            <?php endif; ?>
            <?php if ($subtext): ?>
                <p class="shop-by-occasion__subtext"><?php echo esc_html($subtext); ?></p>
            <?php endif; ?>
        </div>

        <div class="shop-by-occasion__scroll">
            <ul class="shop-by-occasion__list">
                <?php foreach ($occasions as $occasion): ?>
                    <?php
                    $term = !empty($occasion['term']) ? get_term($occasion['term'], 'product_cat') : null;
                    if (!$term || is_wp_error($term)) continue;
                    $bg_color = $occasion['bg_color'] ?: 'pink-tint';
                    ?>
                    <li>
                        <a href="<?php echo esc_url(get_term_link($term)); ?>" class="shop-by-occasion__card"
                            style="background-color: var(--color-<?php echo esc_attr($bg_color); ?>);">
                            <?php if (!empty($occasion['icon'])): ?>
                                <span class="shop-by-occasion__icon" aria-hidden="true"><?php echo esc_html($occasion['icon']); ?></span>
                            <?php endif; ?>
                            <span class="shop-by-occasion__name"><?php echo esc_html($term->name); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>
