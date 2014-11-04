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
 * @param array $args Optional arguments, like columns.
 * @return void
 */
function ccb_register_row( $id, $name, $class, $args = array() ) {
	global $ccb_rows;
	$ccb_rows->register( $id, $name, $class, $args );
}

/*
 * Deregister a row.
 *
 * @param string $id ID of row.
 * @return void
 */
function ccb_deregister_row( $id ) {
	global $ccb_rows;
	$ccb_rows->deregister( $id );
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
 * Deregister a content block.
 *
 * @param string $id ID of row.
 * @return void
 */
function ccb_deregister_block( $id ) {
	global $ccb_blocks;
	$ccb_blocks->deregister( $id );
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

/**
 * Retrieves a template part.
 *
 * Taken mostly from bbPress. Will either load
 * the template or return the path.
 *
 * @since 0.1.0
 *
 * @param string $slug The template slug.
 * @param string $name Optional. Template name. Default null
 * @param bool $load Optional. Load template or just return path. Default is to load the template.
 * @return string
 */
function ccb_get_template_part( $slug, $name = null, $load = true ) {
	/**
	 * Fires before the template is found.
	 *
	 * @since 0.1.0
	 *
	 * @param string $slug Slug of template.
	 * @param string $name Name of template.
	 */
	do_action( "ccb_get_template_part_{$slug}", $slug, $name );

	$templates = array();
	if ( isset( $name ) ) {
		$templates[] = $slug . '-' . $name . '.php';
	}
	$templates[] = $slug . '.php';

	/*
	 * Filter the template parts before loading
	 *
	 * @since 0.1.0
	 *
	 * @param array $templates Templates we want to load.
	 * @param string $slug Slug of template to load.
	 * @param string $name Name of template to load.
	 */
	$templates = apply_filters( 'rcp_get_template_part', $templates, $slug, $name );

	// Return the part that is found
	return ccb_locate_template( $templates, $load, false );
}

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
 * inherit from a parent theme can just overload one file. If the template is
 * not found in either of those, it looks in the plugin last.
 *
 * Taken from bbPress
 *
 * @since 0.0.1
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool $load If true the template file will be loaded if it is found.
 * @param bool $require_once Whether to require_once or require. Default true.
 *                           Has no effect if $load is false.
 * @return string The template filename if one is located.
 */
function ccb_locate_template( $template_names, $load = false, $require_once = true ) {
	$located = false;

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) ) {
			continue;
		}

		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );

		// Check child theme first
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $template_name ) ) {
			$located = trailingslashit( get_stylesheet_directory() ) . $template_name;
			break;

			// Check parent theme next
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $template_name ) ) {
			$located = trailingslashit( get_template_directory() ) . $template_name;
			break;

			// Check plugin last
		} elseif ( file_exists( CCB_PATH ) . $template_name ) {
			$located = CCB_PATH . $template_name;
			break;
		}
	}

	if ( ( true === $load ) && ! empty( $located ) ) {
		load_template( $located, $require_once );
	}

	return $located;
}
