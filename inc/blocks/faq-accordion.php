<?php

declare(strict_types=1);

/**
 * Block: FAQ Accordion
 * ACF flexible content layout `faq_accordion` — a heading plus a repeater of
 * question/answer rows, each independently expandable via
 * assets/js/lib/faq-accordion.js. Rendered inside the have_rows()/the_row()
 * loop in sweetmunchies_render_flexible_content() (see inc/acf.php).
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

$heading = get_sub_field('heading');
$items   = get_sub_field('items');

// Namespaces panel IDs per block instance so two FAQ blocks on one page
// don't emit duplicate IDs (which would break the aria-controls pairing).
$block_index = (int) get_query_var('block_index', 0);

if (!$items) {
    return;
}
?>

<section class="faq-accordion">
    <img class="faq-accordion__deco" src="<?php echo esc_url(get_template_directory_uri() . '/images/candy-icon.webp'); ?>" alt="" aria-hidden="true" />
    <div class="container">
        <?php if ($heading): ?>
            <h2 class="faq-accordion__heading"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <div class="faq-accordion__list">
            <?php foreach ($items as $index => $item): ?>
                <?php
                $panel_id  = 'faq-' . $block_index . '-panel-' . $index;
                $is_open   = $index === 0;
                ?>
                <div class="faq-accordion__item<?php echo $is_open ? ' is-open' : ''; ?>">
                    <button type="button" class="faq-accordion__question" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr($panel_id); ?>">
                        <span><?php echo esc_html($item['question']); ?></span>
                        <svg class="faq-accordion__chevron" width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <div class="faq-accordion__answer" id="<?php echo esc_attr($panel_id); ?>">
                        <p><?php echo esc_html($item['answer']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
