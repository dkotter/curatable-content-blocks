<?php
/**
 * Class to handle all custom meta boxes
 */
class CCB_Meta_Boxes {

	/**
	 * Tracks if we are currently inside of the save function, to avoid infinite loops
	 *
	 * @var bool
	 */
	protected $_saving = false;

	/**
	 * Tracks if the clean_post methods have already been called for this request. Sometimes this was processing more
	 * than once if wp_update_post or similar was called in another plugin / part of the theme
	 *
	 * @var bool
	 */
	protected $_saved = false;

	/*
	 * Default constructor
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Register all needed actions.
	 *
	 * @return void
	 */
	function init() {
		// Enqueue scripts and styles needed for rows/blocks
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Mark an item as being a curated page and save that value
		add_action( 'post_submitbox_misc_actions', array( $this, 'curated_page_checkbox' ) );
		add_action( 'save_post', array( $this, 'save_curated_page_value' ) );

		// Output rows/blocks and save rows/blocks
		add_action( 'edit_form_after_title', array( $this, 'output_blocks' ) );
		add_action( 'save_post', array( $this, 'save_blocks' ), 10, 2 );
	}

	/*
	 * Enqueue the needed scripts and styles.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		$postfix = ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';
		$screen = get_current_screen();

		// Allowed for widgets and any post type with support for 'ccb-content-blocks'
		if ( ( 'post' === $screen->base && post_type_supports( $screen->post_type, 'ccb-content-blocks' ) ) || 'widgets' === $screen->base ) {
			wp_enqueue_script( 'ccb-content-blocks', CCB_URL . "assets/js/content_blocks{$postfix}.js", array( 'jquery', 'jquery-ui-sortable' ), CCB_VERSION, true );
			wp_enqueue_style( 'ccb-content-blocks-admin', CCB_URL . "assets/css/content_blocks_admin.css", array(), CCB_VERSION );
		}
	}

	/**
	 * Add an input to mark a page as curated.
	 *
	 * @return void
	 */
	public function curated_page_checkbox() {
		global $post;

		if ( ! post_type_supports( get_post_type( $post ), 'ccb-content-blocks' ) ) {
			return;
		}

		$curated = ( 'yes' === get_post_meta( get_the_ID(), 'ccb_curated_page', true ) ) ? 'yes' : '';
	?>
		<div id="curated-page" class="misc-pub-section">
			<label for="ccb-curated-page"><strong><?php esc_html_e( apply_filters( 'ccb_curated_checkbox_text', 'Curated Page' ), 'ccb' ); ?>?</strong>&nbsp;</label>
			<input id="ccb-curated-page" type="checkbox" value="yes" name="ccb-curated-page" <?php checked( 'yes', $curated ); ?> />
		</div>
	<?php
		wp_nonce_field( 'save', 'ccb-save-curated-page' );
	}

