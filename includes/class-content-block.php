<?php

/**
 * Class CCB_Content_Block
 *
 * Extend this class to make new content blocks.
 * This class cannot be used directly.
 */
abstract class CCB_Content_Block {

	/*
	 * Default constructor.
	 */
	public function __construct() {
	}

	/*
	 * Output the settings form for a block.
	 *
	 * @return false
	 */
	public static function settings_form( $data, $area, $iterator = 0 ) {
		return false;
	}

	/*
	 * Output the javascript row template.
	 *
	 * @param string $id ID of row.
	 * @param string $name Name of row.
	 * @param string $class Class for row.
	 * @param int $columns Number of columns in row.
	 * @return void
	 */
	public static function js_row_template( $id, $name, $class, $columns ) {
		global $post, $ccb_content_block_areas;

		if ( $post ) :
			$blocks = get_post_meta( $post->ID, 'ccb_content_blocks', true );
		?>
			<script type="text/html" id="tmpl-ccb-cb-<?php echo esc_attr( $id ); ?>">
				<div class="postbox row new <?php echo esc_attr( $class ); ?>">
					<h3>
						<span class="handle"><img src="<?php echo CCB_URL . 'images/drag-handle.png'; ?>" /></span>
						<?php echo esc_html( $name ); ?>
						<a href="#" class="delete-row"><?php esc_html_e( 'Delete', 'ccb' ); ?></a>
					</h3>
					<?php for ( $i = 1; $i <= $columns; $i++ ) : ?>
						<div class="block" data-ccb-column="<?php echo esc_attr( $i ); ?>">
							<?php $ccb_content_block_areas->render_blocks( $id, $blocks ); ?>
						</div><!-- .block -->
					<?php endfor; ?>
				</div><!-- .postbox.row.new.<?php echo esc_attr( $class ); ?> -->
			</script>
		<?php
		endif;
	}

	/*
	 * Output the javascript block template.
	 *
	 * @param string $id ID of block.
	 * @param string $name Name of block.
	 * @return void
	 */
	public static function js_block_template( $id, $name ) {
		global $post;

		if ( $post ) :
			echo '<script type="text/html" id="tmpl-ccb-cb-' .  esc_attr( $id ) . '">';
		?>
			<div class="content-block <?php echo esc_attr( $id ); ?>">
				<h4 class="content-block-header">
					<span class="handle"><img src="<?php echo CCB_URL . 'images/drag-handle.png'; ?>" /></span>
					<?php echo esc_html( $name ); ?>
					<a href="#" class="delete-content-block"><?php esc_html_e( 'Delete', 'ccb' ); ?></a>
				</h4>

				<div class="interior">
					<?php
					static::settings_form( array(), '{{{area}}}', '{{{row}}}', '{{{column}}}', '{{{iterator}}}' );
					?>
				</div><!-- .interior -->
			</div><!-- .content-block.<?php echo esc_attr( $id ); ?> -->
			<?php
			echo '</script>';
		endif;
	}

	/*
	 * Data sanitization function.
	 *
	 * @param array $data Data being saved into block.
	 * @return null
	 */
	public static function clean_data( $data ) {
		return null;
	}

	/*
	 * Display a block.
	 *
	 * @param array $data Data saved in block.
	 * @param string $area Area block is in.
	 * @return null
	 */
	public static function display( $data, $area ) {
		return null;
	}

}
