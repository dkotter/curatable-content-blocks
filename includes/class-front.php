<?php
/**
 * Class to handle any front end requirements
 */
class CCB_Front {

	/*
	 * Default constructor
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Run all actions needed.
	 *
	 * @return void
	 */
	function init() {
		add_filter( 'template_include', array( $this, 'load_curated_template' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Load the curated page template.
	 *
	 * @param string $original_template Current template that will be used.
	 * @return string
	 */
	public function load_curated_template( $original_template ) {
		global $post;

		if ( post_type_supports( get_post_type( $post->ID ), 'ccb-content-blocks' ) && 'yes' === get_post_meta( $post->ID, 'ccb_curated_page', true ) ) {
			return ccb_get_template_part( 'curated-page' );
		} else {
			return $original_template;
		}
	}

	/*
	 * Enqueue the needed styles for the front end.
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		global $post;

		$postfix = ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';

		// Allowed for widgets and any post type with support for 'ccb-content-blocks'
		if ( is_singular() && post_type_supports( get_post_type( $post->ID ), 'ccb-content-blocks' ) && 'yes' === get_post_meta( $post->ID, 'ccb_curated_page', true ) ) {
			wp_enqueue_style( 'ccb-content-blocks', CCB_URL . "assets/css/content_blocks{$postfix}.css", array(), CCB_VERSION );
		}
	}

}

$ccb_front = new CCB_Front;
