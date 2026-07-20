<?php

declare(strict_types=1);

/**
 * Block: Feature Grid
 * ACF flexible content layout `feature_grid` — a consolidated block reused
 * for both "What sets us apart" (icon style, no card) and "How it works"
 * (numbered style, white cards) via the `style` toggle, rather than two
 * near-duplicate layouts. Rendered inside the have_rows()/the_row() loop in
 * sweetmunchies_render_flexible_content() (see inc/acf.php).
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

$heading           = get_sub_field('heading');
$background_color  = get_sub_field('background_color');
$style              = get_sub_field('style') ?: 'icon';
$items              = get_sub_field('items');

if (!$items) {
    return;
}
?>

<section class="feature-grid feature-grid--<?php echo esc_attr($style); ?><?php echo $background_color ? ' feature-grid--bg-' . esc_attr($background_color) : ''; ?>">
    <?php if ($style === 'numbered'): ?>
        <img class="feature-grid__deco" src="<?php echo esc_url(get_template_directory_uri() . '/images/candy-icon.webp'); ?>" alt="" aria-hidden="true" />
    <?php endif; ?>
    <div class="container">
        <?php if ($heading): ?>
            <h2 class="feature-grid__heading"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <ul class="feature-grid__list">
            <?php foreach ($items as $index => $item): ?>
                <li class="feature-grid__item">
                    <span class="feature-grid__badge">
                        <?php echo $style === 'numbered' ? esc_html((string) ($index + 1)) : esc_html($item['icon']); ?>
                    </span>
                    <?php if (!empty($item['title'])): ?>
                        <h3 class="feature-grid__item-title"><?php echo esc_html($item['title']); ?></h3>
                    <?php endif; ?>
                    <?php if (!empty($item['subtext'])): ?>
                        <p class="feature-grid__item-subtext"><?php echo esc_html($item['subtext']); ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
