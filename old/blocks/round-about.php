<?php
if ( ! class_exists( 'Tenup_Content_Block' ) ) {
	return;
}

class Emmis_Round_About_Content_Block extends Tenup_Content_Block {

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
		<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="round-about" />

		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]">Title (or <a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>" target="_blank">image URL</a>)</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]" class="widefat" value="<?php echo isset( $data['title'] ) ? esc_attr( $data['title'] ) : ''; ?>"/>
		</p>
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title_link]">Title Link (if desired)</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title_link]" class="widefat" value="<?php echo isset( $data['title_link'] ) ? esc_url( $data['title_link'] ) : ''; ?>"/>
		</p>

		<div class="non-manual"<?php if ( isset( $data['manual'] ) && 'y' === $data['manual'] ) echo ' style="display:none"'; ?>>
			<p>
				<?php $selected = isset( $data['post_type'] ) ? array_map( 'esc_attr', $data['post_type'] ) : 'any'; ?>
				<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][post_type]">Type of Content to Show</label>
				<select name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][post_type][]" id="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][post_type]" class="postform">
					<option value="any" <?php echo emmis_selected( $selected, 'any' ); ?>>All</option>
					<?php
					$post_types = array( 'post' => 'Articles', 'tribe_events' => 'Events', 'emmis-contests' => 'Contests', 'emmis-galleries' => 'Galleries', 'emmis-bus-listings' => 'Business Listings', 'emmis-polls' => 'Polls' );
					foreach ( $post_types  as $post_type => $name ) {
						echo '<option value="'. esc_attr( $post_type ) .'" '. emmis_selected( $selected, $post_type ) .'>' . esc_html( $name ) . '</option>';
					}
					?>
				</select>
			</p>
			<p>
				<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][category]">Choose Category</label>
				<?php
				$cat_args = array(
					'orderby'          => 'NAME',
					'selected'         => isset( $data['category'] ) ? $data['category'] : 0,
					'name'             => 'tenup_content_blocks[' . esc_attr( $row ) . '][' . esc_attr( $area ) . '][' . esc_attr( $column ) . '][' . esc_attr( $iterator ) . '][category][]',
					'show_option_none' => 'None',
					'class'            => 'postform select2',
					'multiple'         => true,
					'walker'           => new Emmis_Category_Walker(),
				);
				emmis_dropdown_taxonomy( $cat_args );
				?>
			</p>
			<p>
				<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][tag]">Choose Tag</label>
				<?php
				$tag_args = array(
					'orderby'          => 'NAME',
					'selected'         => isset( $data['tag'] ) ? $data['tag'] : 0,
					'name'             => 'tenup_content_blocks[' . esc_attr( $row ) . '][' . esc_attr( $area ) . '][' . esc_attr( $column ) . '][' . esc_attr( $iterator ) . '][tag][]',
					'taxonomy'         => 'post_tag',
					'show_option_none' => 'None',
					'number'           => 1350,
					'orderby'          => 'count',
					'order'            => 'DESC',
					'class'            => 'postform select2',
					'multiple'         => true,
					'walker'           => new Emmis_Category_Walker(),
				);
				emmis_dropdown_taxonomy( $tag_args );
				?>
			</p>
			<p>
				<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][blog]">Choose Blog</label>
				<?php
				$blog_args = array(
					'orderby'          => 'NAME',
					'selected'         => isset( $data['blog'] ) ? $data['blog'] : 0,
					'name'             => 'tenup_content_blocks[' . esc_attr( $row ) . '][' . esc_attr( $area ) . '][' . esc_attr( $column ) . '][' . esc_attr( $iterator ) . '][blog][]',
					'taxonomy'         => 'emmis-blog',
					'show_option_none' => 'None',
					'hide_empty'       => false,
					'class'            => 'postform select2',
					'multiple'         => true,
					'walker'           => new Emmis_Category_Walker(),
				);
				emmis_dropdown_taxonomy( $blog_args );
				?>
			</p>
			<p>
				<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][and-query]">
					<input type="checkbox" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][and-query]" id="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][and-query]" <?php isset( $data['and-query'] ) ? checked( $data['and-query'], 'y' ) : ''; ?>/>
					Use an 'AND' query
				</label>
			</p>
			<p>Note: By default, selecting multiple taxonomy terms creates an 'OR' query, so will match any content that has any of the terms selected, instead of matching any content that has ALL terms selected. Select option above to match ALL terms.</p>
		</div>

		<hr/>

		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][excerpt]">
				<input type="checkbox" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][excerpt]" id="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][excerpt]" <?php isset( $data['excerpt'] ) ? checked( $data['excerpt'], 'y' ) : ''; ?>/>
				Hide excerpt
			</label>
		</p>

		<?php if ( class_exists( 'NS_Post_Finder' ) ) : ?>
			<hr/>
			<p class="toggle-manual">
				<label>
					<input type="checkbox" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][manual]" value="y" <?php isset( $data['manual'] ) ? checked( $data['manual'], 'y' ) : ''; ?>/>
					Manual override
				</label>
			</p>
			<div class="manual hide-if-no-js"<?php if ( ! isset( $data['manual'] ) || 'y' !== $data['manual'] ) echo ' style="display:none"'; ?>>
				<?php
				$options['limit'] = 3;
				$options['args'] = array(
					'post_type' => array( 'post', 'emmis-bus-listings', 'tribe_events' ),
				);

				// Only admins can see everything
				if ( current_user_can( 'manage_options' ) ) {
					$options['args']['post_status'] = 'any';
				} else {
					$options['args']['post_status'] = array( 'publish', 'pending', 'draft', 'inherit' );
				}

				NS_Post_Finder::render( 'tenup_content_blocks[' . $row . '][' . $area . '][' . $column . '][' . $iterator . '][posts]', isset( $data['posts'] ) ? $data['posts'] : '', $options );
				?>
			</div>
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

		$new['pause'] = isset( $data['pause'] ) ? 'y' : '';
		$new['type'] = sanitize_key( $data['type'] );
		$new['manual'] = isset( $data['manual'] ) ? 'y' : '';
		$new['title'] = isset( $data['title'] ) ? wp_filter_post_kses( $data['title'] ) : '';
		$new['title_link'] = isset( $data['title_link'] ) ? esc_url_raw( $data['title_link'] ) : '';

		$new['posts'] = isset( $data['posts'] ) ? implode( ',', array_map( 'absint', explode( ',', $data['posts'] ) ) ) : '';
		$new['post_type'] = isset( $data['post_type'] ) ? array_map( 'esc_html', $data['post_type'] ) : array( 'any' );
		$new['category'] = isset( $data['category'] ) && '-1' != $data['category'] ? array_map( 'absint', $data['category'] ) : array();
		$new['tag'] = isset( $data['tag'] ) && -1 != $data['tag'] ? array_map( 'absint', $data['tag'] ) : array();
		$new['blog'] = isset( $data['blog'] ) && -1 != $data['blog'] ? array_map( 'absint', $data['blog'] ) : array();
		$new['and-query'] = isset( $data['and-query'] ) ? 'y' : '';
		$new['excerpt'] = isset( $data['excerpt'] ) ? 'y' : '';

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
		if ( isset( $data['manual'] ) && 'y' == $data['manual'] ) {
			if ( ! isset( $data['posts'] ) || empty( $data['posts'] ) ) {
				// If in manual mode, and we don't have any posts, gracefully fail
				return;
			}

			$post_ids = explode( ',', $data['posts'] );
		} else {
			$query_args = array(
				'post_type'      => ( isset( $data['post_type'] ) && 'any' !== $data['post_type'][0] ) ? array_map( 'esc_html', $data['post_type'] ) : array( 'post', 'emmis-bus-listings', 'emmis-galleries', 'tribe_events' ),
				'posts_per_page' => 3,
				'no_found_rows'  => true,
				'fields'         => 'ids',
			);

			$category = isset( $data['category'] ) ? array_map( 'absint', $data['category'] ) : false;
			$tag = isset( $data['tag'] ) ? array_map( 'absint', $data['tag'] ) : false;
			$blog = isset( $data['blog'] ) ? array_map( 'absint', $data['blog'] ) : false;

			if ( $category || $tag || $blog ) {
				$query_args['tax_query'] = array();
			}
			if ( ( $category && $tag ) || ( $category && $blog ) || ( $tag && $blog ) ) {
				if ( isset( $data['and-query'] ) && 'y' === $data['and-query'] ) {
					$query_args['tax_query']['relation'] = 'AND';
				} else {
					$query_args['tax_query']['relation'] = 'OR';
				}
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
			if ( $blog ) {
				$query_args['tax_query'][] = array(
					'taxonomy' => 'emmis-blog',
					'field'    => 'id',
					'terms'    => $blog,
				);
			}

			$round_about_query = new WP_Query( $query_args );
			if ( ! $round_about_query->have_posts() ) {
				return;
			}

			$post_ids = $round_about_query->posts;
		}

		echo '<div class="module module-roundabout">';
		if ( isset( $data['title'] ) && '' !== trim( $data['title'] ) ) {
			echo '<h2 class="h2">';
			if ( isset( $data['title_link'] ) && '' !== trim( $data['title_link'] ) ) {
				echo '<a href="'. esc_url( $data['title_link'] ) .'">';
			}
			if ( 'http' === substr( $data['title'], 0, 4 ) || 'www' === substr( $data['title'], 0, 3 ) ) {
				echo '<img src="'. esc_url( $data['title'] ) .'">';
			} else {
				echo esc_html( $data['title'] );
			}
			if ( isset( $data['title_link'] ) && '' !== trim( $data['title_link'] ) ) {
				echo '</a>';
			}
			echo '</h2>';
		}
		echo '<div class="row">';
		foreach ( $post_ids as $post_id ) {
			global $post;

			$post = get_post( (int) $post_id );
			setup_postdata( $post );
			self::markup( $data );
		}
		echo '</div><!-- .row --></div><!-- .module-roundabout -->';

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
		<div class="col-1-3">
			<a href="<?php the_permalink(); ?>">
				<?php if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'landscape' );
				} else if ( $image = emmis_get_images( get_the_ID(), 'landscape' ) ) {
					echo wp_kses_post( $image[0] );
				} ?>
			</a>
			<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<?php if ( ! isset( $data['excerpt'] ) || ( isset( $data['excerpt'] ) && 'y' !== $data['excerpt'] ) ) {
				the_excerpt();
			} ?>
		</div>
	<?php
	}

}