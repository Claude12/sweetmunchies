<?php

/**
 * ACF: Flexible Content > Layouts > Image/Text Block ("50/50 Block" in ACF UI)
 *
 * @package sweetmunchies
 */

$background_color = $section['background_color'];
$left_type        = $section['left_type'];
$img_loading      = get_query_var('block_index', 1) === 0 ? 'eager' : 'lazy';
$left_image       = $section['left_image'];
$left_text        = $section['left_text'];
$right_type       = $section['right_type'];
$right_image      = $section['right_image'];
$right_text       = $section['right_text'];
?>

<section class="image-text-block"<?php echo $background_color ? ' style="background-color: ' . esc_attr($background_color) . ';"' : ''; ?>>
    <div class="container">
        <div class="image-text-block__content" animate="slide-in-up">
            <div class="image-text-block__side">
                <?php if ($left_type === 'image' && $left_image): ?>
                    <img
                        src="<?php echo esc_url($left_image['url']); ?>"
                        alt="<?php echo esc_attr($left_image['alt']); ?>"
                        width="<?php echo esc_attr($left_image['width']); ?>"
                        height="<?php echo esc_attr($left_image['height']); ?>"
                        loading="<?php echo esc_attr($img_loading); ?>"
                        decoding="async" />
                <?php elseif ($left_type === 'text' && $left_text): ?>
                    <div class="rte"><?php echo wp_kses_post($left_text); ?></div>
                <?php endif; ?>
            </div>
            <div class="image-text-block__side">
                <?php if ($right_type === 'image' && $right_image): ?>
                    <img
                        src="<?php echo esc_url($right_image['url']); ?>"
                        alt="<?php echo esc_attr($right_image['alt']); ?>"
                        width="<?php echo esc_attr($right_image['width']); ?>"
                        height="<?php echo esc_attr($right_image['height']); ?>"
                        loading="<?php echo esc_attr($img_loading); ?>"
                        decoding="async" />
                <?php elseif ($right_type === 'text' && $right_text): ?>
                    <div class="rte"><?php echo wp_kses_post($right_text); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
