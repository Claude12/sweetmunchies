<?php

declare(strict_types=1);

/**
 * Block: Contact Info
 * ACF flexible content layout `contact_info` — a two-column section with
 * "Reach us directly" / "Delivery" cards (sourced from the Theme Settings
 * options page) on the left, and an embedded WPForms form on the right.
 * Rendered inside the have_rows()/the_row() loop in
 * sweetmunchies_render_flexible_content() (see inc/acf.php).
 *
 * @package sweetmunchies
 */

$delivery_text = get_sub_field('delivery_text');
$form_id       = (int) get_sub_field('form_id');
$contact_info  = get_field('contact_info', 'option') ?: array();
$whatsapp      = get_field('socials', 'option')['whatsapp'] ?? '';

$phone_display = $contact_info['phone_display'] ?? '';
$email         = $contact_info['email'] ?? '';
$location      = $contact_info['location'] ?? '';
?>

<section class="contact-info">
    <div class="container contact-info__grid">
        <div class="contact-info__left">
            <div class="contact-info__card contact-info__card--reach">
                <h2 class="contact-info__card-title"><?php esc_html_e('Reach us directly', 'sweetmunchies'); ?></h2>
                <?php if ($whatsapp): ?>
                    <a href="<?php echo esc_url('https://wa.me/' . $whatsapp); ?>" class="contact-info__whatsapp-button" target="_blank" rel="noopener noreferrer">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.38 5.07L2 22l5.07-1.33A9.94 9.94 0 0 0 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2Zm0 18a7.9 7.9 0 0 1-4.03-1.1l-.29-.17-3 .79.8-2.93-.19-.3A7.93 7.93 0 1 1 12 20Zm4.36-5.96c-.24-.12-1.42-.7-1.64-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.18-.71-.63-1.19-1.42-1.33-1.66-.14-.24-.01-.37.11-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.19-.46-.39-.4-.54-.4-.14 0-.3-.01-.46-.01-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.6 4.12 3.64.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28Z"/></svg>
                        <span class="contact-info__whatsapp-text">
                            <strong><?php esc_html_e('Chat on WhatsApp', 'sweetmunchies'); ?></strong>
                            <?php if ($phone_display): ?>
                                <small>
                                    <?php
                                    printf(
                                        /* translators: %s: display phone number, e.g. "0776 620 294" */
                                        esc_html__('%s · fastest reply', 'sweetmunchies'),
                                        esc_html($phone_display)
                                    );
                                    ?>
                                </small>
                            <?php endif; ?>
                        </span>
                    </a>
                <?php endif; ?>
                <?php if ($email): ?>
                    <a href="mailto:<?php echo esc_attr($email); ?>" class="contact-info__line">&#9993;&#65039; <?php echo esc_html($email); ?></a>
                <?php endif; ?>
                <?php if ($location): ?>
                    <p class="contact-info__line">&#128205; <?php echo esc_html($location); ?></p>
                <?php endif; ?>
            </div>

            <?php if ($delivery_text): ?>
                <div class="contact-info__card contact-info__card--delivery">
                    <h2 class="contact-info__card-title"><?php esc_html_e('Delivery', 'sweetmunchies'); ?></h2>
                    <p class="contact-info__line"><?php echo esc_html($delivery_text); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="contact-info__right">
            <?php // function_exists guard: a deactivated WPForms must degrade to a missing form, not a fatal on every page using this block. ?>
            <?php if ($form_id && function_exists('wpforms')): ?>
                <div class="contact-info__form">
                    <?php wpforms()->frontend->output($form_id); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
