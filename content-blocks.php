<?php
/*
 * Plugin Name: Content Blocks (10up)
 * Description: Curatable content blocks.
 * Author: 10up
 * Version: 0.1
 */

include_once( 'sample-integration.php' );
include_once( 'class-content-block.php' );
include_once( 'class-content-block-widget.php' );

function tenup_register_row( $id, $name, $class, $cols ) {
	global $tenup_content_blocks;
	$tenup_content_blocks->register_row( $id, $name, $class, $cols );
}

function tenup_get_registered_rows( $args = array() ) {
	global $tenup_content_blocks;
	return $tenup_content_blocks->get_rows( $args );
}

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
	private $rows = array();
	private $blocks = array();

	public function __construct() {
		add_action( 'admin_footer', array( $this, 'print_templates' ) );
	}

	public function register_row( $id, $name, $class, $columns ) {
		$id = sanitize_key( $id );
		$name = esc_html( $name );
		$cols = absint( $columns );

		$this->rows[ $id ] = array(
			'name'  => $name,
			'class' => $class,
			'cols'  => $cols
		);
	}

	public function register( $id, $name, $class, $args = array() ) {
		// should there be something to protect or warn against using the same ID?
		$defaults = array(
			'widget' => false, // Default to NOT creating a widget for this thing!
		);
		$args = wp_parse_args( $args, $defaults );

		$id = sanitize_key( $id );
		$name = esc_html( $name );

		// if class doesn't exist, try loading from plugin
		if ( ! class_exists( $class ) ) {
			if ( ! include_once( 'blocks/' . $id . '.php' ) ) {
				return false;
			}
		}

		$this->blocks[ $id ] = array(
			'name' => $name,
			'class' => $class,
			'widget' => (bool) $args['widget'],
		);
	}

	public function get_rows( $args = array() ) {
		$rows = $this->rows;

		if ( isset( $args['include'] ) && ! empty( $args['include'] ) ) {
			$include = (array) $args['include'];
			$include = array_flip( $include );
			$rows = array_intersect_key( $this->rows, $include );
		} elseif( isset( $args['exclude'] ) && ! empty( $args['exclude'] ) ) {
			$exclude = (array) $args['exclude'];
			$exclude = array_flip( $exclude );
			$rows = array_diff_key( $this->rows, $exclude );
		}

		return $rows;
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
		if ( ( 'post' === $screen->base && post_type_supports( $screen->post_type, 'tenup-content-blocks' ) ) || 'widgets' === $screen->base ) {
			// Print out JS template for each block type
			foreach( tenup_get_registered_content_blocks() as $id => $block ) {
				if ( is_callable( array( $block['class'], 'js_template' ) ) ) {
					echo $block['class']::js_template( $id, $block['name'] );
				}
			}

			// Print out JS template for each row type
			foreach ( tenup_get_registered_rows() as $id => $row ) {
				echo Tenup_Content_Block::js_row_template( $id, $row['name'], $row['class'], $row['cols'] );
			}
		}
	}
}

$tenup_content_blocks = new Tenup_Content_Blocks;
