<?php

abstract class Tenup_Content_Block {
	public function __construct() {
		$this->add_hooks();
	}

	protected static function add_hooks() {
		return false;
	}

	public static function settings_form( $data, $area, $iterator = 0 ) {
		return false;
	}

	public static function js_row_template( $id, $name, $class, $columns ) {
		global $post, $sample_content_block_areas;

		if ( $post ) :
			$blocks = get_post_meta( $post->ID, 'tenup_content_blocks', true );
		?>
			<script type="text/html" id="tmpl-tenup-cb-<?php echo esc_attr( $id ); ?>">
				<div class="postbox row new <?php echo esc_attr( $class ); ?>">
					<h3>
						<span class="handle"><img src="<?php echo plugins_url( 'img/drag-handle.png', __FILE__ ); ?>" /></span>
						<input type="text" name="<?php echo esc_attr( $id ); ?>-{{{row}}}" value="<?php echo esc_attr( $name ); ?>">
						<a href="#" class="delete-row">Delete</a>
					</h3>
					<?php for ( $i = 1; $i <= $columns; $i++ ) : ?>
						<div class="block" data-tenup-column="<?php echo esc_attr( $i ); ?>">
							<?php $sample_content_block_areas->edit_blocks( $id, $blocks ); ?>
						</div>
					<?php endfor; ?>
				</div><!-- .<?php echo esc_attr( $class ); ?> -->
			</script>
		<?php
		endif;
	}

	public static function js_template( $id, $name ) {
		global $post, $sample_content_block_areas;

		if ( $post ) :
			echo '<script type="text/html" id="tmpl-tenup-cb-' .  esc_attr( $id ) . '">';
		?>
			<div class="content-block <?php echo esc_attr( $id ); ?>">
				<h4 class="content-block-header">
					<span class="handle"><img src="<?php echo plugins_url( 'img/drag-handle.png', __FILE__ ); ?>" /></span>
					<?php echo esc_html( $name ); ?>
					<a href="#" class="delete-content-block">Delete</a>
				</h4>

				<div class="interior">
					<?php
					echo static::settings_form( array(), '{{{area}}}', '{{{row}}}', '{{{column}}}', '{{{iterator}}}' );
					?>
				</div>
			</div>
			<?php
			echo '</script>';
		endif;
	}

	public static function clean_data( $data ) {
		return null;
	}

	public static function display( $data, $area ) {
		return null;
	}
}
