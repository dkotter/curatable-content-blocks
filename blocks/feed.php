<?php
if ( ! class_exists( 'Tenup_Content_Block' ) ) {
	return;
}

class Emmis_Feed_Content_Block extends Tenup_Content_Block {

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
		<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="feed" />
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
				<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][number]">Number Of Posts</label>
				<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][number]" value="<?php echo isset( $data['number'] ) ? esc_attr( $data['number'] ) : ''; ?>"/>
			</p>
			<p>
				<?php $selected = isset( $data['post_type'] ) ? array_map( 'esc_attr', $data['post_type'] ) : 'any'; ?>
				<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][post_type]">Type of Content to Show</label>
				<select name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][post_type][]" id="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][post_type]" class="postform select2" multiple>
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
					'selected'         => isset( $data['category'] ) && ! empty( $data['category'] ) ? $data['category'] : 0,
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
				// todo should this use some sort of auto complete library or something? A dropdown of all tags could get pretty insane
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
			<p>
				<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][orderby-title]">
					<input type="checkbox" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][orderby-title]" id="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][orderby-title]" <?php isset( $data['orderby-title'] ) ? checked( $data['orderby-title'], 'y' ) : ''; ?>/>
					Order Numerically By Post Title
				</label>
			</p>
		</div>

		<hr/>

		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][float-image]">
				<input type="checkbox" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][float-image]" id="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][float-image]" <?php isset( $data['float-image'] ) ? checked( $data['float-image'], 'y' ) : ''; ?>/>
				Float Image
			</label>
		</p>
		<p>Note: Check for layout similar to archive pages. Uncheck for full width images.</p>
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][no-byline]">
				<input type="checkbox" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][no-byline]" id="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][no-byline]" <?php isset( $data['no-byline'] ) ? checked( $data['no-byline'], 'y' ) : ''; ?>/>
				Remove Byline
			</label>
		</p>
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][more-text]">More Text</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][more-text]" class="widefat" value="<?php echo isset( $data['more-text'] ) ? esc_attr( $data['more-text'] ) : ''; ?>"/>
		</p>
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][more-link]">More URL</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][more-link]" class="widefat" value="<?php echo isset( $data['more-link'] ) ? esc_attr( $data['more-link'] ) : ''; ?>"/>
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
				$options['limit'] = 50;
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

		$new['posts'] = isset( $data['posts'] ) ? implode( ',', array_map( 'absint', explode( ',', $data['posts'] ) ) ) : '';
		$new['title'] = isset( $data['title'] ) ? wp_filter_post_kses( $data['title'] ) : '';
		$new['title_link'] = isset( $data['title_link'] ) ? esc_url_raw( $data['title_link'] ) : '';
		$new['number'] = isset( $data['number'] ) ? absint( $data['number'] ) : '10';
		$new['post_type'] = isset( $data['post_type'] ) ? array_map( 'esc_html', $data['post_type'] ) : array( 'any' );
		$new['category'] = isset( $data['category'] ) && '-1' != $data['category'] ? array_map( 'absint', $data['category'] ) : array();
		$new['tag'] = isset( $data['tag'] ) && -1 != $data['tag'] ? array_map( 'absint', $data['tag'] ) : array();
		$new['blog'] = isset( $data['blog'] ) && -1 != $data['blog'] ? array_map( 'absint', $data['blog'] ) : array();

		$new['and-query'] = isset( $data['and-query'] ) ? 'y' : '';
		$new['float-image'] = isset( $data['float-image'] ) ? 'y' : '';
		$new['orderby-title'] = isset( $data['orderby-title'] ) ? 'y' : '';
		$new['no-byline'] = isset( $data['no-byline'] ) ? 'y' : '';
		$new['more-text'] = isset( $data['more-text'] ) ? wp_filter_post_kses( $data['more-text'] ) : '';
		$new['more-link'] = isset( $data['more-link'] ) ? esc_url_raw( $data['more-link'] ) : '';

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
				// If in manual mode, and we don't have a post, gracefully fail
				return;
			}

			$post_ids = explode( ',', $data['posts'] );
		} else {
			$params = isset( $data['number'] ) ? absint( $data['number'] ) : 10;
			if ( isset( $data['post_type'] ) ) {
				foreach ( $data['post_type'] as $type ) {
					$params .= '_' . $type;
				}
			}
			if ( isset( $data['category'] ) ) {
				foreach ( $data['category'] as $category_id ) {
					$params .= '_' . $category_id;
				}
			}
			if ( isset( $data['tag'] ) ) {
				foreach ( $data['tag'] as $tag_id ) {
					$params .= '_' . $tag_id;
				}
			}
			if ( isset( $data['blog'] ) ) {
				foreach ( $data['blog'] as $blog_id ) {
					$params .= '_' . $blog_id;
				}
			}
			if ( isset( $data['and-query'] ) && 'y' === $data['and-query'] ) {
				$params .= '_and';
			} else {
				$params .= '_or';
			}
			if ( isset( $data['orderby-title'] ) && 'y' === $data['orderby-title'] ) {
				$params .= '_title';
			} else {
				$params .= '_date';
			}
			$cache_key = 'feed_' . $params;
			$post_ids = wp_cache_get( $cache_key );
			if ( false === $post_ids ) {
				$query_args = array(
					'post_type'      => ( isset( $data['post_type'] ) && 'any' !== $data['post_type'][0] ) ? array_map( 'esc_html', $data['post_type'] ) : array( 'post', 'emmis-bus-listings', 'emmis-galleries', 'tribe_events' ),
					'posts_per_page' => isset( $data['number'] ) ? absint( $data['number'] ) : 10,
					'no_found_rows'  => true,
					'fields'         => 'ids',
				);

				if ( isset( $data['orderby-title'] ) && 'y' === $data['orderby-title'] ) {
					$query_args['fields'] = 'all';
				}

				if ( 'emmis-contests' === $data['post_type'][0] ) {
					$query_args['meta_query'] = array(
						array(
							'key'     => '_emmis_contest_closed',
							'compare' => 'NOT EXISTS',
						)
					);
				}

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

				$feed_query = new WP_Query( $query_args );
				if ( ! $feed_query->have_posts() ) {
					return;
				}

				if ( isset( $data['orderby-title'] ) && 'y' === $data['orderby-title'] ) {
					$posts = $feed_query->posts;
					$filtered_posts = array();
					foreach ( $posts as $post ) {
						$filtered_posts[ $post->ID ] = $post->post_title;
					}

					natsort( $filtered_posts );
					$post_ids = array();
					foreach ( $filtered_posts as $post_id => $post_title ) {
						$post_ids[] = $post_id;
					}
					wp_cache_set( $cache_key, $post_ids, '', MINUTE_IN_SECONDS * 30 );
				} else {
					$post_ids = $feed_query->posts;
					wp_cache_set( $cache_key, $post_ids, '', MINUTE_IN_SECONDS * 30 );
				}
			}
		}

		if ( isset( $data['float-image'] ) && 'y' == $data['float-image'] ) {
			$class = 'align-left';
		} else {
			$class = '';
		}

		echo '<div class="module module-feed">';
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
		echo '<ul>';
		foreach ( $post_ids as $post_id ) {
			global $post;

			$post = get_post( (int) $post_id );
			setup_postdata( $post );

			self::markup( $data, $class );
		}
		if ( isset( $data['more-link'] ) && '' !== trim( $data['more-link'] ) ) {
			echo '<li class="read-more">';
			echo '<a href="'. esc_url( $data['more-link'] ) .'">';
			echo isset( $data['more-link'] ) ? esc_html( $data['more-text'] ) : '';
			echo '</a></li>';
		}
		echo '</ul></div><!-- .module-feed -->';

		wp_reset_postdata();
	}

	/**
	 * Display the block.
	 *
	 * @param array $data Data saved in block.
	 * @param string $class Custom class name.
	 * @return void
	 */
	public static function markup( $data, $class ) {
	?>
		<li <?php if ( get_post_meta( get_the_ID(), '_emmis_sponsored', true ) || get_post_meta( get_the_ID(), '_emmis_promo', true ) || get_post_meta( get_the_ID(), '_emmis_custom_pub', true ) ) { echo 'class="sponsored"'; } ?>>
			<a href="<?php the_permalink(); ?>">
				<?php if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'landscape', array( 'class' => $class ) );
				} else if ( $image = emmis_get_images( get_the_ID(), 'landscape', 1, 'align-left' ) ) {
					echo wp_kses_post( $image[0] );
				} ?>
			</a>
				<div class="post-info <?php echo esc_attr( $class ); ?>">
					<p class="story-tags"><?php the_terms( get_the_ID(), 'emmis-blog' ); ?></p>
					<?php if ( get_post_meta( get_the_ID(), '_emmis_sponsored', true ) ) {
						echo '<span class="sponsored-story">Sponsored</span>';
					} else if ( get_post_meta( get_the_ID(), '_emmis_promo', true ) ) {
						echo '<span class="sponsored-story">Promotion</span>';
					} else if ( get_post_meta( get_the_ID(), '_emmis_custom_pub', true ) ) {
						echo '<span class="sponsored-story">Custom Publication</span>';
					} ?>
					<h4 class="h3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
					<?php the_excerpt(); ?>
					<?php if ( ! isset( $data['no-byline'] ) || '' === trim( $data['no-byline'] ) ) : ?>
						<p class="meta">
							<span class="date"><?php the_time( 'F j, Y' ); ?></span>
							<?php if ( ! get_post_meta( get_the_ID(), '_emmis_la_no_byline', true ) ) : ?>
							<span class="author">
								<?php if ( function_exists( 'coauthors_posts_links' ) ) {
									echo coauthors_posts_links( null, null, null, null, false );
								} else {
									echo esc_html( ucwords( get_the_author() ) );
								} ?>
							</span>
							<?php endif; ?>
						</p>
					<?php endif; ?>
				</div><!-- .post-info -->
		</li>
	<?php
	}

	/**
	 * Display the block, with thumbs.
	 *
	 * @param array $data Data saved in block.
	 * @return void
	 */
	public static function markup_thumbs( $data ) {
		?>
		<li <?php if ( get_post_meta( get_the_ID(), '_emmis_sponsored', true ) || get_post_meta( get_the_ID(), '_emmis_promo', true ) || get_post_meta( get_the_ID(), '_emmis_custom_pub', true ) ) { echo 'class="sponsored"'; } ?>>
			<a href="<?php the_permalink(); ?>">
				<?php if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'thumbnail' );
				} else if ( $image = emmis_get_images( get_the_ID(), 'thumbnail' ) ) {
					echo wp_kses_post( $image[0] );
				} ?>
				<div class="post-info">
					<?php if ( get_post_meta( get_the_ID(), '_emmis_sponsored', true ) ) {
						echo '<span class="sponsored-story">Sponsored</span>';
					} else if ( get_post_meta( get_the_ID(), '_emmis_promo', true ) ) {
						echo '<span class="sponsored-story">Promotion</span>';
					} else if ( get_post_meta( get_the_ID(), '_emmis_custom_pub', true ) ) {
						echo '<span class="sponsored-story">Custom Publication</span>';
					} ?>
					<h4 class="h4"><?php the_title(); ?></h4>
					<?php if ( ! isset( $data['no-byline'] ) || '' === trim( $data['no-byline'] ) ) : ?>
						<p class="meta">
							<?php if ( isset( $data['show-date'] ) && $data['show-date'] ) : ?>
								<span class="date"><?php the_time( 'F j, Y' ); ?></span>
							<?php endif; ?>
							<?php if ( ! get_post_meta( get_the_ID(), '_emmis_la_no_byline', true ) ) : ?>
							<span class="author">
								<?php if ( function_exists( 'coauthors_posts_links' ) ) {
									echo coauthors_posts_links( null, null, null, null, false );
								} else {
									echo esc_html( ucwords( get_the_author() ) );
								} ?>
							</span>
							<?php endif; ?>
						</p>
					<?php endif; ?>
				</div><!-- .post-info -->
			</a>
		</li>
	<?php
	}

}
