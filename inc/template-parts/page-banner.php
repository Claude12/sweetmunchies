<?php

declare(strict_types=1);

/**
 * Interior page banner — photo + gradient overlay hero with a RankMath
 * breadcrumb strip beneath it. Standalone (not a content_sections layout),
 * called directly from page.php for every non-front page. Renders nothing
 * if no background image has been set for the page.
 *
 * @package sweetmunchies
 */

$eyebrow    = get_field('banner_eyebrow');
$heading    = get_field('banner_heading') ?: get_the_title();
$background = get_field('banner_background');

if (!$background) {
    return;
}
?>

<section class="page-banner" style="background-image: url('<?php echo esc_url($background['sizes']['sweetmunchies_page_banner'] ?? $background['url']); ?>');">
    <div class="page-banner__overlay"></div>
    <div class="container page-banner__content">
        <?php if ($eyebrow): ?>
            <p class="page-banner__eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <?php endif; ?>
        <h1 class="page-banner__heading"><?php echo esc_html($heading); ?></h1>
    </div>
</section>

<?php if (function_exists('rank_math_the_breadcrumbs')): ?>
    <div class="container page-banner__breadcrumb">
        <?php rank_math_the_breadcrumbs(); ?>
    </div>
<?php endif; ?>
