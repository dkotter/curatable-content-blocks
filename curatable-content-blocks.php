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
		$this->init();
	}

	/**
	 * Load all files we need and register all actions.
	 *
	 * @return void
	 */
	public function init() {
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

		// Output rows/blocks and checkbox to mark an item as a curated page.
		require_once( CCB_PATH . 'includes/class-meta-boxes.php' );

		// All front end functionality
		require_once( CCB_PATH . 'includes/class-front.php' );

		// Post Finder
		require_once( CCB_PATH . 'includes/post-finder/post-finder.php' );

		add_action( 'after_setup_theme', array( $this, 'register_rows_blocks' ) );
		add_action( 'init', array( $this, 'initialize' ) );
	}

	/*
	 * Register our default rows and blocks.
	 *
	 * @return void
	 */
	public function register_rows_blocks() {
		add_post_type_support( 'page', 'ccb-content-blocks' );

		// Row registration
		ccb_register_row( 'full', __( 'Full Width Column', 'ccb' ), 'col-1-1', array( 'columns' => 1 ) );
		ccb_register_row( '2-col', __( 'Two Equal Columns', 'ccb' ), 'col-1-2', array( 'columns' => 2 ) );
		ccb_register_row( '3-col', __( 'Three Equal Columns', 'ccb' ), 'col-1-3', array( 'columns' => 3 ) );
		ccb_register_row( '4-col', __( 'Four Equal Columns', 'ccb' ), 'col-1-4', array( 'columns' => 4 ) );
		ccb_register_row( '23-col', __( '2/3 - 1/3 Columns', 'ccb' ), 'col-2-3-1-3', array( 'columns' => 2 ) );
		ccb_register_row( '13-col', __( '1/3 - 2/3 Columns', 'ccb' ), 'col-1-3-2-3', array( 'columns' => 2 ) );

		// Content block registration
		ccb_register_content_block( 'embeds', __( 'Embeds', 'ccb' ), 'CCB_Embeds_Content_Block' );
		ccb_register_content_block( 'featured-item', __( 'Featured Item', 'ccb' ), 'CCB_Featured_Item_Content_Block' );
		ccb_register_content_block( 'featured-list', __( 'Featured List', 'ccb' ), 'CCB_Featured_Items_Content_Block', array( 'widget' => true ) );
		ccb_register_content_block( 'feed', __( 'Feed', 'ccb' ), 'CCB_Feed_Content_Block' );
		ccb_register_content_block( 'html', __( 'Text/HTML', 'ccb' ), 'CCB_Content_Block_HTML' );
		ccb_register_content_block( 'images', __( 'Images', 'ccb' ), 'CCB_Featured_Images_Content_Block' );
		ccb_register_content_block( 'section-header', __( 'Section Header', 'ccb' ), 'CCB_Section_Header_Content_Block' );
		ccb_register_content_block( 'twitter', __( 'Twitter Widget', 'ccb' ), 'CCB_Twitter_Content_Block' );
	}

	/**
	 * Initialization routine for adding text domains and such.
	 *
	 * @return void
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
