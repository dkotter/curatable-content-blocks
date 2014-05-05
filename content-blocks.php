<?php
/*
 * Plugin Name: Content Blocks
 * Description: Curatable content blocks.
 * Author: 10up
 * Version: 0.1
 */

include_once( 'class-mtm-content-block-areas.php' );
include_once( 'class-mtm-content-block.php' );
include_once( 'widget-mtm-content-blocks.php' );

function tenup_register_content_block( $id, $name, $class, $args = array() ) {
	global $tenup_content_blocks;
	$tenup_content_blocks->register( $id, $name, $class, $args );
}

function tenup_get_registered_content_blocks( $args = array() ) {
	global $tenup_content_blocks;
	return $tenup_content_blocks->get( $args );
}

function tenup_display_block( $type, $block, $area ) {
	$blocks = tenup_get_registered_content_blocks();

	// not a registered block
	if ( ! isset( $blocks[ $type ] ) ) {
		return false;
	}

	// allow for overrides, e.g. in a child theme
	if ( function_exists( 'tenup_content_block_display_' . $type ) ) {
		call_user_func( 'tenup_content_block_display_' . $type, $type, $block, $area );
	} elseif ( is_callable( array( $blocks[ $type ]['class'], 'display' ) ) ) {
		$blocks[ $type ]['class']::display( $block, $area );
	}
}

class Tenup_Content_Blocks {
	private $blocks = array();

	public function __construct() {
		add_action( 'admin_footer', array( $this, 'print_templates' ) );
	}

	public function register( $id, $name, $class, $args = array() ) {
		// should there be something to protect or warn against using the same ID?
		$defaults = array(
			'widget' => false, // Default to NOT creating a widget for this thing!
		);
		$args = wp_parse_args( $args, $defaults );

		$id = sanitize_key( $id );
		$name = esc_html( $name );

		// load files for classes
		if ( ! include_once( 'blocks/' . $id . '.php' ) ) {
			return false;
		}

		if ( ! class_exists( $class ) ) {
			return false;
		}

		$this->blocks[ $id ] = array(
			'name' => $name,
			'class' => $class,
			'widget' => (bool) $args['widget'],
		);
	}

	public function get( $args = array() ) {
		$blocks = $this->blocks;

		if ( isset( $args['include'] ) && ! empty( $args['include'] ) ) {
			$include = (array) $args['include'];
			$include = array_flip( $include );
			$blocks = array_intersect_key( $this->blocks, $include );
		} elseif( isset( $args['exclude'] ) && ! empty( $args['exclude'] ) ) {
			$exclude = (array) $args['exclude'];
			$exclude = array_flip( $exclude );
			$blocks = array_diff_key( $this->blocks, $exclude );
		}

		return $blocks;
	}

	/**
	 * Echo JS templates in the footer.
	 */
	public function print_templates() {
		$screen = get_current_screen();
		if ( 'post' === $screen->base || 'widgets' === $screen->base || post_type_supports( $screen->post_type, 'tenup-content-blocks' ) ) {
			// Print out JS template for each block type
			foreach( tenup_get_registered_content_blocks() as $id => $block ) {
				if ( is_callable( array( $block['class'], 'js_template' ) ) ) {
					echo $block['class']::js_template( $id, $block['name'] );
				}
			}
		}
	}
}

$tenup_content_blocks = new Tenup_Content_Blocks;
