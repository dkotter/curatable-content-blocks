<?php
if ( ! class_exists( 'CCB_Content_Block' ) ) {
	return;
}

class CCB_Featured_Images_Content_Block extends CCB_Content_Block {

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
		<input type="hidden" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="images" />

		<?php
		/**
		 * Fires before other fields are rendered.
		 *
		 * Allows easy addition of other fields.
		 *
		 * @since 0.1.0
		 */
		do_action( 'ccb_settings_form_images' );
		?>

		<p>
			<label for="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]"><?php esc_html_e( 'Title', 'ccb' ); ?></label>
			<input type="text" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]" class="widefat" value="<?php echo isset( $data['title'] ) ? esc_attr( $data['title'] ) : ''; ?>"/>
		</p>

		<div class="image-container">
			<h4><?php esc_html_e( 'Images', 'ccb' ); ?></h4>
			<?php
			$posts = ( isset( $data['posts'] ) ) ? explode( ',', $data['posts'] ) : array();
			$count = 1;
			foreach ( $posts as $post ) { ?>
				<div class="image">
					<?php
					$image = wp_get_attachment_image_src( $post, 'thumbnail' );
					if ( $image ) {
						$image_src = $image[0];
					} else {
						$image_src = '';
					}
					?>
					<img src="<?php echo esc_url( $image_src ); ?>"/>
					<input type="hidden" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][posts][]" class="image-id-input" value="<?php echo absint( $post ); ?>"/>
					<br/>
					<div class="button button-primary select-img"><?php esc_html_e( 'Choose Image', 'ccb' ); ?></div>
					<a href="#" class="delete-image"><?php esc_html_e( 'Delete Image', 'ccb' ); ?></a>
				</div><!-- .image -->
				<?php
				$count++;
			}
			?>
		</div><!-- .images -->
		<p>
			<a href="#" class="add-image"><?php esc_html_e( 'Add Image', 'ccb' ); ?></a>
			<script type="text/html" class="image-template">
				<div class="image">
					<img src="" />
					<input type="hidden" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][posts][]" class="image-id-input" value=""/>
					<br/>
					<div class="button button-primary select-img"><?php esc_html_e( 'Choose Image', 'ccb' ); ?></div>
					<div class="delete-image"><?php esc_html_e( 'Delete Image', 'ccb' ); ?></div>
				</div><!-- .image -->
			</script>
		</p>

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
		$new['type']  = 'images';
		$new['title'] = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '';
		$new['posts'] = isset( $data['posts'] ) ? implode( ',', array_map( 'absint', $data['posts'] ) ) : '';

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
		$new = apply_filters( 'ccb_clean_data_images', $new, $data );

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
			// If we don't have any images, gracefully fail
			return;
		}

		/*
		 * Fires before the module markup is output.
		 *
		 * @since 0.1.0
		 */
		do_action( 'ccb_before_module_images' );
	?>
		<div class="module module-images">
			<?php if ( isset( $data['title'] ) && '' !== trim( $data['title'] ) ) : ?>
				<h2>
					<?php echo esc_html( $data['title'] ); ?>
				</h2>
			<?php endif; ?>
			<ul>
			<?php $posts = explode( ',', $data['posts'] );
				foreach ( $posts as $post_id ) :
					$att = get_post( $post_id );
					$src = wp_get_attachment_image_src( $post_id, 'portrait' );
				?>
				<li>
					<img src="<?php echo esc_url( $src[0] ); ?>" alt="<?php echo esc_attr( $att->post_excerpt ); ?>" />
					<span><?php echo esc_html( $att->post_excerpt ); ?></span>
				</li>
				<?php endforeach; ?>
			</ul>
		</div><!-- .module.module-images -->
	<?php
		/*
		 * Fires after the module markup is output.
		 *
		 * @since 0.1.0
		 */
		do_action( 'ccb_after_module_images' );
	}

}
