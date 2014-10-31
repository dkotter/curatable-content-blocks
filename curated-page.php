<?php
/**
 * Curated page template
 */

get_header(); ?>

	<div id="content">
		<div class="grid">
			<div class="grid-inner">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php ccb_display_rows( get_post_meta( get_the_ID(), 'ccb_content_blocks', true ) ); ?>
				<?php endwhile; ?>
			</div><!-- .grid-inner -->
		</div><!-- .grid -->
	</div><!-- #content -->

<?php get_footer();
