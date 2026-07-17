<?php
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
$nav_cta = get_field('nav_cta', 'option'); // Call-to-action stored in options
$socials = get_field('socials', 'option'); // Social links stored in options
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
		href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Poppins:wght@500;600;700&display=swap"
		as="style" onload="this.onload=null;this.rel='stylesheet'" />
	<noscript>
		<link
			href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Poppins:wght@500;600;700&display=swap"
			rel="stylesheet" />
	</noscript>

</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site">

		<header class="header">
			<div class="header__container">
				<div class="header__logo">
					<a href="<?php echo esc_url(home_url('/')); ?>">
						<?php if ($site_logo): ?>
							<img src="<?php echo esc_url($site_logo['url']); ?>"
								alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
								width="46" height="46" class="header__logo-image"
								decoding="async" />
						<?php endif; ?>
						<span class="header__title"><?php bloginfo('name'); ?></span>
					</a>
				</div>
				<!-- Hamburger Toggle Button -->
				<div class="header__menu menu">
					<div class="menu__icon">
						<span></span>
					</div>

					<nav data-sub_menu_auto_close="true" class="menu__body">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'menu-1',
								'menu_id' => 'primary-menu',
							)
						);
						?>
						<div class="header__menu-cta d-mobile-only">
							<?php if ($socials): ?>
								<ul class="social-links">
									<?php if (!empty($socials['whatsapp'])): ?>
										<li>
											<a href="https://wa.me/<?php echo esc_attr($socials['whatsapp']); ?>"
												target="_blank" rel="noopener noreferrer">WhatsApp</a>
										</li>
									<?php endif; ?>
									<?php if (!empty($socials['linkedin'])): ?>
										<li>
											<a href="<?php echo esc_url($socials['linkedin']); ?>" target="_blank"
												rel="noopener noreferrer">LinkedIn</a>
										</li>
									<?php endif; ?>
									<?php if (!empty($socials['facebook'])): ?>
										<li>
											<a href="<?php echo esc_url($socials['facebook']); ?>" target="_blank"
												rel="noopener noreferrer">Facebook</a>
										</li>
									<?php endif; ?>
								</ul>
							<?php endif; ?>
						</div>
					</nav>

				</div>

				<div class="header__menu-cta d-desktop-only">
					<?php if ($nav_cta): ?>
						<a href="<?php echo esc_url($nav_cta['url']); ?>" class="button button--primary">
							<?php echo esc_html($nav_cta['title']); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</header>