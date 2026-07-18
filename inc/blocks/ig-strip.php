<?php

declare(strict_types=1);

/**
 * Block: Instagram Strip
 * ACF flexible content layout `ig_strip` — a manually-managed photo gallery
 * (not a live Instagram feed/API — no plugin installs for this project) that
 * links out to the real Instagram profile. Rendered inside the
 * have_rows()/the_row() loop in sweetmunchies_render_flexible_content()
 * (see inc/acf.php).
 *
 * @package sweetmunchies
 */

$heading    = get_sub_field('heading');
$gallery    = get_sub_field('gallery');
$link_text  = get_sub_field('link_text');
$ig_url     = get_field('socials', 'option')['instagram'] ?? '';

if (!$gallery) {
    return;
}
?>

<section class="ig-strip">
    <div class="container">
        <div class="ig-strip__header" animate="fade-in">
            <?php if ($heading): ?>
                <h2 class="ig-strip__heading"><?php echo esc_html($heading); ?></h2>
            <?php endif; ?>
            <?php if ($ig_url && $link_text): ?>
                <a href="<?php echo esc_url($ig_url); ?>" class="ig-strip__link" target="_blank" rel="noopener noreferrer">
                    <?php echo esc_html($link_text); ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="ig-strip__grid">
            <?php foreach ($gallery as $image): ?>
                <div class="ig-strip__frame">
                    <img
                        src="<?php echo esc_url($image['sizes']['medium'] ?? $image['url']); ?>"
                        alt="<?php echo esc_attr($image['alt']); ?>"
                        width="<?php echo esc_attr($image['width']); ?>"
                        height="<?php echo esc_attr($image['height']); ?>"
                        loading="lazy"
                        decoding="async"
                        class="ig-strip__img" />
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
