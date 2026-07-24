<?php

declare(strict_types=1);

/**
 * Block: Process Detail
 * ACF flexible content layout `process_detail` — a heading/intro plus a
 * repeater of icon-badged cards, each holding a full paragraph rather than
 * feature_grid's one-line subtext. Built for longer explainer copy (e.g.
 * "How gift delivery in Mutare works") that needs the site's card/badge
 * visual language instead of raw prose. Rendered inside the
 * have_rows()/the_row() loop in sweetmunchies_render_flexible_content()
 * (see inc/acf.php).
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

$heading          = get_sub_field('heading');
$intro            = get_sub_field('intro');
$background_color = get_sub_field('background_color');
$background_image = get_sub_field('background_image');
$items            = get_sub_field('items');

if (!$items) {
    return;
}

// A background image takes over from the flat color tint entirely — the
// photo sits behind the same brand-gradient overlay used by the page
// banner (see _page-banner.scss), and the heading/intro switch to white
// to stay readable on top of it. Cards stay white either way.
$section_class         = 'process-detail';
$background_image_url = '';
if ($background_image) {
    $section_class        .= ' process-detail--has-image';
    $background_image_url = $background_image['sizes']['large'] ?? $background_image['url'];
} elseif ($background_color) {
    $section_class .= ' process-detail--bg-' . esc_attr($background_color);
}
?>

<section class="<?php echo esc_attr($section_class); ?>"<?php if ($background_image_url): ?> style="background-image: url('<?php echo esc_url($background_image_url); ?>');"<?php endif; ?>>
    <?php if ($background_image): ?>
        <div class="process-detail__overlay"></div>
    <?php endif; ?>
    <div class="container process-detail__container">
        <?php if ($heading): ?>
            <h2 class="process-detail__heading"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>
        <?php if ($intro): ?>
            <p class="process-detail__intro"><?php echo esc_html($intro); ?></p>
        <?php endif; ?>

        <ul class="process-detail__list" animate="fade-in">
            <?php foreach ($items as $item): ?>
                <li class="process-detail__item">
                    <?php if (!empty($item['icon'])): ?>
                        <span class="process-detail__badge"><?php echo esc_html($item['icon']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($item['title'])): ?>
                        <h3 class="process-detail__item-title"><?php echo esc_html($item['title']); ?></h3>
                    <?php endif; ?>
                    <?php if (!empty($item['text'])): ?>
                        <p class="process-detail__item-text"><?php echo esc_html($item['text']); ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
