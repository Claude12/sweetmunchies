<?php

declare(strict_types=1);

/**
 * Block: Shop by Occasion
 * ACF flexible content layout `shop_by_occasion` — a horizontally-scrolling
 * row of cards, each linking to a WooCommerce product category archive.
 * Rendered inside the have_rows()/the_row() loop in
 * sweetmunchies_render_flexible_content() (see inc/acf.php).
 *
 * The "Categories" field lets an editor pick specific categories to
 * feature (choices are restricted to categories with products — see the
 * acf/fields/taxonomy/query filter in inc/acf.php); left empty, it falls
 * back to showing every category that currently has products. Either way,
 * tiles always render in alphabetical order. Each category's icon comes
 * from its own "Icon" field on the category edit screen (see
 * acf-json/group_68f7c100n001.json, attached to the product_cat taxonomy).
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

$heading  = get_sub_field('heading');
$subtext  = get_sub_field('subtext');
$selected = get_sub_field('featured_categories');

if ($selected) {
    $categories = array_filter(array_map(
        fn ($term_id) => get_term($term_id, 'product_cat'),
        $selected
    ), fn ($term) => $term && !is_wp_error($term) && $term->count > 0);
} else {
    $categories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
    ]);
    $categories = is_wp_error($categories) ? [] : $categories;
}

$categories = array_values($categories);
usort($categories, fn ($a, $b) => strcasecmp($a->name, $b->name));
$bg_colors = ['pink-tint', 'green-tint', 'cream-alt'];

if (!$categories) {
    return;
}
?>

<section class="shop-by-occasion">
    <img class="shop-by-occasion__deco" src="<?php echo esc_url(get_template_directory_uri() . '/images/candy-icon.webp'); ?>" alt="" aria-hidden="true" />
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
                <?php foreach ($categories as $index => $category): ?>
                    <?php
                    $term_link = get_term_link($category);
                    if (is_wp_error($term_link)) {
                        continue;
                    }
                    $icon     = get_field('category_icon', $category);
                    $bg_color = $bg_colors[$index % count($bg_colors)];
                    ?>
                    <li>
                        <a href="<?php echo esc_url($term_link); ?>" class="shop-by-occasion__card shop-by-occasion__card--<?php echo esc_attr($bg_color); ?>">
                            <?php if ($icon): ?>
                                <span class="shop-by-occasion__icon" aria-hidden="true"><?php echo esc_html($icon); ?></span>
                            <?php endif; ?>
                            <span class="shop-by-occasion__name"><?php echo esc_html($category->name); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>
