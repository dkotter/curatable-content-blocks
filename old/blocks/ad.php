<?php
if ( ! class_exists( 'Tenup_Content_Block' ) ) {
	return;
}

class Emmis_Ad_Content_Block extends Tenup_Content_Block {

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
		global $ad_code_manager;
		$size = isset( $data['size'] ) ? $data['size'] : '';
	?>
		<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="ad" />
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][size]">Select ad size</label>
			<select name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][size]">
				<?php $ads = $ad_code_manager->get_ad_codes(); ?>
				<?php foreach ( $ads as $ad ) : ?>
					<?php $tag_name = $ad['url_vars']['tag'] . '::' . $ad['url_vars']['tag_name'];?>
					<option value="<?php echo esc_attr( $tag_name ); ?>" <?php selected( $tag_name, $size ); ?>>
						<?php
						echo esc_html( $ad['url_vars']['tag'] );
						if ( isset( $ad['url_vars']['tag_name'] ) && '' !== trim( $ad['url_vars']['tag_name'] ) ) {
							echo ' (' . esc_html( $ad['url_vars']['tag_name'] ) . ')';
						}
						?>
					</option>
				<?php endforeach; ?>
			</select>
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
		$new['size'] = isset( $data['size'] ) ? sanitize_text_field( $data['size'] ) : '';

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
		$size = explode( '::', $data['size'] );
		if ( in_array( $size[0], array( '728x90', 'horizontal-flex', 'horizontal-flex-2' ) ) ) {
			$classes = 'module module-pencil-ad pencil-ad';
		} else if ( 'Sticky Flex' === $size[0] ) {
			$classes = 'module module-ad sticky-ad';
		} else {
			$classes = 'module module-ad';
		}

		echo '<div class="'. esc_attr( $classes ) .'">';
		do_action( 'acm_tag', $size[0] );
		echo '</div><!-- .module-ad -->';
	}

}
