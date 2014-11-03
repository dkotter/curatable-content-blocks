<?php
if ( ! class_exists( 'CCB_Content_Block' ) ) {
	return;
}

class CCB_Twitter_Content_Block extends CCB_Content_Block {

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
		<input type="hidden" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="twitter" />

		<?php
		/**
		 * Fires before other fields are rendered.
		 *
		 * Allows easy addition of other fields.
		 *
		 * @since 0.1.0
		 */
		do_action( 'ccb_settings_form_twitter' );
		?>

		<p>
			<label for="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]"><?php esc_html_e( 'Title', 'ccb' ); ?></label>
			<input type="text" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]" class="widefat" value="<?php echo isset( $data['title'] ) ? esc_attr( $data['title'] ) : ''; ?>"/>
		</p>
		<p>
			<label for="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][widget_id]"><?php esc_html_e( 'Widget ID', 'ccb' ); ?></label>
			<input type="text" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][widget_id]" class="widefat" value="<?php echo isset( $data['widget_id'] ) ? esc_attr( $data['widget_id'] ) : ''; ?>"/>
		</p>
		<p>
			<label for="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][height]"><?php esc_html_e( 'Height', 'ccb' ); ?></label>
			<input type="text" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][height]" class="widefat" value="<?php echo isset( $data['height'] ) ? esc_attr( $data['height'] ) : ''; ?>"/>
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

		$new['pause']     = isset( $data['pause'] ) ? 'y' : '';
		$new['type']      = 'twitter';
		$new['title']     = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '';
		$new['widget_id'] = isset( $data['widget_id'] ) ? sanitize_text_field( $data['widget_id'] ) : '';
		$new['height']    = isset( $data['height'] ) ? preg_replace( '/[^0-9]/', '', $data['height'] ) : ''; // Gets rid of non-numbers

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
		$new = apply_filters( 'ccb_clean_data_twitter', $new, $data );

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
		do_action( 'ccb_before_module_twitter' );
	?>
		<div class="module module-twitter">
			<?php if ( isset( $data['title'] ) && '' !== trim( $data['title'] ) ) : ?>
				<h2>
					<?php echo esc_html( $data['title'] ); ?>
				</h2>
			<?php endif; ?>
			<a class="twitter-timeline" href="https://twitter.com/" height="<?php echo esc_attr( $data['height'] ); ?>" data-widget-id="<?php echo esc_attr( $data['widget_id'] ); ?>"></a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		</div><!-- .module-twitter -->
	<?php
		/*
		 * Fires after the module markup is output.
		 *
		 * @since 0.1.0
		 */
		do_action( 'ccb_after_module_twitter' );
	}
}
