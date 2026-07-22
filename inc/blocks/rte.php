<?php

declare(strict_types=1);

/**
 * Block: Rich Text
 * ACF flexible content layout `rte` — a single WYSIWYG field for long-form
 * prose (the Privacy and Refund policies). Exists so plain editorial pages
 * stay inside the content_sections architecture instead of needing the
 * classic editor turned back on. Rendered inside the have_rows()/the_row()
 * loop in sweetmunchies_render_flexible_content() (see inc/acf.php).
 *
 * The `.rte-block` wrapper is deliberately not the bare `.rte` class used
 * inside image-text-block: that one is styled only when nested under
 * `.image-text-block__col`, so a global `.rte` here would leak new list and
 * link rules into that block's copy.
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

$content = get_sub_field('content');

if (!$content) {
    return;
}
?>

<section class="rte-block">
    <div class="container">
        <?php
        // Deliberately no animate="fade-in" here, unlike the decorative blocks:
        // [animate] starts at opacity:0 and waits for JS to add .animated, which
        // would leave a legal policy invisible if the script fails to run.
        ?>
        <div class="rte-block__content">
            <?php echo wp_kses_post($content); ?>
        </div>
    </div>
</section>
