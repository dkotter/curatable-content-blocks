<?php
if ( ! class_exists( 'Tenup_Content_Block' ) ) {
	return;
}

class Sample_Content_Block_HTML extends Tenup_Content_Block {

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
		<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="html" />

		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]">Title (or <a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>" target="_blank">image URL</a>)</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]" class="widefat" value="<?php echo isset( $data['title'] ) ? esc_attr( $data['title'] ) : ''; ?>"/>
		</p>
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][content]">Content</label>
			<?php
			$editor_content = isset( $data['content'] ) ? $data['content'] : '';
			$editor_id = 'tenup_content_blocks['. esc_attr( $row ) .']['. esc_attr( $area ) .']['. esc_attr( $column ) .']['. esc_attr( $iterator ) .'][content]'; ?>
			<textarea name="<?php echo esc_attr( $editor_id ); ?>" id="<?php echo esc_attr( $editor_id ); ?>" class="ckeditor"><?php echo $editor_content; ?></textarea>
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
		$new['content'] = isset( $data['content'] ) ? wp_filter_post_kses( $data['content'] ) : '';

		return $new;
	}

	public static function display( $data, $area ) {
	?>
		<div class="module module-blanktext entry-content">
			<?php if ( isset( $data['title'] ) && '' !== trim( $data['title'] ) ) : ?>
				<h2 class="h2">
				<?php if ( 'http' === substr( $data['title'], 0, 4 ) || 'www' === substr( $data['title'], 0, 3 ) ) : ?>
					<img src="<?php echo esc_url( $data['title'] ); ?>">
				<?php else : ?>
					<?php echo esc_html( $data['title'] ); ?>
				<?php endif; ?>
				</h2>
			<?php endif; ?>
			<?php
			/*
			 * NOTE: content is already sanitized before save, so currently we trust it.
			 * However, if ever the content is derived from an external source,
			 * something else needs to be done here. kses is heavy and some functions
			 * rely on the user level for allowed tags, but something along those lines
			 * may very well become necessary for security.
			 */
			echo apply_filters( 'the_content', $data['content'] );
			?>
		</div><!-- .module-blanktext -->
	<?php
	}

}

/**
 * Enqueue scripts and styles for the ckeditor.
 *
 * @since 0.1.0
 */
function emmis_ckeditor_scripts_styles() {
	if ( 'page' === get_post_type() ) {
		wp_enqueue_script( 'ckeditor', get_template_directory_uri() . "/assets/js/vendor/ckeditor/ckeditor.js", array(), EMMIS_VERSION, true );
	}
}
add_action( 'admin_enqueue_scripts', 'emmis_ckeditor_scripts_styles' );
