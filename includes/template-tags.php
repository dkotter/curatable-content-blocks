<?php
/**
 * Various helper functions.
 */

/*
 * Register a row.
 *
 * @param string $id ID of row.
 * @param string $name Name of the row.
 * @param string $class Class name(s) for row.
 * @param int $cols Number of columns row has.
 * @return void
 */
function ccb_register_row( $id, $name, $class, $cols ) {
	global $ccb_rows;
	$ccb_rows->register( $id, $name, $class, $cols );
}

/*
 * Get all registered rows.
 *
 * @param array $args Optional arguments.
 * @return array
 */
function ccb_get_registered_rows( $args = array() ) {
	global $ccb_rows;
	return $ccb_rows->get( $args );
}

/*
 * Register a content block.
 *
 * @param string $id ID of block.
 * @param string $name Name of block.
 * @param string $class Class name of block.
 * @param array $args Optional arguments.
 * @return void
 */
function ccb_register_content_block( $id, $name, $class, $args = array() ) {
	global $ccb_blocks;
	$ccb_blocks->register( $id, $name, $class, $args );
}

/*
 * Get all registered content blocks.
 *
 * @param array $args Optional arguments.
 * @return array
 */
function ccb_get_registered_content_blocks( $args = array() ) {
	global $ccb_blocks;
	return $ccb_blocks->get( $args );
}

/*
 * Display an individual block.
 *
 * @param string $type Type of block to display.
 * @param array $block Data saved within block.
 * @param string $area Current area block is in.
 * @return bool
 */
function ccb_display_block( $type, $block, $area ) {
	$blocks = ccb_get_registered_content_blocks();

	// not a registered block
	if ( ! isset( $blocks[ $type ] ) ) {
		return false;
	}

	// Don't show paused blocks
	if ( isset( $block['pause'] ) && 'y' === $block['pause'] ) {
		return false;
	}

	// allow for overrides, e.g. in a child theme
	if ( function_exists( 'ccb_content_block_display_' . $type ) ) {
		call_user_func( 'ccb_content_block_display_' . $type, $type, $block, $area );
	} elseif ( is_callable( array( $blocks[ $type ]['class'], 'display' ) ) ) {
		$blocks[ $type ]['class']::display( $block, $area );
	}

	return true;
}

/**
 * Output curated rows and their associated blocks.
 *
 * @param array $rows Rows to output.
 * @return void
 */
function ccb_display_rows( $rows ) {
	if ( ! function_exists( 'ccb_display_block' ) ) {
		return;
	}

	$rows = (array) $rows;

	if ( ! empty( $rows ) ) {
		foreach ( (array) $rows as $row => $areas ) {
			foreach ( (array) $areas as $area => $columns ) {
				$registered_rows = ccb_get_registered_rows();
				if ( isset( $registered_rows[ $area ] ) ) : ?>

					<?php
					/**
					 * Fires before an individual row is rendered.
					 *
					 * @since 0.1.0
					 */
					do_action( 'ccb_before_row' ); ?>

					<div class="row">

						<?php foreach ( (array) $columns as $column => $blocks ) : ?>
							<?php if ( is_array( $blocks ) ) : ?>
								<?php if ( 'col-2-3-1-3' === $registered_rows[ $area ]['class'] ) {
									if ( 1 === $column ) {
										$class = 'col-2-3';
									} else {
										$class = 'col-1-3';
									}
								} elseif ( 'col-1-3-2-3' === $registered_rows[ $area ]['class'] ) {
									if ( 1 === $column ) {
										$class = 'col-1-3';
									} else {
										$class = 'col-2-3';
									}
								} else {
									$class = $registered_rows[ $area ]['class'];
								} ?>

								<?php
								/**
								 * Fires before an individual block is rendered.
								 *
								 * @since 0.1.0
								 */
								do_action( 'ccb_before_block' ); ?>

								<?php
								/*
								 * Filter the class name(s) used for an individual block.
								 *
								 * @since 0.1.0
								 *
								 * @param string $class Current class names.
								 */
								?>
								<div class="<?php echo esc_attr( apply_filters( 'ccb_block_class', $class ) ); ?>">
									<?php foreach ( $blocks as $data ) {
										if ( isset( $data['type'] ) ) {
											ccb_display_block( $data['type'], $data, $area );
										}
									} ?>
								</div><!-- .<?php echo esc_attr( $class ); ?> -->

								<?php
								/**
								 * Fires after an individual block is rendered.
								 *
								 * @since 0.1.0
								 */
								do_action( 'ccb_after_block' ); ?>
							<?php endif; ?>
						<?php endforeach; ?>

					</div><!-- .row -->

					<?php
					/**
					 * Fires after an individual row is rendered.
					 *
					 * @since 0.1.0
					 */
					do_action( 'ccb_after_row' ); ?>

				<?php endif;
			}
		}
	}
}
