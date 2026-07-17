<?php
/**
 * ACF setup: local JSON path, flexible-content renderer, admin dependency notice.
 *
 * @package sweetmunchies
 */

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
	 * Each layout's `acf_fc_layout` key (underscores) maps to a template file
	 * of the same name (hyphens) in inc/blocks/, e.g. `image_text_block` =>
	 * inc/blocks/image-text-block.php. Add a new block by: (1) adding a layout
	 * to the `content_sections` field group in ACF, (2) creating the matching
	 * inc/blocks/{name}.php template, (3) adding a matching SCSS partial under
	 * assets/scss/components/ and @use-ing it in style.scss.
	 *
	 * @param int|null $post_id Post ID to read the field from. Defaults to the current post.
	 */
	function sweetmunchies_render_flexible_content($post_id = null)
	{
		if (! function_exists('get_field')) {
			return;
		}

		$sections = get_field('content_sections', $post_id);

		if (! $sections || ! is_array($sections)) {
			return;
		}

		foreach ($sections as $block_index => $section) {
			$template = str_replace('_', '-', $section['acf_fc_layout']);
			set_query_var('section', $section);
			set_query_var('block_index', $block_index);
			get_template_part('inc/blocks/' . $template);
		}
	}
endif;

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
