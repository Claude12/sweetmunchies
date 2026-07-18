<?php

declare(strict_types=1);

/**
 * The template for displaying 404 pages (not found) — full custom markup,
 * the theme owns it directly (same "theme owns all markup" approach as
 * page-cart.php / content-single-product.php) rather than depending on an
 * editable ACF page that was never actually configured.
 *
 * @package sweetmunchies
 */

get_header();

$whatsapp   = get_field('socials', 'option')['whatsapp'] ?? '';
$categories = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'number'     => 6,
]);
$categories = is_wp_error($categories) ? [] : $categories;
$deco_src   = esc_url(get_template_directory_uri() . '/images/candy-icon.webp');
?>

<main id="primary" class="site-main error-404">
    <img class="error-404__deco error-404__deco--1" src="<?php echo $deco_src; ?>" alt="" aria-hidden="true" />
    <img class="error-404__deco error-404__deco--2" src="<?php echo $deco_src; ?>" alt="" aria-hidden="true" />
    <img class="error-404__deco error-404__deco--3" src="<?php echo $deco_src; ?>" alt="" aria-hidden="true" />
    <img class="error-404__deco error-404__deco--4" src="<?php echo $deco_src; ?>" alt="" aria-hidden="true" />

    <div class="container">
        <div class="error-404__inner" animate="fade-in-up">
            <div class="error-404__numeral" role="img" aria-label="404">
                <span class="error-404__digit" aria-hidden="true">4</span>
                <svg class="error-404__candy" viewBox="0 0 120 120" aria-hidden="true">
                    <path d="M32 60 4 40v40l28-20Z" fill="var(--color-accent)" />
                    <path d="M88 60l28-20v40L88 60Z" fill="var(--color-accent)" />
                    <circle cx="60" cy="60" r="34" fill="var(--color-primary)" />
                    <path d="M42 44c6 10 6 22 0 32M60 40c7 11 7 29 0 40M78 44c6 10 6 22 0 32" stroke="var(--color-white)" stroke-width="4" stroke-linecap="round" fill="none" opacity="0.55" />
                </svg>
                <span class="error-404__digit" aria-hidden="true">4</span>
            </div>

            <p class="error-404__eyebrow">Uh-oh, sugar rush interrupted</p>
            <h1 class="error-404__heading"><?php esc_html_e("This page isn't in the box", 'sweetmunchies'); ?></h1>
            <p class="error-404__subtext">
                <?php esc_html_e("We checked every gift box and candy tub, but couldn't find the page you were after. It might have been moved, renamed, or never existed.", 'sweetmunchies'); ?>
            </p>

            <div class="error-404__ctas">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="error-404__cta error-404__cta--primary">
                    <?php esc_html_e('Back to Home', 'sweetmunchies'); ?>
                </a>
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="error-404__cta error-404__cta--secondary">
                    <?php esc_html_e('Browse Shop', 'sweetmunchies'); ?>
                </a>
            </div>

            <?php if ($categories): ?>
                <div class="error-404__categories">
                    <p class="error-404__categories-label"><?php esc_html_e('Or jump straight to a category:', 'sweetmunchies'); ?></p>
                    <div class="error-404__pills">
                        <?php foreach ($categories as $category): ?>
                            <a href="<?php echo esc_url(get_term_link($category)); ?>" class="error-404__pill">
                                <?php echo esc_html($category->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($whatsapp): ?>
                <div class="error-404__whatsapp">
                    <p class="error-404__whatsapp-text"><?php esc_html_e("Still can't find it?", 'sweetmunchies'); ?></p>
                    <a
                        href="<?php echo esc_url('https://wa.me/' . rawurlencode($whatsapp)); ?>"
                        class="error-404__whatsapp-button"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.38 5.07L2 22l5.07-1.33A9.94 9.94 0 0 0 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2Zm0 18a7.9 7.9 0 0 1-4.03-1.1l-.29-.17-3 .79.8-2.93-.19-.3A7.93 7.93 0 1 1 12 20Z" /></svg>
                        <?php esc_html_e('Chat on WhatsApp', 'sweetmunchies'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main><!-- #main -->

<?php
get_footer();
