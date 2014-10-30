<?php
/**
 * class CCB_Template
 */
class CCB_Template {

	/*
	 * Hold registered rows.
	 *
	 * @var array
	 */
	public $rows = array();

	/*
	 * Hold registered blocks.
	 *
	 * @var array
	 */
	public $blocks = array();

	/*
	 * Default constructor
	 */
	public function __construct() {
	}

	/*
	 * Initialize any actions/filters needed.
	 */
	public function init() {
		add_action( 'admin_footer', array( $this, 'print_templates' ) );
	}

	/*
	 * Default register function. Overridden in other classes.
	 */
	public function register( $id, $name, $class, $args = array() ) {
	}

	/*
	 * Default get function. Overridden in other classes.
	 */
	public function get( $args = array() ) {
	}

	/**
	 * Echo JS templates in the footer.
	 */
	public function print_templates() {
		$screen = get_current_screen();
		if ( ( 'post' === $screen->base && post_type_supports( $screen->post_type, 'ccb-content-blocks' ) ) || 'widgets' === $screen->base ) {
			// Print out JS template for each block type
			foreach( ccb_get_registered_content_blocks() as $id => $block ) {
				//if ( is_callable( array( $block['class'], 'js_template' ) ) ) {
					$block['class']::js_block_template( $id, $block['name'] );
				//}
			}

			// Print out JS template for each row type
			foreach ( ccb_get_registered_rows() as $id => $row ) {
				CCB_Content_Block::js_row_template( $id, $row['name'], $row['class'], $row['cols'] );
			}
		}
	}

}
