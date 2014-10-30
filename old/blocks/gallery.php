<?php
if ( ! class_exists( 'Tenup_Content_Block' ) ) {
	return;
}

class Emmis_Gallery_Content_Block extends Tenup_Content_Block {

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
		if ( ! class_exists( 'NS_Post_Finder' ) ) {
			return;
		}
	?>
		<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="gallery" />

		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]">Title (or <a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>" target="_blank">image URL</a>)</label>
			<input type="text" class="widefat" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]" value="<?php echo ( isset( $data['title'] ) ) ? esc_attr( $data['title'] ) : "Photo Gallery" ; ?>" />
		</p>
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title_link]">Title Link (if desired)</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title_link]" class="widefat" value="<?php echo isset( $data['title_link'] ) ? esc_url( $data['title_link'] ) : ''; ?>"/>
		</p>
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][number]">Number Of Images To Show</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][number]" value="<?php echo isset( $data['number'] ) ? esc_attr( $data['number'] ) : ''; ?>"/>
		</p>

	<?php
		$options['limit'] = 1;
		$options['args'] = array(
			'post_type' => array( 'emmis-galleries' ),
		);

		$options['args']['post_status'] = array( 'publish' );

		if ( ! isset( $data['post'] ) ) {
			$data['post'] = '';
		}

		NS_Post_Finder::render( 'tenup_content_blocks[' . $row . '][' . $area . '][' . $column . '][' . $iterator . '][post]', $data['post'], $options );
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
		$new['type'] = sanitize_key( $data['type'] );
		$new['title'] = wp_filter_post_kses( $data['title'] );
		$new['title_link'] = isset( $data['title_link'] ) ? esc_url_raw( $data['title_link'] ) : '';
		$new['post'] = isset( $data['post'] ) ? implode( ',', array_map( 'absint', explode( ',', $data['post'] ) ) ) : '';
		$new['number'] = isset( $data['number'] ) ? absint( $data['number'] ) : '1';

		return $new;
	}

	/**
	 * Display the block.
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

		$featured_gallery = get_post( $data['post'] );
		if ( is_wp_error( $featured_gallery ) ) {
			return;
		}

		global $post;
		$post = $featured_gallery;
		setup_postdata( $post );
	?>
		<div class="module module-gallery">
		<?php
		if ( isset( $data['title'] ) && '' !== trim( $data['title'] ) ) {
			echo '<h2 class="h2">';
			if ( isset( $data['title_link'] ) && '' !== trim( $data['title_link'] ) ) {
				echo '<a href="'. esc_url( $data['title_link'] ) .'">';
			}
			if ( 'http' === substr( $data['title'], 0, 4 ) || 'www' === substr( $data['title'], 0, 3 ) ) {
				echo '<img src="'. esc_url( $data['title'] ) .'">';
			} else {
				echo esc_html( $data['title'] );
			}
			if ( isset( $data['title_link'] ) && '' !== trim( $data['title_link'] ) ) {
				echo '</a>';
			}
			echo '</h2>';
		}
		?>

			<?php
			$images = emmis_get_images( get_the_ID(), 'medium', $data['number'] );
			if ( $images && ! empty( $images ) ) :
				echo '<div class="row module-gallery-row">';
				$i = 0;
				foreach ( $images as $image ) :
					if ( $i === 0 ) {
						$class = 'col-2-4';
					} else if ( $i === 9 ) {
						$class = 'col-3-4';
					} else {
						$class = 'col-1-4';
					}
			?>
					<a href="<?php the_permalink(); ?>" class="<?php echo esc_attr( $class ); ?>">
						<div class="image-crop">
							<?php echo wp_kses_post( $image ); ?>
						</div>
					</a>
				<?php $i++; ?>
				<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php
		wp_reset_postdata();
	}
}
