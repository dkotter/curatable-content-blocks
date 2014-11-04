<?php

/*
 * Class CCB_Content_Block_Widget
 */
class CCB_Content_Block_Widget extends WP_Widget {

	/**
	 * Type of content block. Will match a string in the registered content block array
	 *
	 * @var string
	 */
	protected $_content_block_type;

	/**
	 * Register widget
	 *
	 * @param string $type Type of block
	 */
	function __construct( $type ) {
		$registered_blocks = ccb_get_registered_content_blocks();
		$this->_content_block_type = $type;

		// not a registered block!
		if ( ! isset( $registered_blocks[ $this->_content_block_type ] ) ) {
			return;
		}

		$name = $registered_blocks[ $this->_content_block_type ]['name'];

		parent::__construct( 'ccb_content_block_' . $type, $name, array( 'description' => 'A widget for the ' . $name . ' content block' ) );
	}

	/*
	 * Display the widget
	 *
	 * @param array $args Arguments for this widget.
	 * @param array $instance Data saved in widget.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$block = isset( $instance['data'] ) ? $instance['data'] : false;

		if ( function_exists( 'ccb_display_block' ) && false !== $block ) {
			ccb_display_block( $block['type'], $block, 'widget' );
		}
	}

	/*
	 * Output the widget form.
	 *
	 * @param array $instance Instance of this widget.
	 * @return void
	 */
	public function form( $instance ) {
		$registered_blocks = ccb_get_registered_content_blocks();

		$data = isset( $instance['data'] ) ? $instance['data'] : array();

		if ( isset( $registered_blocks[ $this->_content_block_type ] ) && is_callable( array( $registered_blocks[ $this->_content_block_type ]['class'], 'settings_form' ) ) ) {
			$registered_blocks[ $this->_content_block_type ]['class']::settings_form( $data, 'widget', 0, 0 );
		}
	}

	/*
	 * Save the widget data.
	 *
	 * @param array $new_instance New widget instance.
	 * @param array $old_instance Original widget instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		if ( ! isset( $_POST['ccb_content_blocks'] ) ) {
			return $old_instance;
		}

		$data = $_POST['ccb_content_blocks'][0]['widget'][0][0];

		$registered_blocks = ccb_get_registered_content_blocks();

		if ( is_callable( array( $registered_blocks[ $this->_content_block_type ]['class'], 'clean_data' ) ) ) {
			$data = $registered_blocks[ $this->_content_block_type ]['class']::clean_data( $data );
		} else {
			return $old_instance;
		}

		$new_instance['data'] = $data;

		return $new_instance;
	}

}

/*
 * Register widgets for any blocks that have the widget setting as true.
 *
 * @return void
 */
function ccb_register_content_block_widget() {
	$registered_blocks = ccb_get_registered_content_blocks();

	/*
	 * Yeah, this looks scary and dangerous because it uses eval() - All variable values passed to eval() are hard coded
	 * so anything here would already be inside of php that could be executed anyways. Further, the values used here are
	 * passed through sanitize functions, so if it were php code that somehow made it here, it will be messed up by
	 * those anyways
	 *
	 * Need to make sure to NEVER filter any of this data.
	 *
	 * Still don't like it? Yeah ok - Lets get this in core https://core.trac.wordpress.org/ticket/20542
	 */
	foreach ( $registered_blocks as $block_type => $args ) {
		if ( false == $args['widget'] ) {
			continue;
		}

		$class_name = $args['class'];

		$widget_class = $class_name . "_Widget";
		// Was in separate var for easy debugging :)
		$eval = "class $widget_class extends CCB_Content_Block_Widget { public function __construct() { parent::__construct( '$block_type' ); } };";
		eval( $eval );

		register_widget( $widget_class );
	}
}
add_action( 'widgets_init', 'ccb_register_content_block_widget', 15 );
