<?php
/**
 * Handle registration and grabbing of blocks.
 */
class CCB_Blocks extends CCB_Template {

	/*
	 * Register a block.
	 *
	 * @param string $id ID for row.
	 * @param string $name Name of row.
	 * @param string $class Class name for row.
	 * @param array $args Optional arguments, like number of columns
	 * @return bool
	 */
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
			'name'   => $name,
			'class'  => $class,
			'widget' => (bool) $args['widget'],
		);

		return true;
	}

	/*
	 * Deregister a block.
	 *
	 * @param string $id ID for row.
	 * @return void
	 */
	public function deregister( $id ) {
		if ( array_key_exists( $id, $this->blocks ) ) {
			unset( $this->blocks[ $id ] );
		}
	}

	/*
	 * Get all registered blocks.
	 *
	 * @param array $args Optional arguments
	 * @return array
	 */
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

}

global $ccb_blocks;
$ccb_blocks = new CCB_Blocks;
