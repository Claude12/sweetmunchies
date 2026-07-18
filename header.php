<?php

declare(strict_types=1);

/**
 * The header for our theme
 *
 * Displays the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package sweetmunchies
 */

// Global settings from ACF Options Page
$site_logo = get_field('site_logo', 'option'); // Logo stored in options
$promo_text = get_field('promo_text', 'option');

$cart_count = 0;
if (function_exists('WC') && WC()->cart) {
	$cart_count = WC()->cart->get_cart_contents_count();
}
?>

<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<!-- Font preconnect (reduces DNS + TLS latency before wp_head loads font CSS) -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

	<!-- WordPress Head -->
	<?php wp_head(); ?>

	<!-- Fonts (async load, noscript fallback) -->
	<link rel="preload"
		href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Caveat:wght@400;700&display=swap"
		as="style" onload="this.onload=null;this.rel='stylesheet'" />
	<noscript>
		<link
			href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Caveat:wght@400;700&display=swap"
			rel="stylesheet" />
	</noscript>

</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site">

		<?php if ($promo_text): ?>
			<div class="promo-strip"><?php echo wp_kses_post($promo_text); ?></div>
		<?php endif; ?>

		<header class="header">
			<div class="header__container">
				<div class="header__logo">
					<a href="<?php echo esc_url(home_url('/')); ?>">
						<?php if ($site_logo): ?>
							<img src="<?php echo esc_url($site_logo['url']); ?>"
								alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
								class="header__logo-image"
								decoding="async" />
						<?php endif; ?>
					</a>
				</div>

				<nav class="header__nav" aria-label="<?php esc_attr_e('Primary', 'sweetmunchies'); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-1',
							'menu_id' => 'primary-menu-desktop',
							'menu_class' => 'header__nav-list',
							'container' => false,
						)
					);
					?>
				</nav>

				<div class="header__actions">
					<button type="button" class="header__icon-btn" data-search-toggle
						aria-label="<?php esc_attr_e('Search', 'sweetmunchies'); ?>" aria-expanded="false">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none">
							<circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8" />
							<path d="m21 21-4.3-4.3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
						</svg>
					</button>

					<a href="<?php echo esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/')); ?>"
						class="header__icon-btn" aria-label="<?php esc_attr_e('Cart', 'sweetmunchies'); ?>">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
							<path d="M6 7h13l-1.5 9.5a2 2 0 0 1-2 1.7H8.7a2 2 0 0 1-2-1.7L5 4H2" stroke="currentColor"
								stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
							<circle cx="9.5" cy="20.5" r="1.4" fill="currentColor" />
							<circle cx="17" cy="20.5" r="1.4" fill="currentColor" />
						</svg>
						<span class="header__cart-count<?php echo $cart_count > 0 ? '' : ' is-hidden'; ?>"><?php echo esc_html((string) $cart_count); ?></span>
					</a>

					<button type="button" class="header__icon-btn header__hamburger" data-menu-toggle
						aria-label="<?php esc_attr_e('Menu', 'sweetmunchies'); ?>" aria-expanded="false"
						aria-controls="mobile-drawer">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
							<path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8"
								stroke-linecap="round" />
						</svg>
					</button>
				</div>
			</div>
		</header>

		<div class="mobile-drawer" id="mobile-drawer" data-mobile-nav>
			<div class="mobile-drawer__backdrop" data-menu-close></div>
			<div class="mobile-drawer__panel">
				<div class="mobile-drawer__top">
					<?php if ($site_logo): ?>
						<img src="<?php echo esc_url($site_logo['url']); ?>"
							alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
							class="mobile-drawer__logo"
							decoding="async" />
					<?php endif; ?>
					<button type="button" class="mobile-drawer__close" data-menu-close
						aria-label="<?php esc_attr_e('Close menu', 'sweetmunchies'); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none">
							<path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
						</svg>
					</button>
				</div>

				<nav class="mobile-drawer__nav" aria-label="<?php esc_attr_e('Mobile', 'sweetmunchies'); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-1',
							'menu_id' => 'primary-menu-mobile',
							'menu_class' => 'mobile-drawer__list',
							'container' => false,
						)
					);
					?>
				</nav>
			</div>
		</div>

		<div class="search-overlay" data-search-overlay>
			<div class="search-overlay__backdrop" data-search-close></div>
			<div class="search-overlay__panel">
				<div class="search-overlay__inner">
					<?php if (shortcode_exists('fibosearch')): ?>
						<?php echo do_shortcode('[fibosearch style="solaris" layout="classic" submit_btn="off"]'); ?>
					<?php endif; ?>
					<button type="button" class="search-overlay__close" data-search-close aria-label="<?php esc_attr_e('Close', 'sweetmunchies'); ?>">&times;</button>
				</div>
			</div>
		</div>