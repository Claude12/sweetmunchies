<?php

declare(strict_types=1);

/**
 * Block: Testimonials
 * ACF flexible content layout `testimonials` — a heading + grid of customer
 * quotes. Page-agnostic (usable on any page, not just Home). Rendered inside
 * the have_rows()/the_row() loop in sweetmunchies_render_flexible_content()
 * (see inc/acf.php).
 *
 * NOTE: on the Home page, this section currently holds the design file's
 * placeholder mockup quotes (flagged in the ACF field instructions too) —
 * not real customer reviews. Replace with real testimonials as they come in.
 *
 * @package sweetmunchies
 */

$heading = get_sub_field('heading');
$items   = get_sub_field('items');

if (!$items) {
    return;
}
?>

<section class="testimonials">
    <img class="testimonials__deco" src="<?php echo esc_url(get_template_directory_uri() . '/images/candy-icon.webp'); ?>" alt="" aria-hidden="true" />
    <div class="container">
        <?php if ($heading): ?>
            <h2 class="testimonials__heading" animate="fade-in"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <ul class="testimonials__list">
            <?php foreach ($items as $item): ?>
                <?php if (empty($item['quote'])) continue; ?>
                <li class="testimonials__item">
                    <?php $stars = max(1, min(5, (int) ($item['stars'] ?: 5))); ?>
                    <div class="testimonials__stars" aria-hidden="true">
                        <?php for ($i = 0; $i < $stars; $i++): ?>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26 6.91 1.01-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" /></svg>
                        <?php endfor; ?>
                    </div>
                    <p class="testimonials__quote">&ldquo;<?php echo esc_html($item['quote']); ?>&rdquo;</p>
                    <?php if (!empty($item['name'])): ?>
                        <p class="testimonials__name"><?php echo esc_html($item['name']); ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
