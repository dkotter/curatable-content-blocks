<?php
class Sample_Content_Block_No_Settings extends Tenup_Content_Block {
	public static function settings_form( $data, $area, $iterator = 0 ) {
		?>
		<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="no-settings" />
		<?php
	}

	public static function clean_data( $data ) {
		// no settings, just return the type directly
		return array( 'type' => 'no-settings' );
	}

	public static function display( $data, $area ) {
		// Any HTML display you want goes here - maybe something like a Twitter widget.
	}
}
