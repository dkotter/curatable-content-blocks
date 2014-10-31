<?php
if ( ! class_exists( 'CCB_Content_Block' ) ) {
	return;
}

class CCB_Featured_Items_Content_Block extends CCB_Content_Block {

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
		<input type="hidden" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="featured-list" />
		<p>
			<label for="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]"><?php esc_html_e( 'Title', 'ccb' ); ?></label>
			<input type="text" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]" class="widefat" value="<?php echo isset( $data['title'] ) ? esc_attr( $data['title'] ) : ''; ?>"/>
		</p>

		<?php if ( class_exists( 'NS_Post_Finder' ) ) : ?>
			<div class="hide-if-no-js">
				<?php
				$options['limit'] = 50;
				$options['args'] = array(
					'post_type'   => array( 'post' ),
					'post_status' => 'publish',
				);

				NS_Post_Finder::render( 'ccb_content_blocks[' . $row . '][' . $area . '][' . $column . '][' . $iterator . '][posts]', isset( $data['posts'] ) ? $data['posts'] : '', $options );
				?>
			</div><!-- .hide-if-no-js -->
		<?php
		endif;
		?>

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
		$new['type']  = 'featured-list';
		$new['title'] = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '';
		$new['posts'] = isset( $data['posts'] ) ? implode( ',', array_map( 'absint', explode( ',', $data['posts'] ) ) ) : '';

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
		if ( ! isset( $data['posts'] ) || empty( $data['posts'] ) ) {
			// If in manual mode, and we don't have a post, gracefully fail
			return;
		}

		$post_ids = explode( ',', $data['posts'] );

		echo '<div class="module module-featured-list">';
		if ( isset( $data['title'] ) && '' !== trim( $data['title'] ) ) : ?>
			<h2 class="h2">
				<?php echo esc_html( $data['title'] ); ?>
			</h2>
		<?php endif;
		echo '<ul>';
		foreach ( $post_ids as $post_id ) {
			global $post;

			$post = get_post( (int) $post_id );
			setup_postdata( $post );

			self::markup( $data );
		}

		echo '</ul></div><!-- .module.module-featured-list -->';

		wp_reset_postdata();
	}

	/**
	 * Display the block.
	 *
	 * @param array $data Data saved in block.
	 * @return void
	 */
	public static function markup( $data ) {
	?>
		<li>
			<a href="<?php the_permalink(); ?>">
				<span class="date"><?php the_time( 'F j, Y' ); ?></span>
				<h3><?php the_title(); ?></h3>
			</a>
		</li>
	<?php
	}

}
