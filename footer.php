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
$socials = get_field('socials', 'option');
$footer_tagline = get_field('footer_tagline', 'option');
$footer_thanks = get_field('footer_thanks', 'option');
$payment_badges = get_field('payment_badges', 'option');
$contact_info = get_field('contact_info', 'option');
$footer_credit = get_field('footer_credit', 'option');

?>

<div id="scroll-to-top" class="scroll-to-top">
	<svg width="70px" height="70px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path
			d="M5 15C5 16.8565 5.73754 18.6371 7.05029 19.9498C8.36305 21.2626 10.1435 21.9999 12 21.9999C13.8565 21.9999 15.637 21.2626 16.9498 19.9498C18.2625 18.6371 19 16.8565 19 15V9C19 7.14348 18.2625 5.36305 16.9498 4.05029C15.637 2.73754 13.8565 2 12 2C10.1435 2 8.36305 2.73754 7.05029 4.05029C5.73754 5.36305 5 7.14348 5 9V15Z"
			stroke="var(--color-primary)" stroke-width="1.08" stroke-linecap="round" stroke-linejoin="round" />
		<path d="M12 6V14" stroke="var(--color-primary)" stroke-width="1.08" stroke-linecap="round" stroke-linejoin="round" />
		<path d="M15 11L12 14L9 11" stroke="var(--color-primary)" stroke-width="1.08" stroke-linecap="round"
			stroke-linejoin="round" />
	</svg>
</div>

<footer class="footer">
	<div class="footer__logo-wrap">
		<a href="<?php echo esc_url(home_url('/')); ?>">
			<?php if ($site_logo): ?>
				<img src="<?php echo esc_url($site_logo['url']); ?>"
					alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
					class="footer__logo" loading="lazy" decoding="async" />
			<?php endif; ?>
		</a>
	</div>

	<div class="footer__grid">
		<div class="footer__col footer__col--brand">
			<?php if ($footer_tagline): ?>
				<p class="footer__tagline"><?php echo esc_html($footer_tagline); ?></p>
			<?php endif; ?>
			<?php if ($footer_thanks): ?>
				<p class="footer__thanks"><?php echo esc_html($footer_thanks); ?></p>
			<?php endif; ?>

			<?php if ($socials): ?>
				<ul class="footer__socials">
					<?php if (!empty($socials['instagram'])): ?>
						<li>
							<a href="<?php echo esc_url($socials['instagram']); ?>" target="_blank" rel="noopener noreferrer"
								aria-label="Instagram">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
									<rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="1.6" />
									<circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.6" />
									<circle cx="17.5" cy="6.5" r="1" fill="currentColor" />
								</svg>
							</a>
						</li>
					<?php endif; ?>
					<?php if (!empty($socials['whatsapp'])): ?>
						<li>
							<a href="https://wa.me/<?php echo esc_attr($socials['whatsapp']); ?>" target="_blank"
								rel="noopener noreferrer" aria-label="WhatsApp">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
									<path
										d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.38 5.07L2 22l5.07-1.33A9.94 9.94 0 0 0 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2Zm0 18a7.9 7.9 0 0 1-4.03-1.1l-.29-.17-3 .79.8-2.93-.19-.3A7.93 7.93 0 1 1 12 20Z" />
								</svg>
							</a>
						</li>
					<?php endif; ?>
					<?php if (!empty($socials['tiktok'])): ?>
						<li>
							<a href="<?php echo esc_url($socials['tiktok']); ?>" target="_blank" rel="noopener noreferrer"
								aria-label="TikTok">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
									<path
										d="M16.5 3c.4 2.2 1.9 3.7 4 4v3c-1.5 0-2.9-.4-4-1.2v6.4a5.7 5.7 0 1 1-5.7-5.7c.3 0 .6 0 .9.1v3.1a2.6 2.6 0 1 0 1.8 2.5V3h3Z" />
								</svg>
							</a>
						</li>
					<?php endif; ?>
					<?php if (!empty($socials['facebook'])): ?>
						<li>
							<a href="<?php echo esc_url($socials['facebook']); ?>" target="_blank" rel="noopener noreferrer"
								aria-label="Facebook">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
									<path
										d="M13.5 21v-7.5H16l.4-3H13.5V8.3c0-.87.24-1.46 1.5-1.46H16.5V4.3C16.2 4.26 15.2 4.17 14 4.17c-2.4 0-4 1.46-4 4.16v2.37H7.5v3H10V21h3.5Z" />
								</svg>
							</a>
						</li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="footer__col">
			<h4 class="footer__col-title">Shop</h4>
			<?php
			wp_nav_menu([
				'theme_location' => 'menu-2',
				'menu_class' => 'footer__links',
				'container' => false,
				'depth' => 1,
			]);
			?>
		</div>

		<div class="footer__col">
			<h4 class="footer__col-title">Get in touch</h4>
			<div class="footer__links">
				<?php if (!empty($socials['whatsapp']) && !empty($contact_info['phone_display'])): ?>
					<span>
						<?php esc_html_e('Phone: ', 'sweetmunchies'); ?><a href="https://wa.me/<?php echo esc_attr($socials['whatsapp']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($contact_info['phone_display']); ?></a>
					</span>
				<?php endif; ?>
				<?php if (!empty($contact_info['email'])): ?>
					<span>
						<?php esc_html_e('Email: ', 'sweetmunchies'); ?><a href="mailto:<?php echo esc_attr($contact_info['email']); ?>"><?php echo esc_html($contact_info['email']); ?></a>
					</span>
				<?php endif; ?>
				<?php if (!empty($contact_info['location'])): ?>
					<span>
						<?php
						printf(
							/* translators: %s: location */
							esc_html__('Location: %s', 'sweetmunchies'),
							esc_html($contact_info['location'])
						);
						?>
					</span>
				<?php endif; ?>
			</div>
		</div>

		<?php if ($payment_badges): ?>
			<div class="footer__col">
				<h4 class="footer__col-title"><?php esc_html_e('We accept', 'sweetmunchies'); ?></h4>
				<div class="footer__badges">
					<?php foreach ($payment_badges as $badge): ?>
						<?php if (!empty($badge['label'])): ?>
							<span class="footer__badge"><?php echo esc_html($badge['label']); ?></span>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<div class="footer__bottom">
		<span>&copy; <?php echo esc_html(date_i18n('Y')); ?> <?php bloginfo('name'); ?>. All rights reserved.</span>
		<?php if (!empty($footer_credit['url'])): ?>
			<span>
				<?php esc_html_e('Website designed and developed by', 'sweetmunchies'); ?>
				<a href="<?php echo esc_url($footer_credit['url']); ?>"
					target="<?php echo esc_attr($footer_credit['target'] ?: '_self'); ?>"
					rel="noopener noreferrer"><?php echo esc_html($footer_credit['title']); ?></a>
			</span>
		<?php endif; ?>
	</div>
</footer>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>

</html>
