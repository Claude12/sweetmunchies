<?php

declare(strict_types=1);

/**
 * Block: Instagram Strip
 * ACF flexible content layout `ig_strip` — a manually-managed photo gallery
 * (not a live Instagram feed/API — no plugin installs for this project) that
 * links out to a social profile (Instagram by default, but any platform
 * configured in Theme Settings → Socials can be chosen via the `platform`
 * field). Rendered inside the have_rows()/the_row() loop in
 * sweetmunchies_render_flexible_content() (see inc/acf.php).
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

$heading    = get_sub_field('heading');
$gallery    = get_sub_field('gallery');
$link_text  = get_sub_field('link_text');
$platform   = get_sub_field('platform') ?: 'instagram';
$socials    = get_field('socials', 'option') ?: [];
$social_url = $socials[$platform] ?? '';

if ($social_url && $platform === 'whatsapp') {
    $social_url = 'https://wa.me/' . $social_url;
}

if (!$gallery) {
    return;
}
?>

<section class="ig-strip">
    <div class="container">
        <?php if ($heading || ($social_url && $link_text)): ?>
            <div class="ig-strip__header" animate="fade-in">
                <?php if ($heading): ?>
                    <h2 class="ig-strip__heading"><?php echo esc_html($heading); ?></h2>
                <?php endif; ?>
                <?php if ($social_url && $link_text): ?>
                    <a href="<?php echo esc_url($social_url); ?>" class="ig-strip__link" target="_blank" rel="noopener noreferrer">
                        <?php echo esc_html($link_text); ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="ig-strip__grid">
            <?php foreach ($gallery as $image): ?>
                <?php
                // wp_get_attachment_image emits srcset. The grid's auto-fit
                // columns (see __grid) stretch tiles to fill leftover row
                // space when there are fewer photos than fit at the 130px
                // minimum — e.g. 4 photos in a 1180px container end up
                // ~286px wide, not 150px — so "medium" (300px) with a
                // 150px sizes hint was underselling the needed resolution
                // and coming out soft, especially on retina screens. "large"
                // plus a wider sizes hint gives enough headroom for that
                // worst-case stretch.
                $img_html = wp_get_attachment_image(
                    (int) $image['ID'],
                    'large',
                    false,
                    array(
                        'class'    => 'ig-strip__img',
                        'loading'  => 'lazy',
                        'decoding' => 'async',
                        'sizes'    => '(min-width: 768px) 300px, 45vw',
                    )
                );
                ?>
                <?php if ($social_url): ?>
                    <a href="<?php echo esc_url($social_url); ?>" class="ig-strip__frame" target="_blank" rel="noopener noreferrer">
                        <?php
                        // wp_kses_post() would strip srcset/sizes/decoding
                        // (not in its allowed-attributes list), defeating the
                        // point of this responsive markup — $img_html is
                        // trusted, WP-core-generated output, not user input.
                        echo $img_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?>
                    </a>
                <?php else: ?>
                    <div class="ig-strip__frame">
                        <?php
                        // wp_kses_post() would strip srcset/sizes/decoding
                        // (not in its allowed-attributes list), defeating the
                        // point of this responsive markup — $img_html is
                        // trusted, WP-core-generated output, not user input.
                        echo $img_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
