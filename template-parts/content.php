<?php

declare(strict_types=1);

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<a href="<?php the_permalink(); ?>" rel="bookmark">
		<div class="card">
			<?php if (has_post_thumbnail()): ?>
				<div class="post-thumbnail">
					<?php the_post_thumbnail(); ?>
				</div><!-- .post-thumbnail -->
			<?php endif; ?>
			<div class="card-body">
				<header class="entry-header">
					<?php the_title('<h3 class="entry-title">', '</h3>'); ?>
				</header><!-- .entry-header -->

				<div class="entry-excerpt">
					<?php the_excerpt(); ?>
				</div><!-- .entry-excerpt -->

				<a class="card-body__cta link" href="<?php the_permalink(); ?>" rel="bookmark">
					Read more
				</a>
			</div>
		</div>
	</a>
</article><!-- #post-<?php the_ID(); ?> -->
