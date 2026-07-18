<?php

declare(strict_types=1);

/**
 * Block: Home Hero
 * ACF flexible content layout `home_hero` — the homepage's top section:
 * badge, heading, subtext, two CTAs, a trust line, and a rotated photo
 * collage (2 fixed groups on desktop either side of the text, a 3-photo
 * row on mobile). Rendered inside the have_rows()/the_row() loop in
 * sweetmunchies_render_flexible_content() (see inc/acf.php).
 *
 * Matches wp-content/themes/design/Sweet Munchies.dc.html lines 145-212.
 *
 * @package sweetmunchies
 */

$badge_text       = get_sub_field('badge_text');
$heading          = get_sub_field('heading');
$subtext          = get_sub_field('subtext');
$primary_cta      = get_sub_field('primary_cta');
$secondary_cta    = get_sub_field('secondary_cta');
$trust_line       = get_sub_field('trust_line');
$collage_images   = get_sub_field('collage_images');
$price_badge_text = get_sub_field('price_badge_text');
$img_loading      = (int) get_query_var('block_index', 1) === 0 ? 'eager' : 'lazy';

$collage_images = $collage_images ? array_slice($collage_images, 0, 4) : array();

/**
 * Slot 0/2 are the tall (3:4) frames, slot 1/3 are the square (1:1) frames.
 * Slot 3 (bottom-right) carries the price badge and is dropped from the
 * mobile row — matches the design 1:1.
 */
$slot_shape = array(0 => 'portrait', 1 => 'square', 2 => 'portrait', 3 => 'square');

$render_frame = static function ($image, int $slot) use ($slot_shape, $img_loading, $price_badge_text) {
	if (!$image) {
		return;
	}
	?>
	<div class="home-hero__frame home-hero__frame--<?php echo (int) $slot; ?>">
		<div class="home-hero__frame-inner home-hero__frame-inner--<?php echo esc_attr($slot_shape[$slot]); ?>">
			<img
				src="<?php echo esc_url($image['sizes']['medium'] ?? $image['url']); ?>"
				alt="<?php echo esc_attr($image['alt']); ?>"
				width="<?php echo esc_attr($image['width']); ?>"
				height="<?php echo esc_attr($image['height']); ?>"
				loading="<?php echo esc_attr($img_loading); ?>"
				decoding="async" />
		</div>
		<?php if (3 === $slot && $price_badge_text): ?>
			<span class="home-hero__price-badge"><?php echo esc_html($price_badge_text); ?></span>
		<?php endif; ?>
	</div>
	<?php
};
?>

<section class="home-hero">
	<img class="home-hero__deco home-hero__deco--1" src="<?php echo esc_url(get_template_directory_uri() . '/images/candy-icon.webp'); ?>" alt="" aria-hidden="true" />
	<img class="home-hero__deco home-hero__deco--2" src="<?php echo esc_url(get_template_directory_uri() . '/images/candy-icon.webp'); ?>" alt="" aria-hidden="true" />
	<img class="home-hero__deco home-hero__deco--3" src="<?php echo esc_url(get_template_directory_uri() . '/images/candy-icon.webp'); ?>" alt="" aria-hidden="true" />
	<img class="home-hero__deco home-hero__deco--4" src="<?php echo esc_url(get_template_directory_uri() . '/images/candy-icon.webp'); ?>" alt="" aria-hidden="true" />
	<img class="home-hero__deco home-hero__deco--5" src="<?php echo esc_url(get_template_directory_uri() . '/images/candy-icon.webp'); ?>" alt="" aria-hidden="true" />

	<div class="container home-hero__inner">
		<div class="home-hero__content" animate="slide-in-up">
			<?php if ($badge_text): ?>
				<span class="home-hero__badge"><?php echo esc_html($badge_text); ?></span>
			<?php endif; ?>

			<?php if ($heading): ?>
				<?php
				$highlight = '<span class="home-hero__highlight">sweet'
					. '<svg viewBox="0 0 120 14" class="home-hero__highlight-underline" aria-hidden="true">'
					. '<path d="M2 9c20-8 96-8 116 0" stroke="currentColor" stroke-width="5" fill="none" stroke-linecap="round" />'
					. '</svg></span>';
				?>
				<?php // wp_kses_post would strip the <svg> underline; $heading is already esc_html'd and $highlight is a fixed literal, so raw output here is safe. ?>
				<h1 class="home-hero__heading"><?php echo str_ireplace('sweet', $highlight, esc_html($heading)); ?></h1>
			<?php endif; ?>

			<?php if ($subtext): ?>
				<p class="home-hero__subtext"><?php echo esc_html($subtext); ?></p>
			<?php endif; ?>

			<?php if (!empty($primary_cta['url']) || !empty($secondary_cta['url'])): ?>
				<div class="home-hero__ctas">
					<?php if (!empty($primary_cta['url'])): ?>
						<a href="<?php echo esc_url($primary_cta['url']); ?>" class="home-hero__cta home-hero__cta--primary"
							target="<?php echo esc_attr($primary_cta['target'] ?: '_self'); ?>">
							<?php echo esc_html($primary_cta['title']); ?>
						</a>
					<?php endif; ?>
					<?php if (!empty($secondary_cta['url'])): ?>
						<a href="<?php echo esc_url($secondary_cta['url']); ?>" class="home-hero__cta home-hero__cta--secondary"
							target="<?php echo esc_attr($secondary_cta['target'] ?: '_self'); ?>">
							<?php echo esc_html($secondary_cta['title']); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ($trust_line): ?>
				<p class="home-hero__trust-line">
					<svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
						<path d="M12 2 3 6v6c0 5 4 8.5 9 10 5-1.5 9-5 9-10V6l-9-4Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
						<path d="M8.5 12.5l2.3 2.3L16 9.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
					<?php echo esc_html($trust_line); ?>
				</p>
			<?php endif; ?>
		</div>

		<?php if (count($collage_images) === 4): ?>
			<div class="home-hero__photo-group home-hero__photo-group--left" animate="fade-in-left">
				<?php
				$render_frame($collage_images[0], 0);
				$render_frame($collage_images[1], 1);
				?>
			</div>
			<div class="home-hero__photo-group home-hero__photo-group--right" animate="fade-in-right">
				<?php
				$render_frame($collage_images[2], 2);
				$render_frame($collage_images[3], 3);
				?>
			</div>

			<div class="home-hero__photo-mobile">
				<?php
				$render_frame($collage_images[0], 0);
				$render_frame($collage_images[1], 1);
				$render_frame($collage_images[2], 2);
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
