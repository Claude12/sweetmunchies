<?php

declare(strict_types=1);

/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function sweetmunchies_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'sweetmunchies_body_classes' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function sweetmunchies_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'sweetmunchies_pingback_header' );

/**
 * Allow `fetchpriority` through wp_kses_post() on <img> tags — core's own
 * allowlist doesn't include it yet. Needed so the eager-loaded LCP product
 * image (see woocommerce/content-product.php) keeps its fetchpriority="high"
 * hint instead of being silently stripped by the wp_kses_post() wrapper
 * around $product->get_image().
 */
add_filter('wp_kses_allowed_html', function (array $tags, string $context): array {
	if ('post' === $context && isset($tags['img'])) {
		$tags['img']['fetchpriority'] = true;
	}

	return $tags;
}, 10, 2);
