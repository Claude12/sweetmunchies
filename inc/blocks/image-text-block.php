<?php

declare(strict_types=1);

/**
 * Block: 50/50 Block
 * ACF flexible content layout `image_text_block` — two-column image/text
 * section with an optional background colour. Rendered inside the
 * have_rows()/the_row() loop in sweetmunchies_render_flexible_content()
 * (see inc/acf.php), so get_sub_field() reads from the current row.
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

$background_color = get_sub_field('background_color');
$left_type         = get_sub_field('left_type');
$left_image        = get_sub_field('left_image');
$left_text         = get_sub_field('left_text');
$right_type        = get_sub_field('right_type');
$right_image       = get_sub_field('right_image');
$right_text        = get_sub_field('right_text');
$eyebrow           = get_sub_field('eyebrow');
$cta               = get_sub_field('cta');
$stats             = get_sub_field('stats');
$img_loading       = (int) get_query_var('block_index', 1) === 0 ? 'eager' : 'lazy';
?>

<section class="image-text-block<?php echo $background_color ? ' image-text-block--bg-' . esc_attr($background_color) : ''; ?>">
    <div class="container">
        <div class="image-text-block__content" animate="slide-in-up">
            <div class="image-text-block__side">
                <?php if ($left_type === 'image' && $left_image): ?>
                    <?php // wp_get_attachment_image over a raw <img>: emits srcset/sizes so a 50% column doesn't download the full-size original. ?>
                    <?php echo wp_get_attachment_image((int) $left_image['ID'], 'large', false, array('loading' => $img_loading, 'decoding' => 'async')); ?>
                <?php elseif ($left_type === 'text' && $left_text): ?>
                    <?php if ($eyebrow): ?>
                        <p class="image-text-block__eyebrow"><?php echo esc_html($eyebrow); ?></p>
                    <?php endif; ?>
                    <div class="rte"><?php echo wp_kses_post($left_text); ?></div>
                    <?php if ($stats): ?>
                        <ul class="image-text-block__stats">
                            <?php foreach ($stats as $stat): ?>
                                <li class="image-text-block__stat">
                                    <span class="image-text-block__stat-number"><?php echo esc_html($stat['number']); ?></span>
                                    <span class="image-text-block__stat-label"><?php echo esc_html($stat['label']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if (!empty($cta['url'])): ?>
                        <a href="<?php echo esc_url($cta['url']); ?>" class="image-text-block__cta"
                            target="<?php echo esc_attr($cta['target'] ?: '_self'); ?>">
                            <?php echo esc_html($cta['title']); ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="image-text-block__side">
                <?php if ($right_type === 'image' && $right_image): ?>
                    <?php echo wp_get_attachment_image((int) $right_image['ID'], 'large', false, array('loading' => $img_loading, 'decoding' => 'async')); ?>
                <?php elseif ($right_type === 'text' && $right_text): ?>
                    <?php if ($eyebrow): ?>
                        <p class="image-text-block__eyebrow"><?php echo esc_html($eyebrow); ?></p>
                    <?php endif; ?>
                    <div class="rte"><?php echo wp_kses_post($right_text); ?></div>
                    <?php if ($stats): ?>
                        <ul class="image-text-block__stats">
                            <?php foreach ($stats as $stat): ?>
                                <li class="image-text-block__stat">
                                    <span class="image-text-block__stat-number"><?php echo esc_html($stat['number']); ?></span>
                                    <span class="image-text-block__stat-label"><?php echo esc_html($stat['label']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if (!empty($cta['url'])): ?>
                        <a href="<?php echo esc_url($cta['url']); ?>" class="image-text-block__cta"
                            target="<?php echo esc_attr($cta['target'] ?: '_self'); ?>">
                            <?php echo esc_html($cta['title']); ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
