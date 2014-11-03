<?php
if ( ! class_exists( 'CCB_Content_Block' ) ) {
	return;
}

class CCB_Featured_Item_Content_Block extends CCB_Content_Block {

	/**
	 * Output our settings form for this block.
	 *
	 * @param array $data Data saved in block.
	 * @param string $area Current area we are in.
	 * @param int $row Current row number.
	 * @param int $column Current column number.
	 * @param int $iterator Current block number.
	 * @return void
	 */
	public static function settings_form( $data, $area, $row = 1, $column = 1, $iterator = 0 ) {
	?>
		<input type="hidden" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="featured-item" />

		<?php
		/**
		 * Fires before other fields are rendered.
		 *
		 * Allows easy addition of other fields.
		 *
		 * @since 0.1.0
		 */
		do_action( 'ccb_settings_form_featured-item' );
		?>

		<?php if ( class_exists( 'NS_Post_Finder' ) ) : ?>
			<div class="hide-if-no-js" style="margin-top: 20px;">
				<?php
				$options['limit'] = 1;
				$options['args'] = array(
					'post_type' => array( 'post' )
				);

				$options['args']['post_status'] = 'publish';

				NS_Post_Finder::render( 'ccb_content_blocks[' . $row . '][' . $area . '][' . $column . '][' . $iterator . '][post]', isset( $data['post'] ) ? $data['post'] : '', $options );
				?>
			</div><!-- .hide-if-no-js -->
		<?php endif; ?>

	<?php
	}

	/**
	 * Clean our data saved in this block.
	 *
	 * @param array $data Data being saved in block.
	 * @return array
	 */
	public static function clean_data( $data ) {
		$new = array();

		$new['pause'] = isset( $data['pause'] ) ? 'y' : '';
		$new['type']  = 'featured-item';
		$new['post']  = isset( $data['post'] ) ? implode( ',', array_map( 'absint', explode( ',', $data['post'] ) ) ) : '';

		/**
		 * Filter the data being saved.
		 *
		 * Gives the ability to handle saving new fields
		 * that might be added via the ccb_settings_form_{block} hook.
		 *
		 * @since 0.1.0
		 *
		 * @param array $new The data we want to save.
		 * @param array $data The data sent to us.
		 */
		$new = apply_filters( 'ccb_clean_data_featured-item', $new, $data );

		return $new;
	}

	/**
	 * Run queries needed to display block.
	 *
	 * @param array $data Data saved in block.
	 * @param string $area Current area.
	 * @return void
	 */
	public static function display( $data, $area ) {
		if ( ! isset( $data['post'] ) || empty( $data['post'] ) ) {
			// If we don't have a post, gracefully fail
			return;
		}

		$featured_post = get_post( $data['post'] );
		if ( is_wp_error( $featured_post ) ) {
			return;
		}

		global $post;
		$post = $featured_post;
		setup_postdata( $post );

		self::markup( $data );

		wp_reset_postdata();
	}

	/**
	 * Display the block.
	 *
	 * @param array $data Data saved in block.
	 * @return void
	 */
	public static function markup( $data ) {
		/*
		 * Fires before the module markup is output.
		 *
		 * @since 0.1.0
		 */
		do_action( 'ccb_before_module_featured-item' );
	?>
		<div class="module module-single-article">
			<a href="<?php the_permalink(); ?>" class="story cover-story">
				<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'medium' );
				}
				?>
			</a>
			<div class="cover-story-meta">
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<p><span class="date"><?php the_time( 'F j, Y' ); ?></span></p>
				<p class="story-tags">
					<?php the_terms( get_the_ID(), 'post_tag' ); ?>
				</p>
			</div><!-- .cover-story-meta -->
		</div><!-- .module.module-single-article -->
	<?php
		/*
		 * Fires after the module markup is output.
		 *
		 * @since 0.1.0
		 */
		do_action( 'ccb_after_module_featured-item' );
	}

}
