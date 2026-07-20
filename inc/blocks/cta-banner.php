<?php

declare(strict_types=1);

/**
 * Block: CTA Banner
 * ACF flexible content layout `cta_banner` — a lean closing-CTA card with a
 * generic link button (title/url/target), kept separate from `whatsapp_cta`
 * since that block's semantics are hardcoded to a wa.me deep link. Rendered
 * inside the have_rows()/the_row() loop in
 * sweetmunchies_render_flexible_content() (see inc/acf.php).
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

$heading = get_sub_field('heading');
$subtext = get_sub_field('subtext');
$button  = get_sub_field('button');

if (!$heading || empty($button['url'])) {
    return;
}
?>

<section class="cta-banner">
    <div class="container">
        <div class="cta-banner__inner" animate="fade-in">
            <h2 class="cta-banner__heading"><?php echo esc_html($heading); ?></h2>
            <?php if ($subtext): ?>
                <p class="cta-banner__subtext"><?php echo esc_html($subtext); ?></p>
            <?php endif; ?>
            <a href="<?php echo esc_url($button['url']); ?>" class="cta-banner__button"
                target="<?php echo esc_attr($button['target'] ?: '_self'); ?>">
                <?php echo esc_html($button['title']); ?>
            </a>
        </div>
    </div>
</section>
