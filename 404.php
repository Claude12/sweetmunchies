<?php

declare(strict_types=1);

/**
 * The template for displaying 404 pages (not found)
 *
 * Renders ACF flexible content blocks from a page with slug '404-error'.
 * Falls back to a simple message if that page doesn't exist or has no blocks.
 *
 * @package sweetmunchies
 */

get_header();

$error_page = get_page_by_path( '404-error' );
$has_error_page_content = $error_page && function_exists( 'get_field' ) && get_field( 'content_sections', $error_page->ID );
?>

<main id="primary" class="site-main">

	<?php if ( $has_error_page_content ) : ?>

		<?php sweetmunchies_render_flexible_content( $error_page->ID ); ?>

	<?php else : ?>

		<section class="error-404-fallback">
			<div class="container">
				<h1><?php esc_html_e( 'Page Not Found', 'sweetmunchies' ); ?></h1>
				<p><?php esc_html_e( "It looks like nothing was found at this location. Let's get you back on track.", 'sweetmunchies' ); ?></p>
				<a class="button button--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'Go Home', 'sweetmunchies' ); ?>
				</a>
			</div>
		</section>

	<?php endif; ?>

</main><!-- #main -->

<?php
get_footer();
