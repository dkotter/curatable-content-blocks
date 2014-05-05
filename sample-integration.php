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
		add_action( 'add_meta_boxes_post', array( $this, 'post_blocks' ) );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
	}

	/**
	 * Register content blocks.
	 *
	 * @return void
	 */
	function init() {

		// content block registration
		tenup_register_content_block( 'html', 'Text/HTML', 'Sample_Content_Block_HTML' );

		// This one has the widget bridge enabled
		tenup_register_content_block( 'no-settings', 'No Settings', 'Sample_Content_Block_No_Settings', array( 'widget' => true ) );
	}

	public function admin_enqueue_scripts() {
		$screen = get_current_screen();

		// Allowed for post, widgets, and any post type with support for 'tenup-content-blocks'
		if ( 'post' === $screen->base || 'widgets' === $screen->base || post_type_supports( $screen->post_type, 'tenup-content-blocks' ) ) {
			// wp_enqueue_media();

			wp_enqueue_script( 'tenup-content-blocks', plugins_url( '/js/content-blocks.js', __FILE__ ), array( 'jquery', 'jquery-ui-sortable' ), false, true );
			wp_enqueue_style( 'tenup-content-blocks', plugins_url( '/css/content-blocks.css', __FILE__ ) );
		}
	}

	public function page_blocks( $post ) {
		if ( ! in_array( get_post_type( $post ), array( 'custom_post_type', 'page' ) ) ) {
			return false;
		}

		$blocks = get_post_meta( $post->ID, 'tenup_content_blocks', true );

		// get default blocks when adding a new custom item
		if ( ! $blocks && 'custom_post_type' === get_post_type( $post ) ) {
			$screen = get_current_screen();
			if ( 'add' === $screen->action ) {
				$blocks = $this->default_cpt_blocks();
			}
		}

		wp_nonce_field( 'tenup-save-content-blocks', $name = 'tenup_content_blocks_nonce' );
?>
<div class="content-blocks-wrapper">
	<?php if ( 'custom_post_type' === get_post_type( $post ) ) : ?>
	<div class="postbox breaking-news">
		<h3>Breaking News</h3>

		<?php
			// Only allow specific blocks
			$block_args = array(
				'include' => array( 'html' ),
			);
			$this->edit_blocks( 'breaking-news', $blocks, $block_args );
		?>
	</div>
	<?php endif; ?>

	<div class="sample-layout-left">
		<div class="postbox large-main">
			<h3>Large Main Column</h3>

			<?php $this->edit_blocks( 'large-main', $blocks ); ?>
		</div>

		<div class="postbox left-main-col">
			<h3>Left Column</h3>

			<?php $this->edit_blocks( 'left-main', $blocks ); ?>
		</div>

		<div class="postbox right-main-col">
			<h3>Right Column</h3>

			<?php $this->edit_blocks( 'right-main', $blocks ); ?>
		</div>

		<div class="postbox bottom-col">
			<h3>Bottom Column</h3>

			<?php $this->edit_blocks( 'bottom', $blocks ); ?>
		</div>
	</div>

	<div class="sample-layout-right">
		<div class="postbox large-main">
			<h3>Sidebar</h3>

			<?php $this->edit_blocks( 'sidebar', $blocks ); ?>
		</div>
	</div>
</div>
<?php
	}

	private function default_cpt_blocks() {
		// @todo: some of these may need some default settings as well
		return array(
			'sidebar' => array(
				array( 'type' => 'no-settings' ),
			),
		);
	}

	public function post_blocks() {
		add_meta_box( 'sample-article-sidebar', 'Article Sidebar', array( $this, 'article_sidebar' ), 'post', 'normal', 'high' );
	}

	public function article_sidebar() {
		$blocks = get_post_meta( get_the_ID(), 'tenup_content_blocks', true );

		wp_nonce_field( 'tenup-save-content-blocks', $name = 'tenup_content_blocks_nonce' );

		$this->edit_blocks( 'sidebar', $blocks );
	}

	public function edit_blocks( $area, $blocks, $block_args = array() ) {
?>
<div class="content-blocks sortable">
	<?php
		$i = 0;

		// render the current data
		if ( ! empty( $blocks[ $area ] ) && is_array( $blocks[ $area ] ) ) {
			foreach ( $blocks[ $area ] as $data ) {
				echo $this->template( $data['type'], $data, $area, $i );
				$i++;
			}
		}

		$this->adder( $area, $i, $block_args );
	?>
</div>
<?php
	}

	private function template( $type, $data, $area, $i ) {
		$registered_blocks = tenup_get_registered_content_blocks();
		if ( isset( $registered_blocks[ $type ] ) && is_callable( array( $registered_blocks[ $type ]['class'], 'settings_form' ) ) ) {
			?>
			<div class="content-block collapsed <?php echo esc_attr( $data['type'] ); ?>">
				<h4 class="content-block-header">
					<span class="handle"><img src="<?php echo plugins_url( 'img/drag-handle.png', __FILE__ ); ?>" /></span>
					<?php echo esc_html( $registered_blocks[ $type ]['name'] ); ?>
					<a href="#" class="delete-content-block">Delete</a>
				</h4>

				<div class="interior">
					<?php
					$registered_blocks[ $type ]['class']::settings_form( $data, $area, $i );
					?>
				</div>
			</div>
			<?php
		}
	}

	private function adder( $area, $iterator = 0, $block_args = array() ) {
?>
<div class="content-block-adder" data-tenup-area=<?php echo esc_attr( $area ); ?> data-tenup-iterator=<?php echo esc_attr( $iterator ); ?>>
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

		foreach ( (array) $_POST['tenup_content_blocks'] as $area => $blocks ) {
			$value = array();

			foreach ( (array) $blocks as $key => $data ) {
				$type = $data['type'];

				// not a registered block
				if ( ! isset( $registered_blocks[ $type ] ) ) {
					continue;
				}

				if ( is_callable( array( $registered_blocks[ $type ]['class'], 'clean_data' ) ) ) {
					$value[] = $registered_blocks[ $type ]['class']::clean_data( $data );
				}
			}

			$new[ $area ] = $value;
		}

		update_post_meta( $post_id, $meta_key, $new );

		$this->_saving = false;
		$this->_saved = true;
	}
} // Sample_Content_Block_Areas

$sample_content_block_areas = new Sample_Content_Block_Areas();
