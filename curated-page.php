<?php
/**
 * Curated page template
 */

get_header(); ?>

	<?php
	/**
	 * Fires after call to get_header.
	 *
	 * @since 0.1.0
	 */
	do_action( 'ccb_after_header' ); ?>

	<div id="content">
		<div class="grid">
			<div class="grid-inner">
				<?php
				/**
				 * Fires before the rows are rendered.
				 *
				 * @since 0.1.0
				 */
				do_action( 'ccb_before_rows' ); ?>

				<?php while ( have_posts() ) : the_post(); ?>
					<?php ccb_display_rows( get_post_meta( get_the_ID(), 'ccb_content_blocks', true ) ); ?>
				<?php endwhile; ?>

				<?php
				/**
				 * Fires after the rows are rendered.
				 *
				 * @since 0.1.0
				 */
				do_action( 'ccb_after_rows' ); ?>
			</div><!-- .grid-inner -->
		</div><!-- .grid -->
	</div><!-- #content -->

	<?php
	/**
	 * Fires before the call to get_footer.
	 *
	 * @since 0.1.0
	 */
	do_action( 'ccb_before_footer' ); ?>

<?php get_footer();
