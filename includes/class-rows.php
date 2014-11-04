<?php
/**
 * Handle registration and grabbing of rows.
 */
class CCB_Rows extends CCB_Template {

	/*
	 * Register a row.
	 *
	 * @param string $id ID for row.
	 * @param string $name Name of row.
	 * @param string $class Class name for row.
	 * @param array $args Optional arguments, like number of columns
	 * @return bool
	 */
	public function register( $id, $name, $class, $args = array() ) {
		$id = sanitize_key( $id );
		$name = esc_html( $name );
		$cols = absint( $args['columns'] );

		$this->rows[ $id ] = array(
			'name'  => $name,
			'class' => $class,
			'cols'  => $cols
		);

		return true;
	}

	/*
	 * Deregister a row.
	 *
	 * @param string $id ID for row.
	 * @return void
	 */
	public function deregister( $id ) {
		if ( array_key_exists( $id, $this->rows ) ) {
			unset( $this->rows[ $id ] );
		}
	}

	/*
	 * Get all registered rows.
	 *
	 * @param array $args Optional arguments
	 * @return array
	 */
	public function get( $args = array() ) {
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

}

global $ccb_rows;
$ccb_rows = new CCB_Rows;
