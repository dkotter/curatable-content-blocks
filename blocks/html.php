<?php
/**
 * This is a sample of a content block; in this case, a freeform
 * textarea that allows some HTML.
 */
class Sample_Content_Block_HTML extends Tenup_Content_Block {
	public static function settings_form( $data, $area, $row = 1, $column = 1, $iterator = 0 ) {
	?>
		<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="html" />

		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][content]">Content</label>
			<textarea name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][content]" class="widefat"><?php if ( isset( $data['content'] ) ) echo esc_textarea( $data['content' ] ); ?></textarea>
		</p>
	<?php
	}

	public static function clean_data( $data ) {
		$new = array();

		$new['type'] = 'html';
		$new['content'] = wp_filter_post_kses( $data['content'] );

		return $new;
	}

	public static function display( $data, $area ) {
?>
<div class="content-block-html">
	<?php
		/*
		 * NOTE: content is already sanitized before save, so currently we trust it.
		 * However, if ever the content is derived from an external source,
		 * something else needs to be done here. kses is heavy and some functions
		 * rely on the user level for allowed tags, but something along those lines
		 * may very well become necessary for security.
		 */
		echo $data['content'];
	?>
</div>
<?php
	}
}
