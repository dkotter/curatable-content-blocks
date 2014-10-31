<?php
/**
 * This is a sample of how content blocks were integrated into the
 * editing experience by way of curatable "areas" that contain
 * content blocks. Note that areas a really an integral part of the
 * code, as some routines and JS depend on them, but have not been
 * truly decoupled just yet.
 */
class CCB_Content_Block_Areas {

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
	 * Register content blocks.
	 *
	 * @return void
	 */
	function init() {
		add_action( 'after_setup_theme', array( $this, 'register_rows_blocks' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'post_submitbox_misc_actions', array( $this, 'curated_page_checkbox' ) );
		add_action( 'edit_form_after_title', array( $this, 'output_blocks' ) );
		add_action( 'save_post', array( $this, 'save_curated_page_value' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_blocks' ), 10, 2 );
	}

	/*
	 * Register our default rows and blocks.
	 *
	 * @return void
	 */
	public function register_rows_blocks() {
		// Row registration
		ccb_register_row( 'full', __( 'Full Width Column', 'ccb' ), 'col-1-1', array( 'columns' => 1 ) );
		ccb_register_row( '2-col', __( 'Two Equal Columns', 'ccb' ), 'col-1-2', array( 'columns' => 2 ) );
		ccb_register_row( '3-col', __( 'Three Equal Columns', 'ccb' ), 'col-1-3', array( 'columns' => 3 ) );
		ccb_register_row( '4-col', __( 'Four Equal Columns', 'ccb' ), 'col-1-4', array( 'columns' => 4 ) );
		ccb_register_row( '23-col', __( '2/3 - 1/3 Columns', 'ccb' ), 'col-2-3-1-3', array( 'columns' => 2 ) );
		ccb_register_row( '13-col', __( '1/3 - 2/3 Columns', 'ccb' ), 'col-1-3-2-3', array( 'columns' => 2 ) );

		// Content block registration
		ccb_register_content_block( 'html', __( 'Text/HTML', 'ccb' ), 'CCB_Content_Block_HTML' );
		ccb_register_content_block( 'embeds', __( 'Embeds', 'ccb' ), 'CCB_Embeds_Content_Block' );

		// TO-DO: Remove some of these
		/*ccb_register_content_block( 'ad', 'Ad', 'Emmis_Ad_Content_Block' );
		ccb_register_content_block( 'featured-grid', 'Featured Grid', 'Emmis_Featured_Grid_Content_Block' );
		ccb_register_content_block( 'featured-item', 'Featured Item', 'Emmis_Featured_Item_Content_Block' );
		ccb_register_content_block( 'featured', 'Featured List', 'Emmis_Featured_Items_Content_Block', array( 'widget' => true ) );
		ccb_register_content_block( 'feed', 'Feed', 'Emmis_Feed_Content_Block' );
		ccb_register_content_block( 'gallery', 'Gallery', 'Emmis_Gallery_Content_Block' );
		ccb_register_content_block( 'images', 'Images', 'Emmis_Featured_Images_Content_Block' );
		ccb_register_content_block( 'list', 'Ordered List', 'Emmis_Lists_Content_Block' );
		ccb_register_content_block( 'listing-search', 'Listing Search', 'Emmis_Listing_Search_Content_Block', array( 'widget' => true ) );
		ccb_register_content_block( 'restaurant-search', 'Restaurant Search', 'Emmis_Restaurant_Search_Content_Block', array( 'widget' => true ) );
		ccb_register_content_block( 'newsletter', 'Newsletter', 'Emmis_Newsletter_Content_Block', array( 'widget' => true ) );
		ccb_register_content_block( 'round-about', 'Featured Row', 'Emmis_Round_About_Content_Block' );
		ccb_register_content_block( 'section-header', 'Section Header', 'Emmis_Section_Header_Content_Block' );
		ccb_register_content_block( 'social', 'Social', 'Emmis_Social_Content_Block', array( 'widget' => true ) );
		ccb_register_content_block( 'twitter', 'Twitter Widget', 'Emmis_Twitter_Content_Block' );
		ccb_register_content_block( 'user-favorites', 'User Favorites', 'Emmis_User_Favorites_Content_Block' );*/
	}

	/*
	 * Enqueue the needed scripts and styles.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		$postfix = ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';
		$screen = get_current_screen();

		// Allowed for post, widgets, and any post type with support for 'ccb-content-blocks'
		if ( ( 'post' === $screen->base && post_type_supports( $screen->post_type, 'ccb-content-blocks' ) ) || 'widgets' === $screen->base ) {
			wp_enqueue_script( 'ccb-content-blocks', CCB_URL . "/assets/js/content_blocks{$postfix}.js", array( 'jquery', 'jquery-ui-sortable' ), CCB_VERSION, true );
			//wp_enqueue_script( 'select2', CCB_URL . "/assets/js/vendor/select2{$postfix}.js", array(), '3.5.0' );
			wp_enqueue_style( 'ccb-content-blocks', CCB_URL . "/assets/css/content_blocks{$postfix}.css", array(), CCB_VERSION );
			//wp_enqueue_style( 'select2css', CCB_URL . '/assets/css/select2.css', array(), '3.5.0' );
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
			<label for="ccb-curated-page"><strong><?php esc_html_e( 'Curated Page', 'ccb' ); ?>?</strong>&nbsp;</label>
			<input id="ccb-curated-page" type="checkbox" value="yes" name="ccb-curated-page" <?php checked( 'yes', $curated ); ?> />
		</div>
	<?php
		wp_nonce_field( 'save', 'ccb-save-curated-page' );
	}

	/**
	 * Save the curated page value.
	 *
	 * @param int $post_id Post ID.
	 * @param object $post Post object.
	 * @return void
	 */
	public function save_curated_page_value( $post_id, $post ) {
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

		wp_nonce_field( 'ccb-save-content-blocks', $name = 'ccb_content_blocks_nonce' );
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
								<span class="handle"><img src="<?php echo plugins_url( 'img/drag-handle.png', __FILE__ ); ?>" /></span>
								<a href="#" class="delete-row"><?php esc_html_e( 'Delete', 'ccb' ); ?></a>
							</h3>

							<?php if ( count( (array) $columns ) === $cols ) : ?>
								<?php foreach ( (array) $columns as $column => $blocks ) : ?>
									<div class="block" data-tenup-column="<?php echo esc_attr( $column ); ?>">
										<?php $this->render_blocks( $area, $blocks, $row, $column ); ?>
									</div>
								<?php endforeach; ?>
							<?php else :
								$i = 1; while ( $i <= $cols ) :
									if ( isset( $columns[ $i ] ) ) : ?>
										<div class="block" data-tenup-column="<?php echo esc_attr( $i ); ?>">
											<?php $this->render_blocks( $area, $columns[ $i ], $row, $i ); ?>
										</div>
									<?php else : ?>
										<div class="block" data-tenup-column="<?php echo esc_attr( $i ); ?>">
											<?php $this->render_blocks( $area, '', $row, $i ); ?>
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
	 * Render an individual block.
	 *
	 * @param string $area The name of the area the blocks are a part of.
	 * @param array|string $blocks The blocks saved to this area.
	 * @param int $row The row number.
	 * @param int $column The column number.
	 * @param array $block_args Optional block arguments.
	 * @return void
	 */
	public function render_blocks( $area, $blocks, $row = 0, $column = 1, $block_args = array() ) {
	?>
		<div class="content-blocks sortable">
			<?php
			$i = 0;

			// render the current data
			if ( ! empty( $blocks ) && is_array( $blocks ) ) {
				foreach ( $blocks as $data ) {
					if ( isset( $data['type'] ) ) {
						$this->template( $data['type'], $data, $area, $i, $row, $column );
					}
					$i++;
				}
			}

			$this->adder( $area, $i, $block_args );
			?>
		</div><!-- .content-blocks.sortable -->
	<?php
	}

	/*
	 * Render the template for the block.
	 *
	 * @param string $type The type of block this is.
	 * @param array $data The data saved to this block.
	 * @param string $area The area the block belongs to.
	 * @param int $i The iteration of this block.
	 * @param int $row The row the block belongs to.
	 * @param int $column The column the block belongs to.
	 * @return void
	 */
	private function template( $type, $data, $area, $i, $row = 0, $column = 1 ) {
		$registered_blocks = ccb_get_registered_content_blocks();
		if ( isset( $registered_blocks[ $type ] ) && is_callable( array( $registered_blocks[ $type ]['class'], 'settings_form' ) ) ) {
	?>
			<div class="content-block collapsed <?php echo esc_attr( $data['type'] ); ?>">
				<h4 class="content-block-header">
					<span class="handle"><img src="<?php echo plugins_url( 'img/drag-handle.png', __FILE__ ); ?>" /></span>
					<?php echo esc_html( $registered_blocks[ $type ]['name'] ); ?>
					<a href="#" class="delete-content-block"><?php esc_html_e( 'Delete', 'ccb' ); ?></a>
					<div class="pause">
						<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $i ); ?>][pause]"><?php esc_html_e( 'Pause', 'ccb' ); ?></label>
						<input type="checkbox" id="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $i ); ?>][pause]" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $i ); ?>][pause]" value="y" <?php if ( isset( $data['pause'] ) ) { checked( $data['pause'], 'y' ); } ?>>
					</div><!-- .pause -->
				</h4>

				<div class="interior">
					<?php $registered_blocks[ $type ]['class']::settings_form( $data, $area, $row, $column, $i ); ?>
				</div><!-- .interior -->
			</div><!-- .content-block.collapsed.<?php echo esc_attr( $data['type'] ); ?> -->
	<?php
		}
	}

	/*
	 * Output the Add button.
	 *
	 * @param string $area The name of the area the block belongs to.
	 * @param int $iterator The iteration of the block.
	 * @param array $block_args Optional block arguments.
	 * @return void
	 */
	private function adder( $area, $iterator = 0, $block_args = array() ) {
	?>
	<div class="content-block-adder" data-ccb-area="<?php echo esc_attr( $area ); ?>" data-ccb-iterator="<?php echo esc_attr( $iterator ); ?>">
		<a class="toggle" href="#"><?php esc_html_e( 'Add block', 'ccb' ); ?></a>
		<div class="content-block-select hide-if-js">
			<select name="new_content_block">
				<?php foreach( ccb_get_registered_content_blocks( $block_args ) as $id => $block ) : ?>
					<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $block['name'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<a class="add-content-block button-secondary">Add</a>
		</div><!-- .content-block-select.hide-if-js -->
	</div><!-- .content-block-adder -->
	<?php
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
} // CCB_Content_Block_Areas

global $ccb_content_block_areas;
$ccb_content_block_areas = new CCB_Content_Block_Areas();
