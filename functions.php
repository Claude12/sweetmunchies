<?php

declare(strict_types=1);

/**
 * sweetmunchies functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package sweetmunchies
 */

if (!defined('_S_VERSION')) {
	// Replace the version number of the theme on each release.
	define('_S_VERSION', '1.0.0'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- _s starter-theme constant, referenced throughout.
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function sweetmunchies_setup()
{
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on sweetmunchies, use a find and replace
	 * to change 'sweetmunchies' to the name of your theme in all the template files.
	 */
	load_theme_textdomain('sweetmunchies', get_template_directory() . '/languages');

	// Add default posts and comments RSS feed links to head.
	add_theme_support('automatic-feed-links');

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support('title-tag');

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support('post-thumbnails');

	// Product card image (see woocommerce/content-product.php) — matches the
	// 4:3 crop used across the homepage product grids.
	add_image_size('sweetmunchies_product_card', 480, 360, true);

	// Interior page banner background (see inc/template-parts/page-banner.php).
	add_image_size('sweetmunchies_page_banner', 1600, 520, true);

	// Single product page hero image — 4:5 crop (see woocommerce/content-single-product.php).
	add_image_size('sweetmunchies_product_hero', 960, 1200, true);

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__('Primary', 'sweetmunchies'),
			'menu-2' => esc_html__('Footer', 'sweetmunchies'),
		)
	);

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support('customize-selective-refresh-widgets');

	/**
	 * WooCommerce.
	 *
	 * @link https://github.com/woocommerce/woocommerce/wiki/Declaring-WooCommerce-support-in-themes
	 */
	add_theme_support('woocommerce');
	add_theme_support('wc-product-gallery-zoom');
	add_theme_support('wc-product-gallery-lightbox');
	add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'sweetmunchies_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function sweetmunchies_content_width()
{
	$GLOBALS['content_width'] = apply_filters('sweetmunchies_content_width', 640);
}
add_action('after_setup_theme', 'sweetmunchies_content_width', 0);

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function sweetmunchies_widgets_init()
{
	register_sidebar(
		array(
			'name' => esc_html__('Sidebar', 'sweetmunchies'),
			'id' => 'sidebar-1',
			'description' => esc_html__('Add widgets here.', 'sweetmunchies'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget' => '</section>',
			'before_title' => '<h2 class="widget-title">',
			'after_title' => '</h2>',
		)
	);
}
add_action('widgets_init', 'sweetmunchies_widgets_init');

/**
 * Enqueue scripts and styles.
 */
function sweetmunchies_scripts()
{
	// Fall back to the theme version if dist/ hasn't been built yet — a
	// missing file would make filemtime() emit a warning on every request.
	$css_file    = get_template_directory() . '/dist/css/style.css';
	$js_file     = get_template_directory() . '/dist/js/main.js';
	$css_version = file_exists($css_file) ? filemtime($css_file) : _S_VERSION;
	$js_version  = file_exists($js_file) ? filemtime($js_file) : _S_VERSION;

	// Fonts are self-hosted — @font-face rules live in the compiled
	// stylesheet (assets/scss/base/_fonts.scss), files in /fonts/.
	wp_enqueue_style('theme-style', get_template_directory_uri() . '/dist/css/style.css', array(), $css_version);

	wp_enqueue_script('theme-script', get_template_directory_uri() . '/dist/js/main.js', array(), $js_version, true);

	foreach (sweetmunchies_needed_block_assets() as $layout) {
		$assets = sweetmunchies_block_assets()[$layout] ?? array();

		foreach ($assets['styles'] ?? array() as $style) {
			wp_enqueue_style(
				$style['handle'],
				get_template_directory_uri() . $style['src'],
				$style['deps'] ?? array(),
				$style['version'] ?? _S_VERSION
			);
		}

		foreach ($assets['scripts'] ?? array() as $script) {
			wp_enqueue_script(
				$script['handle'],
				get_template_directory_uri() . $script['src'],
				$script['deps'] ?? array(),
				$script['version'] ?? _S_VERSION,
				$script['in_footer'] ?? true
			);
		}
	}
}
add_action('wp_enqueue_scripts', 'sweetmunchies_scripts');

/**
 * Per-block conditional asset registry.
 *
 * Some flexible-content layouts need extra CSS/JS (e.g. a carousel library)
 * that most pages don't — this keeps that weight off pages that don't use
 * the layout, instead of loading it site-wide via sweetmunchies_scripts().
 *
 * Add a block's assets by adding its acf_fc_layout key here, e.g.:
 *
 *   'gallery_carousel' => [
 *       'styles' => [
 *           ['handle' => 'splide-css', 'src' => '/dist/vendor/splide.min.css'],
 *       ],
 *       'scripts' => [
 *           ['handle' => 'splide-js', 'src' => '/dist/vendor/splide.min.js'],
 *       ],
 *   ],
 */
function sweetmunchies_block_assets(): array
{
	return array();
}

/**
 * Block templates run AFTER wp_head() fires wp_enqueue_scripts, so they
 * cannot call an enqueue function directly. Instead, detection runs on the
 * 'wp' action (before wp_head) by scanning the page's ACF layouts against
 * sweetmunchies_block_assets(), and sweetmunchies_scripts() enqueues
 * whatever the current page's layouts asked for.
 */
add_action('wp', function () {
	$registry = sweetmunchies_block_assets();

	// Skip the DB query entirely until at least one layout is registered.
	if (empty($registry) || !is_singular() || !function_exists('get_field')) return;

	$sections = get_field('content_sections');
	if (!$sections || !is_array($sections)) return;

	$needed = array();
	foreach ($sections as $section) {
		$layout = $section['acf_fc_layout'] ?? null;
		if ($layout && isset($registry[$layout])) {
			$needed[$layout] = true;
		}
	}

	$GLOBALS['sweetmunchies_needed_block_assets'] = array_keys($needed);
});

/**
 * The block layouts on the current page that have assets registered in
 * sweetmunchies_block_assets() — detected on the 'wp' action above.
 */
function sweetmunchies_needed_block_assets(): array
{
	return $GLOBALS['sweetmunchies_needed_block_assets'] ?? array();
}

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * ACF setup: options pages, flexible-content helpers, admin dependency notice.
 */
require get_template_directory() . '/inc/acf.php';

/**
 * WooCommerce integration.
 */
if (class_exists('WooCommerce')) {
	require get_template_directory() . '/inc/woocommerce.php';
	require get_template_directory() . '/inc/woocommerce-cart.php';
}

/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Remove the default content editor from pages and posts — all content is
 * managed via ACF flexible content blocks.
 */
add_action( 'init', function () {
	remove_post_type_support( 'page', 'editor' );
	remove_post_type_support( 'post', 'editor' );
} );

/**
 * Performance: disable WordPress emoji scripts/styles — unused on this site
 * and they add a DNS prefetch hint + inline JS to every page.
 */
add_action('init', function () {
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	add_filter('tiny_mce_plugins', function ($plugins) {
		return array_diff($plugins, ['wpemoji']);
	});
	add_filter('wp_resource_hints', function ($urls, $relation_type) {
		if ('dns-prefetch' === $relation_type) {
			return array_filter($urls, fn($url) => strpos((string) $url, 'emoji') === false);
		}
		return $urls;
	}, 10, 2);
});

/**
 * Performance: strip low-value <head> tags — EditURI, Windows Live Writer
 * manifest, WP version generator, shortlink, adjacent-post links, REST
 * discovery link, and oEmbed discovery. None are needed on a public site.
 */
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_shortlink_wp_head', 10);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
remove_action('wp_head', 'rest_output_link_wp_head', 10);
remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);

// Splide defer strategy — re-enable alongside the enqueue block above when a carousel block is built.
// add_action('wp_enqueue_scripts', function () {
//  wp_script_add_data('splide-js', 'strategy', 'defer');
// }, 20);

/**
 * Security Headers.
 */
function sweetmunchies_security_headers()
{
	header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
	header("X-Content-Type-Options: nosniff");
	header("X-Frame-Options: SAMEORIGIN");
	header("Referrer-Policy: no-referrer-when-downgrade");
	header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
	header("Cross-Origin-Opener-Policy: same-origin");
}
add_action('send_headers', 'sweetmunchies_security_headers');
