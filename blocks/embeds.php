<?php
if ( ! class_exists( 'Tenup_Content_Block' ) ) {
	return;
}

class Emmis_Embeds_Content_Block extends Tenup_Content_Block {

	/**
	 * Output our settings form for this block.
	 *
	 * @param array $data Data saved in block.
	 * @param string $area Current area we are in.
	 * @param int $row Current row number.
	 * @param int $column Current column number.
	 * @param int $iterator Current block number.
	 * @return void
	 */
	public static function settings_form( $data, $area, $row = 1, $column = 1, $iterator = 0 ) {
		?>
		<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="embeds" />

		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]">Title (or <a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>" target="_blank">image URL</a>)</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]" class="widefat" value="<?php echo isset( $data['title'] ) ? esc_attr( $data['title'] ) : ''; ?>"/>
		</p>
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][content]">Content</label>
			<?php
			$editor_content = isset( $data['content'] ) ? $data['content'] : '';
			$editor_id = 'tenup_content_blocks['. esc_attr( $row ) .']['. esc_attr( $area ) .']['. esc_attr( $column ) .']['. esc_attr( $iterator ) .'][content]'; ?>
			<?php wp_editor( $editor_content, $editor_id, array( 'textarea_rows' => 5, 'tinymce' => false, 'quicktags' => array( 'buttons' => 'strong,em,block,del,ins,img,ul,ol,li,code,more,close' ) ) ); ?>
		</p>
	<?php
	}

	/**
	 * Clean our data saved in this block.
	 *
	 * @param array $data Data being saved in block.
	 * @return array
	 */
	public static function clean_data( $data ) {
		$whitelisted_src = array( site_url(), 'http://www.redbubble.com/assets/external_portfolio.js', 'http://admin.brightcove.com/js/BrightcoveExperiences.js', '+ ord +', 'http://e.issuu.com/', );
		$new = array();

		$new['pause'] = isset( $data['pause'] ) ? 'y' : '';
		$new['type'] = sanitize_key( $data['type'] );
		$new['title'] = isset( $data['title'] ) ? wp_filter_post_kses( $data['title'] ) : '';

		// Remove any non-whitelisted scripts
		if ( isset( $data['content'] ) ) {
			if ( strpos( $data['content'], '<script' ) !== false ) {
				preg_match_all( "/<script (.|\n)*?>(.|\n)*?<\/script>/", $data['content'], $matches );
				if ( is_array( $matches ) && isset( $matches[0] ) ) {
					foreach ( (array) $matches[0] as $script ) {
						if ( strpos( $script, 'src=' ) !== false ) {
							$found = false;
							foreach ( $whitelisted_src as $src ) {
								if ( strpos( $script, $src ) !== false ) {
									$found = true;
								}
							}
							if ( ! $found ) {
								$data['content'] = str_replace( $script, '', $data['content'] );
							}
						}
					}
				}
			}
		}
		$new['content'] = isset( $data['content'] ) ? addslashes( wp_kses( stripslashes( $data['content'] ), 'embed_module' ) ) : '';

		return $new;
	}

	public static function display( $data, $area ) {
		?>
		<div class="module module-blanktext module-embeds">
			<?php if ( isset( $data['title'] ) && '' !== trim( $data['title'] ) ) : ?>
				<h2 class="h2">
					<?php if ( 'http' === substr( $data['title'], 0, 4 ) || 'www' === substr( $data['title'], 0, 3 ) ) : ?>
						<img src="<?php echo esc_url( $data['title'] ); ?>">
					<?php else : ?>
						<?php echo esc_html( $data['title'] ); ?>
					<?php endif; ?>
				</h2>
			<?php endif; ?>
			<?php
			/*
			 * NOTE: content is already sanitized before save, so currently we trust it.
			 * However, if ever the content is derived from an external source,
			 * something else needs to be done here. kses is heavy and some functions
			 * rely on the user level for allowed tags, but something along those lines
			 * may very well become necessary for security.
			 */
			echo apply_filters( 'the_content', $data['content'] );
			?>
		</div><!-- .module-embeds -->
	<?php
	}

}

/*
 * Allow iframes in the embed module
 *
 * @param array $allowedtags Currently allowed tags.
 * @param string $context Current context.
 * @return array
 */
function emmis_allow_iframes_in_modules( $allowedtags, $context ) {
	global $allowedposttags;

	if ( current_user_can( 'edit_others_posts' ) && 'embed_module' === $context ) {
		$allowedtags = $allowedposttags;
		$allowedtags['div']['data-configid'] = true;
		$allowedtags['iframe'] = array(
			'width'           => true,
			'height'          => true,
			'src'             => true,
			'frameborder'     => true,
			'allowfullscreen' => true,
			'align'           => true,
			'name'            => true,
			'style'           => true,
		);
		$allowedtags['object'] = array(
			'id'    => true,
			'class' => true,
		);
		$allowedtags['param'] = array(
			'name'  => true,
			'value' => true,
		);
		$allowedtags['link'] = array(
			'rel'   => true,
			'type'  => true,
			'href'  => true,
			'media' => true,
		);
		$allowedtags['script'] = array(
			'type'     => true,
			'id'       => true,
			'src'      => true,
			'language' => true,
		);
		$allowedtags['nocleanuptag'] = array();
		$allowedtags['noscript'] = array();
	}

	return $allowedtags;
}
add_filter( 'wp_kses_allowed_html', 'emmis_allow_iframes_in_modules', 10, 2 );

/*
 * Allow a few more CSS attributes.
 *
 * @param array $attr Current list of approved attributes.
 * @return array
 */
function emmis_filter_allowed_css( $attr ) {
	$attr[] = 'display';
	$attr[] = 'float';

	return $attr;
}
add_filter( 'safe_style_css', 'emmis_filter_allowed_css' );
