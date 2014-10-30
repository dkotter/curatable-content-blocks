<?php
if ( ! class_exists( 'Tenup_Content_Block' ) ) {
	return;
}

class Emmis_Featured_Images_Content_Block extends Tenup_Content_Block {

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
		<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="images" />

		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]">Title (or <a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>" target="_blank">image URL</a>)</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]" class="widefat" value="<?php echo isset( $data['title'] ) ? esc_attr( $data['title'] ) : ''; ?>"/>
		</p>
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title_link]">Title Link (if desired)</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title_link]" class="widefat" value="<?php echo isset( $data['title_link'] ) ? esc_url( $data['title_link'] ) : ''; ?>"/>
		</p>
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][center]">
				<input type="checkbox" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][center]" id="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][center]" <?php isset( $data['center'] ) ? checked( $data['center'], 'y' ) : ''; ?>/>
				Center Images
			</label>
		</p>

		<div class="image-container">
			<h4>Images</h4>
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
					<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][posts][]" class="image-id-input" value="<?php echo absint( $post ); ?>"/>
					<br/>
					<div class="button button-primary select-img">Choose Image</div>
					<a href="#" class="delete-image">Delete Image</a>
				</div>
				<?php
				$count++;
			}
			?>
		</div>
		<p>
			<a href="#" class="add-image">Add Image</a>
			<script type="text/html" class="image-template">
				<div class="image">
					<img src="" />
					<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][posts][]" class="image-id-input" value=""/>
					<br/>
					<div class="button button-primary select-img">Choose Image</div>
					<div class="delete-image">Delete Image</div>
				</div>
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
		$new['type'] = sanitize_key( $data['type'] );
		$new['title'] = isset( $data['title'] ) ? wp_filter_post_kses( $data['title'] ) : '';
		$new['title_link'] = isset( $data['title_link'] ) ? esc_url_raw( $data['title_link'] ) : '';
		$new['center'] = isset( $data['center'] ) ? 'y' : '';
		$new['posts'] = isset( $data['posts'] ) ? implode( ',', array_map( 'absint', $data['posts'] ) ) : '';

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
	?>
		<div class="module module-issues">
			<?php if ( isset( $data['title'] ) && '' !== trim( $data['title'] ) ) {
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
			} ?>
			<ul <?php if ( isset( $data['center'] ) && 'y' === trim( $data['center'] ) ) echo 'class="center"'; ?>>
			<?php $posts = explode( ',', $data['posts'] );
				foreach ( $posts as $post_id ) :
					$att = get_post( $post_id );
					$src = wp_get_attachment_image_src( $post_id, 'portrait' );
				?>
				<li>
					<?php if ( $link = get_post_meta( $post_id, 'emmis_image_link', true ) ) : ?>
					<a href="<?php echo esc_url( $link ); ?>">
					<?php endif; ?>
						<img src="<?php echo esc_url( $src[0] ); ?>" alt="<?php echo esc_attr( $att->post_excerpt ); ?>" />
						<span><?php echo esc_html( $att->post_excerpt ); ?></span>
					<?php if ( $link ) : ?>
					</a>
					<?php endif; ?>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>

	<?php
	}

}

/**
 * Add URL fields to media uploader
 *
 * @param array $form_fields Fields to include in attachment form
 * @param object $post Attachment record in database
 * @return array
 */
function emmis_attachment_image_link( $form_fields, $post ) {
	$form_fields['emmis-image-link'] = array(
		'label' => 'Image Link',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'emmis_image_link', true ),
		'helps' => 'Used in Images module',
	);

	return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'emmis_attachment_image_link', 10, 2 );

/**
 * Save value of Image Link in media uploader
 *
 * @param array $post The post data for database
 * @param array $attachment Attachment fields from $_POST form
 * @return array
 */
function emmis_attachment_field_link_save( $post, $attachment ) {
	if ( isset( $attachment['emmis-image-link'] ) && '' !== trim( $attachment['emmis-image-link'] ) ) {
		update_post_meta( $post['ID'], 'emmis_image_link', esc_url_raw( $attachment['emmis-image-link'] ) );
	}

	return $post;
}
add_filter( 'attachment_fields_to_save', 'emmis_attachment_field_link_save', 10, 2 );
