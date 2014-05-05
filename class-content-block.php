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

	public static function js_template( $id, $name ) {
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
				echo static::settings_form( array(), '{{{area}}}', '{{{iterator}}}' );
				?>
			</div>
		</div>
		<?php
		echo '</script>';
	}

	public static function clean_data( $data ) {
		return null;
	}

	public static function display( $data, $area ) {
		return null;
	}
}
