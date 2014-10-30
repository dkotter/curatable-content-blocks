<?php
if ( ! class_exists( 'Tenup_Content_Block' ) ) {
	return;
}

class Emmis_Lists_Content_Block extends Tenup_Content_Block {

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
		<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="list" />
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]">Title (or <a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>" target="_blank">image URL</a>)</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]" class="widefat" value="<?php echo isset( $data['title'] ) ? esc_attr( $data['title'] ) : ''; ?>"/>
		</p>
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title_link]">Title Link (if desired)</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title_link]" class="widefat" value="<?php echo isset( $data['title_link'] ) ? esc_url( $data['title_link'] ) : ''; ?>"/>
		</p>

		<?php if ( class_exists( 'NS_Post_Finder' ) ) : ?>
			<hr/>

			<div class="hide-if-no-js">
				<?php
				$options['limit'] = 50;
				$options['outside_links'] = true;
				$options['args'] = array(
					'post_type' => array( 'post', 'emmis-bus-listings', 'tribe_events' ),
					'meta_key'  => '_emmis_not_here',
				);

				// Only admins can see everything
				if ( current_user_can( 'manage_options' ) ) {
					$options['args']['post_status'] = 'any';
				} else {
					$options['args']['post_status'] = array( 'publish', 'pending', 'draft', 'inherit' );
				}

				NS_Post_Finder::render( 'tenup_content_blocks[' . $row . '][' . $area . '][' . $column . '][' . $iterator . '][posts]', isset( $data['posts'] ) ? $data['posts'] : '', $options );
				?>
			</div>
			<hr/>
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
		$new['type'] = sanitize_key( $data['type'] );

		$new['posts'] = isset( $data['posts'] ) ? $data['posts'] : '';
		$new['title'] = isset( $data['title'] ) ? wp_filter_post_kses( $data['title'] ) : '';
		$new['title_link'] = isset( $data['title_link'] ) ? esc_url_raw( $data['title_link'] ) : '';

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
			// If in manual mode, and we dont have a post, gracefully fail
			return;
		}

		$items = explode( ',', $data['posts'] );

		echo '<div class="module module-toplists">';
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
		echo '<ol>';
		foreach ( $items as $item ) {
			if ( is_numeric( $item ) ) {
				global $post;

				$post = get_post( (int) $item );
				setup_postdata( $post );

				self::markup( $data );
			} elseif ( strpos( $item, ';' ) ) {
				$outside_item = explode( ';', $item );
				self::markup_outside_item( $outside_item );
			}
		}
		echo '</ol></div><!-- .module-toplists -->';

		wp_reset_postdata();
	}

	/**
	 * Display the block if it's a normal post.
	 *
	 * @param array $data Data saved in block.
	 * @return void
	 */
	public static function markup( $data ) {
	?>
		<li>
			<a href="<?php the_permalink(); ?>">
				<?php the_title(); ?>
			</a>
		</li>
	<?php
	}

	/**
	 * Display the block for an outside link.
	 *
	 * @param array $outside_item Outside link information.
	 * @return void
	 */
	public static function markup_outside_item( $outside_item ) {
	?>
		<li>
			<a href="<?php echo esc_url( $outside_item[1] ); ?>">
				<?php echo esc_html( $outside_item[0] ); ?>
			</a>
		</li>
	<?php
	}

}