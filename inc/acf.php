<?php

declare(strict_types=1);

/**
 * ACF setup: local JSON path, flexible-content renderer, admin dependency notice.
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

/**
 * Local JSON save/load point.
 *
 * Explicit even though it matches ACF's own default (get_stylesheet_directory()
 * . '/acf-json'), so the sync location is documented rather than implied.
 */
add_filter('acf/settings/save_json', function () {
	return get_stylesheet_directory() . '/acf-json';
});

if (! function_exists('sweetmunchies_render_flexible_content')) :
	/**
	 * Renders the "content_sections" ACF flexible content field for a post.
	 *
	 * Runs the field through ACF's own have_rows()/the_row() loop (rather than
	 * pulling the raw array via get_field()) so ACF's row context is active
	 * inside each block template — that's what makes get_sub_field() work
	 * there, per .cursorrules. Each layout name (underscores) maps to a
	 * template file of the same name (hyphens) in inc/blocks/, e.g.
	 * `image_text_block` => inc/blocks/image-text-block.php.
	 *
	 * Add a new block by: (1) adding a layout to the `content_sections` field
	 * group in ACF, (2) creating the matching inc/blocks/{name}.php template
	 * (using get_sub_field() for every field), (3) adding a matching SCSS
	 * partial under assets/scss/components/ and @use-ing it in style.scss.
	 *
	 * @param int|string|null $post_id Post ID to read the field from. Defaults to the current post.
	 */
	function sweetmunchies_render_flexible_content($post_id = null): void
	{
		if (! function_exists('have_rows')) {
			return;
		}

		$block_index = 0;

		while (have_rows('content_sections', $post_id)) :
			the_row();

			$template = str_replace('_', '-', (string) get_row_layout());
			set_query_var('block_index', $block_index);
			get_template_part('inc/blocks/' . $template);

			$block_index++;
		endwhile;
	}
endif;

/**
 * Restrict the "Shop by Occasion" block's Categories field (see
 * inc/blocks/shop-by-occasion.php) to product categories that currently
 * have at least one product — an empty category can't be selected as a
 * tile.
 */
add_filter('acf/fields/taxonomy/query/key=field_68f7c200p021', function ($args) {
	$args['hide_empty'] = true;

	return $args;
});

/**
 * Warn admins in wp-admin if Advanced Custom Fields Pro isn't active — every
 * template in this theme reads its content via ACF fields and will render
 * blank without it.
 */
add_action('admin_notices', function () {
	if (! current_user_can('activate_plugins') || class_exists('ACF')) {
		return;
	}

	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__('The sweetmunchies theme requires Advanced Custom Fields (Pro) to be installed and active — page, post and 404 content is driven entirely by ACF flexible content fields.', 'sweetmunchies')
	);
});
