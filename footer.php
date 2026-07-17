<?php

declare(strict_types=1);

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package sweetmunchies
 */

$site_logo = get_field('site_logo', 'option');
$site_note = get_field('site_note', 'option');
$socials = get_field('socials', 'option');

?>

<div id="scroll-to-top" class="scroll-to-top">
	<svg width="70px" height="70px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

		<g id="SVGRepo_bgCarrier" stroke-width="0" />

		<g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" />

		<g id="SVGRepo_iconCarrier">
			<path
				d="M5 15C5 16.8565 5.73754 18.6371 7.05029 19.9498C8.36305 21.2626 10.1435 21.9999 12 21.9999C13.8565 21.9999 15.637 21.2626 16.9498 19.9498C18.2625 18.6371 19 16.8565 19 15V9C19 7.14348 18.2625 5.36305 16.9498 4.05029C15.637 2.73754 13.8565 2 12 2C10.1435 2 8.36305 2.73754 7.05029 4.05029C5.73754 5.36305 5 7.14348 5 9V15Z"
				stroke="#00bea3" stroke-width="1.08" stroke-linecap="round" stroke-linejoin="round" />
			<path d="M12 6V14" stroke="#00bea3" stroke-width="1.08" stroke-linecap="round" stroke-linejoin="round" />
			<path d="M15 11L12 14L9 11" stroke="#00bea3" stroke-width="1.08" stroke-linecap="round"
				stroke-linejoin="round" />
		</g>

	</svg>
</div>

<footer class="footer">
	<div class="footer-top curve-top">
		<div class="footer-brand">
			<a href="<?php echo esc_url(home_url('/')); ?>">
				<?php if ($site_logo): ?>
					<img src="<?php echo esc_url($site_logo['url']); ?>"
						alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
						class="footer-brand__logo" width="46" height="46"
						loading="lazy" decoding="async" />
				<?php endif; ?>
			</a>
			<a href="<?php echo esc_url(home_url('/')); ?>">
				<h2 class="footer-brand__title" style="text-align: center;"><?php bloginfo('name'); ?></h2>
			</a>
		</div>

		<!-- Social Media Links -->
		<div class="footer-socials">
			<?php if ($socials): ?>
				<ul class="social-links">
					<?php if (!empty($socials['whatsapp'])): ?>
						<li>
							<a href="https://wa.me/<?php echo esc_attr($socials['whatsapp']); ?>" target="_blank"
								rel="noopener noreferrer">WhatsApp</a>
						</li>
					<?php endif; ?>
					<?php if (!empty($socials['linkedin'])): ?>
						<li>
							<a href="<?php echo esc_url($socials['linkedin']); ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
						</li>
					<?php endif; ?>
					<?php if (!empty($socials['facebook'])): ?>
						<li>
							<a href="<?php echo esc_url($socials['facebook']); ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
						</li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
		</div>

		<!-- Footer disclaimer -->
		<?php if ($site_note): ?>
			<div class="footer-disclaimer">
				<p>
					<?php echo wp_kses_post($site_note); ?>
				</p>
			</div>
		<?php endif; ?>
	</div>

	<!-- Copyright Section -->
	<div class="footer-bottom">
		<div class="container">
			<div class="footer-copyright">
				<p>&copy; <?php echo date_i18n('Y'); ?> <?php bloginfo('name'); ?>. All Rights Reserved.</p>
			</div>

			<!-- Footer Menu -->
			<div class="footer-menu">
				<?php
				wp_nav_menu([
					'theme_location' => 'menu-2',
					'menu_class' => 'footer-nav',
					'container' => 'nav',
					'depth' => 1
				]);
				?>
			</div>
		</div>
	</div>
</footer>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>

</html>