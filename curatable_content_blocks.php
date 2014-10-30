<?php
/**
 * Plugin Name: Curatable Content Blocks
 * Plugin URI:  http://10up.com/
 * Description: Curatable content blocks and page builder.
 * Version:     0.1.0
 * Author:      Darin Kotter
 * Author URI:
 * License:     GPLv2+
 * Text Domain: ccb
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2014 Darin Kotter (email : darin@10up.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using grunt-wp-plugin
 * Copyright (c) 2013 10up, LLC
 * https://github.com/10up/grunt-wp-plugin
 */

// Useful global constants
define( 'CCB_VERSION', '0.1.0' );
define( 'CCB_URL',     plugin_dir_url( __FILE__ ) );
define( 'CCB_PATH',    dirname( __FILE__ ) . '/' );

/**
 * Wrapper to initiate plugin functionality.
 */
class CCB_Init {

	/*
	 * Default constructor
	 */
	public function __construct() {
		// Classes that have functions to register blocks/rows
		require_once( CCB_PATH . 'includes/class-template.php' );
		require_once( CCB_PATH . 'includes/class-rows.php' );
		require_once( CCB_PATH . 'includes/class-blocks.php' );

		// Default content block class
		require_once( CCB_PATH . 'includes/class-content-block.php' );

		// Convert a block into a widget
		require_once( CCB_PATH . 'includes/class-content-block-widget.php' );

		// Useful functions
		require_once( CCB_PATH . 'includes/template-tags.php' );

		// All functionality related to adding/saving content blocks and rows.
		require_once( CCB_PATH . 'includes/content-block-areas.php' );
	}

	/**
	 * Initialization routine for adding text domains and such.
	 */
	public function initialize() {
		$ccb_template = new CCB_Template;
		$ccb_template->init();

		$locale = apply_filters( 'plugin_locale', get_locale(), 'ccb' );
		load_textdomain( 'ccb', WP_LANG_DIR . '/ccb/ccb-' . $locale . '.mo' );
		load_plugin_textdomain( 'ccb', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}
$ccb_init = new CCB_Init();

add_action( 'init', array( $ccb_init, 'initialize' ) );
