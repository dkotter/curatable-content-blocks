<?php
if ( ! class_exists( 'Tenup_Content_Block' ) ) {
	return;
}

class Emmis_Featured_Item_Content_Block extends Tenup_Content_Block {

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
		<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="featured-item" />

		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][show-excerpt]">
				<input type="checkbox" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][show-excerpt]" id="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][show-excerpt]" <?php isset( $data['show-excerpt'] ) ? checked( $data['show-excerpt'], 'y' ) : ''; ?>/>
				Show Excerpt
			</label>
		</p>
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][full-image]">
				<input type="checkbox" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][full-image]" id="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][full-image]" <?php isset( $data['full-image'] ) ? checked( $data['full-image'], 'y' ) : ''; ?>/>
				Use Full Image
			</label>
		</p>

		<?php if ( class_exists( 'NS_Post_Finder' ) ) : ?>
			<div class="hide-if-no-js">
				<?php
				$options['limit'] = 1;
				$options['args'] = array(
					'post_type' => array( 'post', 'emmis-bus-listings', 'tribe_events' )
				);

				// Only admins can see everything
				if ( current_user_can( 'manage_options' ) ) {
					$options['args']['post_status'] = 'any';
				} else {
					$options['args']['post_status'] = array( 'publish', 'pending', 'draft', 'inherit' );
				}

				NS_Post_Finder::render( 'tenup_content_blocks[' . $row . '][' . $area . '][' . $column . '][' . $iterator . '][post]', isset( $data['post'] ) ? $data['post'] : '', $options );
				?>
			</div>
			<hr/>
		<?php endif; ?>

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
		$new['post'] = isset( $data['post'] ) ? implode( ',', array_map( 'absint', explode( ',', $data['post'] ) ) ) : '';
		$new['show-excerpt'] = isset( $data['show-excerpt'] ) ? 'y' : '';
		$new['full-image'] = isset( $data['full-image'] ) ? 'y' : '';

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
		if ( ! isset( $data['post'] ) || empty( $data['post'] ) ) {
			// If we don't have a post, gracefully fail
			return;
		}

		$featured_post = get_post( $data['post'] );
		if ( is_wp_error( $featured_post ) ) {
			return;
		}

		global $post;
		$post = $featured_post;
		setup_postdata( $post );

		if ( isset( $data['show-excerpt'] ) && 'y' == $data['show-excerpt'] ) {
			self::markup_excerpt( $data );
		} else {
			self::markup( $data );
		}

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
		<div class="module module-single-article<?php if ( get_post_meta( get_the_ID(), '_emmis_sponsored', true ) ) { echo ' sponsored'; } ?>">
			<a href="<?php the_permalink(); ?>" class="story cover-story">
				<?php
				$image_size = 'medium';
				if ( isset( $data['full-image'] ) && 'y' == $data['full-image'] ) {
					$image_size = 'full';
				}
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( $image_size );
				} else if ( $image = emmis_get_images( get_the_ID(), $image_size ) ) {
					echo wp_kses_post( $image[0] );
				} ?>
			</a>
				<div class="cover-story-meta">
					<?php if ( get_post_meta( get_the_ID(), '_emmis_sponsored', true ) ) {
						echo '<span class="sponsored-story">Sponsored</span>';
					} ?>
					<h2 class="h2 story-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p><span class="date"><?php the_time( 'F j, Y' ); ?></span></p>
					<p class="story-tags">
						<?php the_terms( get_the_ID(), 'emmis-blog' ); ?>
					</p>
				</div><!-- .cover-story-meta -->
		</div><!-- .module-single-article -->
	<?php
	}

	/**
	 * Display the block, with an excerpt.
	 *
	 * @param array $data Data saved in block.
	 * @return void
	 */
	public static function markup_excerpt( $data ) {
	?>
		<div class="home-posts-list<?php if ( get_post_meta( get_the_ID(), '_emmis_sponsored', true ) ) { echo ' sponsored'; } ?>">
			<div class="post module-post">
				<?php if ( get_post_meta( get_the_ID(), '_emmis_sponsored', true ) ) {
					echo '<span class="sponsored-story">Sponsored</span>';
				} ?>
				<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<div class="caption">
					<a href="<?php the_permalink(); ?>">
						<?php
						$image_size = 'landscape';
						if ( isset( $data['full-image'] ) && 'y' == $data['full-image'] ) {
							$image_size = 'full';
						}
						if ( has_post_thumbnail() ) {
							the_post_thumbnail( $image_size );
						} else if ( $image = emmis_get_images( get_the_ID(), $image_size ) ) {
							echo wp_kses_post( $image[0] );
						} ?>
						<p class="story-tags">
							<?php the_terms( get_the_ID(), 'emmis-blog' ); ?>
						</p>
					</a>
				</div>
				<?php emmis_entry_meta(); ?>
				<?php the_excerpt(); ?>
			</div><!-- .module-post -->
		</div><!-- .home-posts-list -->
	<?php
	}

}
