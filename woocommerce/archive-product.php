<?php

declare(strict_types=1);

/**
 * Shop archive + every product-category term archive — full custom markup.
 * Included via wc_get_template_part('archive', 'product') from the
 * is_shop()/is_product_taxonomy() branch in woocommerce.php (theme root),
 * the same way woocommerce_content() there pulls in
 * woocommerce/content-single-product.php for single products — get_header()/
 * get_footer() are already open by the time this runs, so this file only
 * owns the <main> markup. Reads the main $wp_query directly instead of the
 * woocommerce_shop_loop_* hook soup, matching page-cart.php and
 * content-single-product.php's "theme owns all markup" approach.
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

$shop_page_id = wc_get_page_id('shop');
$queried_term = is_product_taxonomy() ? get_queried_object() : null;

$categories = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
]);

global $wp_query;
$products = array_values(array_filter(array_map(
    fn ($post) => wc_get_product($post->ID),
    $wp_query->posts
)));
?>

<main id="primary" class="site-main shop-page">
    <?php get_template_part('inc/template-parts/page-banner', null, ['hero' => false]); ?>

    <div class="container">
        <img class="shop-page__deco" src="<?php echo esc_url(get_template_directory_uri() . '/images/candy-icon.webp'); ?>" alt="" aria-hidden="true" />

        <?php
        // The page-banner above runs in hero=false mode, which skips its <h1>
        // along with the hero image — so without this the Shop page and all 14
        // category archives ship no <h1> at all. Prefers the term's/page's ACF
        // banner_heading (the same field the hero would have used, so an editor
        // can set a keyword-led heading like "Birthday Gift Boxes in Mutare")
        // and falls back to the plain term/page title.
        $archive_heading = $queried_term
            ? (get_field('banner_heading', $queried_term) ?: $queried_term->name)
            : (get_field('banner_heading', $shop_page_id) ?: get_the_title($shop_page_id));
        ?>
        <h1 class="shop-page__heading"><?php echo esc_html($archive_heading); ?></h1>

        <?php if ($queried_term): ?>
            <?php $term_description = term_description($queried_term); ?>
            <?php if ($term_description): ?>
                <div class="shop-page__intro">
                    <?php echo wp_kses_post($term_description); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="shop-page__toolbar">
            <?php if ($categories): ?>
                <div class="shop-page__pills">
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="shop-page__pill<?php echo !$queried_term ? ' is-active' : ''; ?>">
                        <?php esc_html_e('All', 'sweetmunchies'); ?>
                    </a>
                    <?php foreach ($categories as $category): ?>
                        <?php
                        $term_link = get_term_link($category);
                        if (is_wp_error($term_link)) {
                            continue;
                        }
                        ?>
                        <a href="<?php echo esc_url($term_link); ?>" class="shop-page__pill<?php echo ($queried_term && $queried_term->term_id === $category->term_id) ? ' is-active' : ''; ?>">
                            <?php echo esc_html($category->name); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($products && function_exists('woocommerce_catalog_ordering')): ?>
                <div class="shop-page__sort">
                    <?php woocommerce_catalog_ordering(); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($products): ?>
            <?php get_template_part('inc/template-parts/product-grid', null, ['products' => $products, 'eager_first' => true]); ?>
            <?php woocommerce_pagination(); ?>
        <?php else: ?>
            <div class="shop-page__empty">
                <p><?php esc_html_e('No products in this category yet — check back soon!', 'sweetmunchies'); ?></p>
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="shop-page__empty-link"><?php esc_html_e('View all products', 'sweetmunchies'); ?></a>
            </div>
        <?php endif; ?>
    </div>

    <?php sweetmunchies_render_flexible_content($shop_page_id); ?>
</main><!-- #main -->
