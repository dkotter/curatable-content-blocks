<?php
/**
 * This is a sample of how content blocks were integrated into the
 * editing experience by way of curatable "areas" that contain
 * content blocks. Note that areas a really an integral part of the
 * code, as some routines and JS depend on them, but have not been
 * truly decoupled just yet.
 */
class Sample_Content_Block_Areas {

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

	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'edit_form_after_title', array( $this, 'page_blocks' ) );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
	}

	/**
	 * Register content blocks.
	 *
	 * @return void
	 */
	function init() {
		// Row registration
		tenup_register_row( 'full', 'Full Width Column', 'col-1-1', 1 );
		tenup_register_row( '2-col', 'Two Equal Columns', 'col-1-2', 2 );
		tenup_register_row( '3-col', 'Three Equal Columns', 'col-1-3', 3 );
		tenup_register_row( '4-col', 'Four Equal Columns', 'col-1-4', 4 );
		tenup_register_row( '23-col', '2/3 - 1/3 Columns', 'col-2-3-1-3', 2 );
		tenup_register_row( '13-col', '1/3 - 2/3 Columns', 'col-1-3-2-3', 2 );

		// content block registration
		tenup_register_content_block( 'html', 'Text/HTML', 'Sample_Content_Block_HTML' );
		tenup_register_content_block( 'ad', 'Ad', 'Emmis_Ad_Content_Block' );
		tenup_register_content_block( 'embeds', 'Embeds', 'Emmis_Embeds_Content_Block' );
		tenup_register_content_block( 'featured-grid', 'Featured Grid', 'Emmis_Featured_Grid_Content_Block' );
		tenup_register_content_block( 'featured-item', 'Featured Item', 'Emmis_Featured_Item_Content_Block' );
		tenup_register_content_block( 'featured', 'Featured List', 'Emmis_Featured_Items_Content_Block', array( 'widget' => true ) );
		tenup_register_content_block( 'feed', 'Feed', 'Emmis_Feed_Content_Block' );
		tenup_register_content_block( 'gallery', 'Gallery', 'Emmis_Gallery_Content_Block' );
		tenup_register_content_block( 'images', 'Images', 'Emmis_Featured_Images_Content_Block' );
		tenup_register_content_block( 'list', 'Ordered List', 'Emmis_Lists_Content_Block' );
		tenup_register_content_block( 'listing-search', 'Listing Search', 'Emmis_Listing_Search_Content_Block', array( 'widget' => true ) );
		tenup_register_content_block( 'restaurant-search', 'Restaurant Search', 'Emmis_Restaurant_Search_Content_Block', array( 'widget' => true ) );
		tenup_register_content_block( 'newsletter', 'Newsletter', 'Emmis_Newsletter_Content_Block', array( 'widget' => true ) );
		tenup_register_content_block( 'round-about', 'Featured Row', 'Emmis_Round_About_Content_Block' );
		tenup_register_content_block( 'section-header', 'Section Header', 'Emmis_Section_Header_Content_Block' );
		tenup_register_content_block( 'social', 'Social', 'Emmis_Social_Content_Block', array( 'widget' => true ) );
		tenup_register_content_block( 'twitter', 'Twitter Widget', 'Emmis_Twitter_Content_Block' );
		tenup_register_content_block( 'user-favorites', 'User Favorites', 'Emmis_User_Favorites_Content_Block' );
	}

	public function admin_enqueue_scripts() {
		$screen = get_current_screen();

		// Allowed for post, widgets, and any post type with support for 'tenup-content-blocks'
		if ( ( 'post' === $screen->base && post_type_supports( $screen->post_type, 'tenup-content-blocks' ) ) || 'widgets' === $screen->base ) {
			wp_enqueue_script( 'tenup-content-blocks', plugins_url( '/js/content-blocks.js', __FILE__ ), array( 'jquery', 'jquery-ui-sortable' ), false, true );
			wp_enqueue_script( 'select2', plugins_url( '/js/select2.min.js', __FILE__ ), array(), false );
			wp_enqueue_style( 'tenup-content-blocks', plugins_url( '/css/content-blocks.css', __FILE__ ) );
			wp_enqueue_style( 'select2css', plugins_url( '/css/select2.css', __FILE__ ) );
		}
	}

	public function page_blocks( $post ) {
		if ( ! in_array( get_post_type( $post ), array( 'page' ) ) ) {
			return false;
		}

		$rows = get_post_meta( $post->ID, 'tenup_content_blocks', true );

		wp_nonce_field( 'tenup-save-content-blocks', $name = 'tenup_content_blocks_nonce' );
	?>

		<div class="content-blocks-wrapper sortable">
		<?php if ( ! empty( $rows ) ) :
			foreach ( (array) $rows as $row => $areas ) :
				foreach ( (array) $areas as $area => $columns ) :
					$registered_rows = tenup_get_registered_rows();
					if ( isset( $registered_rows[ $area ] ) ) :
						$cols = $registered_rows[ $area ]['cols'];
						$row_name = isset( $areas[ $area ]['row_name'] ) ? $areas[ $area ]['row_name'] : $registered_rows[ $area ]['name'];
	?>

						<div class="postbox row <?php echo esc_attr( $registered_rows[ $area ]['class'] ); ?>">
							<h3>
								<span class="handle"><img src="<?php echo plugins_url( 'img/drag-handle.png', __FILE__ ); ?>" /></span>
								<input type="text" name="<?php echo esc_attr( $area ); ?>-<?php echo esc_attr( $row ); ?>" value="<?php echo esc_attr( $row_name ); ?>">
								<a href="#" class="delete-row">Delete</a>
							</h3>

							<?php unset( $columns['row_name'] ); ?>
							<?php if ( count( (array) $columns ) === $cols ) : ?>
								<?php foreach ( (array) $columns as $column => $blocks ) : ?>
									<div class="block" data-tenup-column="<?php echo esc_attr( $column ); ?>">
										<?php $this->edit_blocks( $area, $blocks, $row, $column ); ?>
									</div>
								<?php endforeach; ?>
							<?php else :
								$i = 1; while ( $i <= $cols ) :
									if ( isset( $columns[ $i ] ) ) : ?>
										<div class="block" data-tenup-column="<?php echo esc_attr( $i ); ?>">
											<?php $this->edit_blocks( $area, $columns[ $i ], $row, $i ); ?>
										</div>
									<?php else : ?>
										<div class="block" data-tenup-column="<?php echo esc_attr( $i ); ?>">
											<?php $this->edit_blocks( $area, '', $row, $i ); ?>
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

			<h3 class="ccb-add"><i class="dashicons dashicons-plus"></i> Add New Row</h3>
			<div class="ccb-menu postbox">
				<div class="ccb-menu-pane">
					<ul class="ccb-choose-row">
					<?php foreach ( tenup_get_registered_rows() as $id => $row ) : ?>
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
	}

	public function edit_blocks( $area, $blocks, $row = 0, $column = 1, $block_args = array() ) {
?>
<div class="content-blocks sortable">
	<?php
		$i = 0;

		// render the current data
		if ( ! empty( $blocks ) && is_array( $blocks ) ) {
			foreach ( $blocks as $data ) {
				if ( isset( $data['type'] ) ) {
					echo $this->template( $data['type'], $data, $area, $i, $row, $column );
				}
				$i++;
			}
		}

		$this->adder( $area, $i, $block_args );
	?>
</div>
<?php
	}

	private function template( $type, $data, $area, $i, $row = 0, $column = 1 ) {
		$registered_blocks = tenup_get_registered_content_blocks();
		if ( isset( $registered_blocks[ $type ] ) && is_callable( array( $registered_blocks[ $type ]['class'], 'settings_form' ) ) ) {
			?>
			<div class="content-block collapsed <?php echo esc_attr( $data['type'] ); ?>">
				<h4 class="content-block-header">
					<span class="handle"><img src="<?php echo plugins_url( 'img/drag-handle.png', __FILE__ ); ?>" /></span>
					<?php echo esc_html( $registered_blocks[ $type ]['name'] ); ?>
					<a href="#" class="delete-content-block">Delete</a>
					<div class="pause">
						<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $i ); ?>][pause]">Pause</label>
						<input type="checkbox" id="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $i ); ?>][pause]" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $i ); ?>][pause]" value="y" <?php isset( $data['pause'] ) ? checked( $data['pause'], 'y' ) : ''; ?>>
					</div>
				</h4>

				<div class="interior">
					<?php
					$registered_blocks[ $type ]['class']::settings_form( $data, $area, $row, $column, $i );
					?>
				</div>
			</div>
			<?php
		}
	}

	private function adder( $area, $iterator = 0, $block_args = array() ) {
?>
<div class="content-block-adder" data-tenup-area="<?php echo esc_attr( $area ); ?>" data-tenup-iterator="<?php echo esc_attr( $iterator ); ?>">
	<a class="toggle" href="#">Add block</a>
	<div class="content-block-select hide-if-js">
		<select name="new_content_block">
			<?php foreach( tenup_get_registered_content_blocks( $block_args ) as $id => $block ) : ?>
				<option value="<?php echo $id; ?>"><?php echo $block['name']; ?></option>
			<?php endforeach; ?>
		</select>
		<a class="add-content-block button-secondary">Add</a>
	</div>
</div>
<?php
	}

	public function save( $post_id, $post ) {
		if ( $this->_saving || $this->_saved ) {
			return;
		}

		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['tenup_content_blocks_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['tenup_content_blocks_nonce'], 'tenup-save-content-blocks' ) ) {
			return;
		}

		// Avoiding infinite loops :)
		$this->_saving = true;

		// use a different key if this is a preview and the post was already published
		// see WP#20299
		if ( ( isset( $_POST['wp-preview'] ) && 'dopreview' === $_POST['wp-preview'] ) && ( isset( $_POST['original_post_status'] ) && 'publish' === $_POST['original_post_status'] ) ) {
			$meta_key = 'tenup_content_blocks_preview';
		} else {
			$meta_key = 'tenup_content_blocks';
		}

		// No content blocks set - delete it and get out
		if ( ! isset( $_POST['tenup_content_blocks'] ) ) {
			delete_post_meta( $post_id, $meta_key );
			$this->_saving = false;
			$this->_saved = true;
			return;
		}

		$registered_blocks = tenup_get_registered_content_blocks();

		$new = array();

		foreach ( (array) $_POST['tenup_content_blocks'] as $row => $areas ) {
			$value = array();

			foreach ( (array) $areas as $area => $columns ) {
				$value[ $area ] = array();
				$value[ $area ]['row_name'] = isset( $_POST[ $area . '-' . $row ] ) ? esc_html( $_POST[ $area . '-' . $row ] ) : '';

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
} // Sample_Content_Block_Areas

global $sample_content_block_areas;
$sample_content_block_areas = new Sample_Content_Block_Areas();