	/**
	 * Save the curated page value.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_curated_page_value( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['ccb-save-curated-page'] ) || ! wp_verify_nonce( $_POST['ccb-save-curated-page'], 'save' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['ccb-curated-page'] ) && 'yes' === $_POST['ccb-curated-page'] ) {
			update_post_meta( $post_id, 'ccb_curated_page', 'yes' );
		} else {
			delete_post_meta( $post_id, 'ccb_curated_page' );
		}
	}

	/*
	 * Output blocks (if there are any) and row adder.
	 *
	 * @return bool
	 */
	public function output_blocks( $post ) {
		if ( ! post_type_supports( get_post_type( $post ), 'ccb-content-blocks' ) ) {
			return false;
		}

		$rows = get_post_meta( $post->ID, 'ccb_content_blocks', true );

		wp_nonce_field( 'ccb-save-content-blocks', 'ccb_content_blocks_nonce' );
	?>

		<div class="content-blocks-wrapper sortable">
	<?php
			if ( ! empty( $rows ) ) :
				foreach ( (array) $rows as $row => $areas ) :
					foreach ( (array) $areas as $area => $columns ) :
						$registered_rows = ccb_get_registered_rows();
						if ( isset( $registered_rows[ $area ] ) ) :
							$cols = $registered_rows[ $area ]['cols'];
	?>

							<div class="postbox row <?php echo esc_attr( $registered_rows[ $area ]['class'] ); ?>">
								<h3>
									<span class="handle"><img src="<?php echo CCB_URL . 'images/drag-handle.png'; ?>" /></span>
									<?php echo esc_html( $registered_rows[ $area ]['name'] ); ?>
									<a href="#" class="delete-row"><?php esc_html_e( 'Delete', 'ccb' ); ?></a>
								</h3>

								<?php if ( count( (array) $columns ) === $cols ) : ?>
									<?php foreach ( (array) $columns as $column => $blocks ) : ?>
										<div class="block" data-ccb-column="<?php echo esc_attr( $column ); ?>">
											<?php ccb_render_blocks( $area, $blocks, $row, $column ); ?>
										</div>
									<?php endforeach; ?>
								<?php else :
									$i = 1; while ( $i <= $cols ) :
									if ( isset( $columns[ $i ] ) ) : ?>
										<div class="block" data-ccb-column="<?php echo esc_attr( $i ); ?>">
											<?php ccb_render_blocks( $area, $columns[ $i ], $row, $i ); ?>
										</div>
									<?php else : ?>
										<div class="block" data-ccb-column="<?php echo esc_attr( $i ); ?>">
											<?php ccb_render_blocks( $area, '', $row, $i ); ?>
										</div>
									<?php endif; ?>
									<?php $i++; endwhile; ?>
								<?php endif; ?>
							</div><!-- .<?php echo esc_attr( $registered_rows[$area]['class'] ); ?> -->

						<?php
						endif;
					endforeach;
				endforeach;
			endif;
	?>

			<h3 class="ccb-add"><i class="dashicons dashicons-plus"></i> <?php esc_html_e( 'Add New Row', 'ccb' ); ?></h3>
			<div class="ccb-menu postbox">
				<div class="ccb-menu-pane">
					<ul class="ccb-choose-row">
						<?php foreach ( ccb_get_registered_rows() as $id => $row ) : ?>
							<li>
								<a href="#" class="<?php echo esc_attr( $row['class'] ); ?>" data-type="<?php echo esc_attr( $id ); ?>">
									<div class="ccb-item-wrapper"><span class="ccb-item-add-icon"></span></div>
									<div class="ccb-item-description"><?php echo esc_html( $row['name'] ); ?></div>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div><!-- .ccb-menu-pane -->
			</div><!-- .ccb-menu -->
		</div><!-- .content-blocks-wrapper -->

	<?php
		return true;
	}

	/*
	 * Save the content blocks.
	 *
	 * @param int $post_id ID of content blocks are associated with.
	 * @param WP_Post $post Post object
	 */
	public function save_blocks( $post_id, $post ) {
		if ( $this->_saving || $this->_saved ) {
			return;
		}

		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['ccb_content_blocks_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['ccb_content_blocks_nonce'], 'ccb-save-content-blocks' ) ) {
			return;
		}

		// Avoiding infinite loops :)
		$this->_saving = true;

		// use a different key if this is a preview and the post was already published
		// see WP#20299
		if ( ( isset( $_POST['wp-preview'] ) && 'dopreview' === $_POST['wp-preview'] ) && ( isset( $_POST['original_post_status'] ) && 'publish' === $_POST['original_post_status'] ) ) {
			$meta_key = 'ccb_content_blocks_preview';
		} else {
			$meta_key = 'ccb_content_blocks';
		}

		// No content blocks set - delete it and get out
		if ( ! isset( $_POST['ccb_content_blocks'] ) ) {
			delete_post_meta( $post_id, $meta_key );
			$this->_saving = false;
			$this->_saved = true;
			return;
		}

		$registered_blocks = ccb_get_registered_content_blocks();

		$new = array();

		foreach ( (array) $_POST['ccb_content_blocks'] as $row => $areas ) {
			$value = array();

			foreach ( (array) $areas as $area => $columns ) {
				$value[ $area ] = array();

				foreach ( (array) $columns as $column => $blocks ) {
					$value[ $area ][ $column ] = array();

					foreach ( (array) $blocks as $key => $data ) {
						$type = $data['type'];

						// not a registered block
						if ( ! isset( $registered_blocks[ $type ] ) ) {
							continue;
						}

						if ( is_callable( array( $registered_blocks[ $type ]['class'], 'clean_data' ) ) ) {
							$value[ $area ][ $column ][] = $registered_blocks[ $type ]['class']::clean_data( $data );
						}
					}
				}
			}

			$new[ $row ] = $value;
		}

		update_post_meta( $post_id, $meta_key, $new );

		$this->_saving = false;
		$this->_saved = true;
	}

}

$ccb_meta_boxes = new CCB_Meta_Boxes();
