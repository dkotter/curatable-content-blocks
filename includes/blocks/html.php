<?php
if ( ! class_exists( 'CCB_Content_Block' ) ) {
	return;
}

class CCB_Content_Block_HTML extends CCB_Content_Block {

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
		<input type="hidden" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="html" />

		<?php
		/**
		 * Fires before other fields are rendered.
		 *
		 * Allows easy addition of other fields.
		 *
		 * @since 0.1.0
		 */
		do_action( 'ccb_settings_form_html' );
		?>

		<p>
			<label for="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]"><?php esc_html_e( 'Title', 'ccb' ); ?></label>
			<input type="text" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]" class="widefat" value="<?php echo isset( $data['title'] ) ? esc_attr( $data['title'] ) : ''; ?>"/>
		</p>
		<p>
			<label for="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][content]"><?php esc_html_e( 'Content', 'ccb' ); ?></label>
			<?php
			$editor_content = isset( $data['content'] ) ? $data['content'] : '';
			$editor_id = 'ccb_content_blocks['. esc_attr( $row ) .']['. esc_attr( $area ) .']['. esc_attr( $column ) .']['. esc_attr( $iterator ) .'][content]'; ?>
			<textarea name="<?php echo esc_attr( $editor_id ); ?>" id="<?php echo esc_attr( $editor_id ); ?>" class="widefat"><?php echo esc_textarea( $editor_content ); ?></textarea>
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

		$new['pause']   = isset( $data['pause'] ) ? 'y' : '';
		$new['type']    = 'html';
		$new['title']   = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '';
		$new['content'] = isset( $data['content'] ) ? wp_filter_post_kses( $data['content'] ) : '';

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
		$new = apply_filters( 'ccb_clean_data_html', $new, $data );

		return $new;
	}

	/*
	 * Display the block.
	 *
	 * @param array $data Data saved in block.
	 * @param string $area Area block is in.
	 * @return void
	 */
	public static function display( $data, $area ) {
		/*
		 * Fires before the module markup is output.
		 *
		 * @since 0.1.0
		 */
		do_action( 'ccb_before_module_html' );
	?>
		<div class="module module-blanktext">
			<?php if ( isset( $data['title'] ) && '' !== trim( $data['title'] ) ) : ?>
				<h2>
					<?php echo esc_html( $data['title'] ); ?>
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
		</div><!-- .module.module-blanktext -->
	<?php
		/*
		 * Fires after the module markup is output.
		 *
		 * @since 0.1.0
		 */
		do_action( 'ccb_after_module_html' );
	}

}
