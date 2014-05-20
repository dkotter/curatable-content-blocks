<?php

class Tenup_Content_Block_Widget extends WP_Widget {

	/**
	 * Type of content block. Will match a string in the registered content block array
	 *
	 * @var string
	 */
	protected $_content_block_type;

	/**
	 * Register widget
	 */
	function __construct( $type ) {
		$registered_blocks = tenup_get_registered_content_blocks();
		$this->_content_block_type = $type;

		// not a registered block!
		if ( ! isset( $registered_blocks[ $this->_content_block_type ] ) ) {
			return;
		}

		$name = $registered_blocks[ $this->_content_block_type ]['name'];

		parent::__construct( 'tenup_content_block_' . $type, $name, array( 'description' => 'A widget for the ' . $name . ' content block' ) );
	}

	public function widget( $args, $instance ) {
		$block = isset( $instance['data'] ) ? $instance['data'] : false;

		if ( function_exists( 'tenup_display_block' ) && false !== $block ) {
			tenup_display_block( $block['type'], $block, 'widget' );
		}
	}

	public function form( $instance ) {
		$registered_blocks = tenup_get_registered_content_blocks();

		$data = isset( $instance['data'] ) ? $instance['data'] : array();

		if ( isset( $registered_blocks[ $this->_content_block_type ] ) && is_callable( array( $registered_blocks[ $this->_content_block_type ]['class'], 'settings_form' ) ) ) {
			$registered_blocks[ $this->_content_block_type ]['class']::settings_form( $data, 'widget', 0 );
		}
	}

	public function update( $new_instance, $old_instance ) {
		if ( ! isset( $_POST['tenup_content_blocks'] ) ) {
			return $old_instance;
		}

		$data = $_POST['tenup_content_blocks']['widget'][0];

		$registered_blocks = tenup_get_registered_content_blocks();

		if ( is_callable( array( $registered_blocks[ $this->_content_block_type ]['class'], 'clean_data' ) ) ) {
			$data = $registered_blocks[ $this->_content_block_type ]['class']::clean_data( $data );
		} else {
			return $old_instance;
		}

		$new_instance['data'] = $data;

		return $new_instance;
	}

}

function tenup_register_content_block_widget() {
	$registered_blocks = tenup_get_registered_content_blocks();

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
		$eval = "class $widget_class extends Tenup_Content_Block_Widget { public function __construct() { parent::__construct( '$block_type' ); } };";
		eval( $eval );

		register_widget( $widget_class );
	}
}
add_action( 'widgets_init', 'tenup_register_content_block_widget', 15 );
