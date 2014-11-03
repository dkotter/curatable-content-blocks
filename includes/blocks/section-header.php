<?php
if ( ! class_exists( 'CCB_Content_Block' ) ) {
	return;
}

class CCB_Section_Header_Content_Block extends CCB_Content_Block {

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
		<input type="hidden" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="section-header" />

		<?php
		/**
		 * Fires before other fields are rendered.
		 *
		 * Allows easy addition of other fields.
		 *
		 * @since 0.1.0
		 */
		do_action( 'ccb_settings_form_section-header' );
		?>

		<p>
			<label for="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][content]"><?php esc_html_e( 'Header Text or Image', 'ccb' ); ?></label>
			<?php
			$editor_content = isset( $data['content'] ) ? $data['content'] : '';
			$editor_id = 'ccb_content_blocks['. esc_attr( $row ) .']['. esc_attr( $area ) .']['. esc_attr( $column ) .']['. esc_attr( $iterator ) .'][content]'; ?>
			<?php wp_editor( $editor_content, $editor_id, array( 'textarea_rows' => 5, 'tinymce' => false, 'media_buttons' => false, 'wpautop' => false, 'quicktags' => array( 'buttons' => 'strong,em,block,del,ins,img,ul,ol,li,code,more,close' ) ) ); ?>
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
		$new['type']    = 'section-header';
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
		$new = apply_filters( 'ccb_clean_data_section-header', $new, $data );

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
		/*
		 * Fires before the module markup is output.
		 *
		 * @since 0.1.0
		 */
		do_action( 'ccb_before_module_section-header' );
	?>
		<div class="module module-header">
			<?php if ( isset( $data['content'] ) && '' !== trim( $data['content'] ) ) : ?>
				<h2>
					<?php echo apply_filters( 'the_content', $data['content'] ); ?>
				</h2>
			<?php endif; ?>
		</div><!-- .module.module-header -->
	<?php
		/*
		 * Fires after the module markup is output.
		 *
		 * @since 0.1.0
		 */
		do_action( 'ccb_after_module_section-header' );
	}

}
