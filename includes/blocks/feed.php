<?php
if ( ! class_exists( 'CCB_Content_Block' ) ) {
	return;
}

class CCB_Feed_Content_Block extends CCB_Content_Block {

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
		<input type="hidden" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="feed" />
		<p>
			<label for="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]"><?php esc_html_e( 'Title', 'ccb' ); ?></label>
			<input type="text" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]" class="widefat" value="<?php echo isset( $data['title'] ) ? esc_attr( $data['title'] ) : ''; ?>"/>
		</p>

		<div class="non-manual"<?php if ( isset( $data['manual'] ) && 'y' === $data['manual'] ) echo ' style="display:none"'; ?>>
			<p>
				<label for="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][number]"><?php esc_html_e( 'Number of Items', 'ccb' ); ?></label>
				<input type="text" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][number]" value="<?php echo isset( $data['number'] ) ? esc_attr( $data['number'] ) : ''; ?>"/>
			</p>
			<p>
				<?php $selected = isset( $data['post_type'] ) ? esc_attr( $data['post_type'] ) : 'any'; ?>
				<label for="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][post_type]"><?php esc_html_e( 'Type of Content', 'ccb' ); ?></label>
				<select name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][post_type][]" id="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][post_type]" class="postform select2">
					<option value="any" <?php selected( $selected, 'any' ); ?>>All</option>
					<?php
					$post_types = get_post_types( array( 'public' => true, ) );
					foreach ( $post_types  as $post_type => $name ) {
						echo '<option value="'. esc_attr( $post_type ) .'" '. selected( $selected, $post_type ) .'>' . esc_html( $name ) . '</option>';
					}
					?>
				</select>
			</p>
			<p>
				<label for="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][category]"><?php esc_html_e( 'Choose Category', 'ccb' ); ?></label>
				<?php
				$cat_args = array(
					'orderby'          => 'NAME',
					'selected'         => isset( $data['category'] ) && ! empty( $data['category'] ) ? $data['category'] : 0,
					'name'             => 'ccb_content_blocks[' . esc_attr( $row ) . '][' . esc_attr( $area ) . '][' . esc_attr( $column ) . '][' . esc_attr( $iterator ) . '][category][]',
					'show_option_none' => 'None',
					'class'            => 'postform select2',
				);
				wp_dropdown_categories( $cat_args );
				?>
			</p>
			<p>
				<label for="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][tag]"><?php esc_html_e( 'Choose Tag', 'ccb' ); ?></label>
				<?php
				// todo should this use some sort of auto complete library or something? A dropdown of all tags could get pretty insane
				$tag_args = array(
					'orderby'          => 'NAME',
					'selected'         => isset( $data['tag'] ) ? $data['tag'] : 0,
					'name'             => 'ccb_content_blocks[' . esc_attr( $row ) . '][' . esc_attr( $area ) . '][' . esc_attr( $column ) . '][' . esc_attr( $iterator ) . '][tag][]',
					'taxonomy'         => 'post_tag',
					'show_option_none' => 'None',
					'order'            => 'DESC',
					'class'            => 'postform select2',
				);
				wp_dropdown_categories( $tag_args );
				?>
			</p>
		</div><!-- .non-manual -->

		<?php if ( class_exists( 'NS_Post_Finder' ) ) : ?>
			<hr/>
			<p class="toggle-manual">
				<label>
					<input type="checkbox" name="ccb_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][manual]" value="y" <?php isset( $data['manual'] ) ? checked( $data['manual'], 'y' ) : ''; ?>/>
					Manual override
				</label>
			</p>
			<div class="manual hide-if-no-js"<?php if ( ! isset( $data['manual'] ) || 'y' !== $data['manual'] ) echo ' style="display:none"'; ?>>
				<?php
				$options['limit'] = 50;
				$options['args'] = array(
					'post_type'   => array( 'post', 'emmis-bus-listings', 'tribe_events' ),
					'post_status' => 'publish',
				);

				NS_Post_Finder::render( 'ccb_content_blocks[' . $row . '][' . $area . '][' . $column . '][' . $iterator . '][posts]', isset( $data['posts'] ) ? $data['posts'] : '', $options );
				?>
			</div><!-- .manual.hide-if-no-js -->
			<hr/>
		<?php
		endif;
		?>

	<?php
	}

	/**
	 * Clean our data saved in this block.
	 *
	 * @param array $data Data being saved in block.
	 * @return array
	 */
	public static function clean_data( $data ) {
		$new = array();

		$new['pause']     = isset( $data['pause'] ) ? 'y' : '';
		$new['type']      = 'feed';
		$new['manual']    = isset( $data['manual'] ) ? 'y' : '';
		$new['posts']     = isset( $data['posts'] ) ? implode( ',', array_map( 'absint', explode( ',', $data['posts'] ) ) ) : '';
		$new['title']     = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '';
		$new['number']    = isset( $data['number'] ) ? absint( $data['number'] ) : '10';
		$new['post_type'] = isset( $data['post_type'] ) ? sanitize_text_field( $data['post_type'] ) : 'any';
		$new['category']  = isset( $data['category'] ) && '-1' !== $data['category'] ? array_map( 'absint', $data['category'] ) : array();
		$new['tag']       = isset( $data['tag'] ) && -1 !== $data['tag'] ? array_map( 'absint', $data['tag'] ) : array();

		return $new;
	}

	/**
	 * Run queries needed to display block.
	 *
	 * @param array $data Data saved in block.
	 * @param string $area Current area.
	 * @return void
	 */
	public static function display( $data, $area ) {
		if ( isset( $data['manual'] ) && 'y' === $data['manual'] ) {
			if ( ! isset( $data['posts'] ) || empty( $data['posts'] ) ) {
				// If in manual mode, and we don't have any posts, gracefully fail
				return;
			}

			$post_ids = explode( ',', $data['posts'] );
		} else {
			$params = isset( $data['number'] ) ? absint( $data['number'] ) : 10;
			if ( isset( $data['post_type'] ) ) {
				$params .= '_' . $data['post_type'];
			}
			if ( isset( $data['category'] ) ) {
				$params .= '_' . $data['category'];
			}
			if ( isset( $data['tag'] ) ) {
				$params .= '_' . $data['tag'];
			}
			$cache_key = 'ccb_feed_' . $params;
			$post_ids = wp_cache_get( $cache_key );
			if ( false === $post_ids ) {
				$query_args = array(
					'post_type'      => ( isset( $data['post_type'] ) && 'any' !== $data['post_type'] ) ? esc_attr( $data['post_type'] ) : 'post',
					'posts_per_page' => isset( $data['number'] ) ? absint( $data['number'] ) : 10,
					'no_found_rows'  => true,
					'fields'         => 'ids',
				);

				$category = isset( $data['category'] ) ? absint( $data['category'] ) : false;
				$tag = isset( $data['tag'] ) ? absint( $data['tag'] ) : false;

				if ( $category || $tag ) {
					$query_args['tax_query'] = array();
				}
				if ( ( $category && $tag ) ) {
					$query_args['tax_query']['relation'] = 'AND';
				}
				if ( $category ) {
					$query_args['tax_query'][] = array(
						'taxonomy' => 'category',
						'field'    => 'id',
						'terms'    => $category,
					);
				}
				if ( $tag ) {
					$query_args['tax_query'][] = array(
						'taxonomy' => 'post_tag',
						'field'    => 'id',
						'terms'    => $tag,
					);
				}

				$feed_query = new WP_Query( $query_args );
				if ( ! $feed_query->have_posts() ) {
					return;
				}

				$post_ids = $feed_query->posts;
				wp_cache_set( $cache_key, $post_ids, '', MINUTE_IN_SECONDS * 30 );
			}
		}

		echo '<div class="module module-feed">';
		if ( isset( $data['title'] ) && '' !== trim( $data['title'] ) ) {
			echo '<h2>';
			echo esc_html( $data['title'] );
			echo '</h2>';
		}
		echo '<ul>';
		foreach ( $post_ids as $post_id ) {
			global $post;

			$post = get_post( (int) $post_id );
			setup_postdata( $post );

			self::markup( $data );
		}
		echo '</ul></div><!-- .module.module-feed -->';

		wp_reset_postdata();
	}

	/**
	 * Display the block.
	 *
	 * @param array $data Data saved in block.
	 * @return void
	 */
	public static function markup( $data ) {
	?>
		<li>
			<a href="<?php the_permalink(); ?>">
				<?php if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'medium' );
				} ?>
			</a>
			<div class="post-info">
				<p class="story-tags"><?php the_terms( get_the_ID(), 'post_tag' ); ?></p>
				<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<?php the_excerpt(); ?>
				<p class="meta">
					<span class="date"><?php the_time( 'F j, Y' ); ?></span>
					<span class="author"><?php the_author(); ?></span>
				</p>
			</div><!-- .post-info -->
		</li>
	<?php
	}

}
