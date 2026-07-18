<?php

declare(strict_types=1);

/**
 * Block: Trust Bar
 * ACF flexible content layout `trust_bar` — a row of small trust indicators
 * (icon + label). Rendered inside the have_rows()/the_row() loop in
 * sweetmunchies_render_flexible_content() (see inc/acf.php).
 *
 * @package sweetmunchies
 */

$items = get_sub_field('items');

if (!$items) {
    return;
}
?>

<section class="trust-bar">
    <div class="container">
        <ul class="trust-bar__list" animate="fade-in">
            <?php foreach ($items as $item): ?>
                <?php if (empty($item['label'])) continue; ?>
                <li class="trust-bar__item">
                    <?php if (!empty($item['icon'])): ?>
                        <span class="trust-bar__icon" aria-hidden="true"><?php echo esc_html($item['icon']); ?></span>
                    <?php endif; ?>
                    <span class="trust-bar__label"><?php echo esc_html($item['label']); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
