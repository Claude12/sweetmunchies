<?php

declare(strict_types=1);

/**
 * Block: WhatsApp CTA
 * ACF flexible content layout `whatsapp_cta` — a promo banner with a single
 * button that deep-links straight to a WhatsApp chat (no capture form / no
 * backend — replaces the design file's non-functional "newsletter" concept).
 * Rendered inside the have_rows()/the_row() loop in
 * sweetmunchies_render_flexible_content() (see inc/acf.php).
 *
 * @package sweetmunchies
 */

$heading      = get_sub_field('heading');
$subtext      = get_sub_field('subtext');
$button_text  = get_sub_field('button_text');
$whatsapp     = get_field('socials', 'option')['whatsapp'] ?? '';

if (!$whatsapp || !$button_text) {
    return;
}
?>

<section class="whatsapp-cta">
    <div class="container">
        <div class="whatsapp-cta__inner" animate="fade-in">
            <div class="whatsapp-cta__text">
                <?php if ($heading): ?>
                    <h2 class="whatsapp-cta__heading"><?php echo esc_html($heading); ?></h2>
                <?php endif; ?>
                <?php if ($subtext): ?>
                    <p class="whatsapp-cta__subtext"><?php echo esc_html($subtext); ?></p>
                <?php endif; ?>
            </div>
            <a href="https://wa.me/<?php echo esc_attr($whatsapp); ?>" class="whatsapp-cta__button" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html($button_text); ?>
            </a>
        </div>
    </div>
</section>
