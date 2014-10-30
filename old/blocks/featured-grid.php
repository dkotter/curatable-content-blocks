<?php
if ( ! class_exists( 'Tenup_Content_Block' ) ) {
	return;
}

class Emmis_Featured_Grid_Content_Block extends Tenup_Content_Block {

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
		<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="featured-grid" />

		<?php if ( class_exists( 'NS_Post_Finder' ) ) : ?>
			<div class="manual hide-if-no-js">
				<?php
				$options['limit'] = 4;
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
		$new['posts'] = isset( $data['posts'] ) ? implode( ',', array_map( 'absint', explode( ',', $data['posts'] ) ) ) : '';

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
		if ( ! isset( $data['posts'] ) || empty( $data['posts'] ) ) {
			// If in manual mode, and we don't have any posts, gracefully fail
			return;
		}

		$post_ids = explode( ',', $data['posts'] );
		$total = count( $post_ids );
		$first_post = array_shift( $post_ids );

		echo '<div class="module module-featuredgrid module-featuredgrid-' . $total . '">';

		self::markup_first( $first_post );

		if ( 3 === $total ) {
			echo '<div class="feat-grid-2-2">';
		} elseif ( 4 === $total ) {
			echo '<div class="feat-grid-3-2">';
		}

		foreach ( $post_ids as $post_id ) {
			global $post;

			$post = get_post( (int) $post_id );
			setup_postdata( $post );

			self::markup( $data );
		}

		echo '</div></div><!-- .module-featuredgrid -->';

		wp_reset_postdata();
	}

	/**
	 * Display the first block.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public static function markup_first( $post_id ) {
		global $post;
		$post = get_post( (int) $post_id );
		setup_postdata( $post );
	?>
		<div class="feat-grid-1-2<?php if ( get_post_meta( get_the_ID(), '_emmis_sponsored', true ) || get_post_meta( get_the_ID(), '_emmis_promo', true ) || get_post_meta( get_the_ID(), '_emmis_custom_pub', true ) ) { echo ' sponsored'; } ?>">
			<a href="<?php the_permalink(); ?>" class="story cover-story">
				<?php if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'medium-large' );
				} else if ( $image = emmis_get_images( get_the_ID() ) ) {
					echo wp_kses_post( $image[0] );
				} ?>
				<div class="cover-story-meta">
					<?php
					$terms = get_the_terms( get_the_ID(), 'emmis-blog' );
					if ( $terms ) {
						$output = '';
						echo '<p class="story-tags">';
						foreach ( $terms as $term ) {
							$output = esc_html( $term->name ) . ', ';
						}
						echo rtrim( $output, ', ' );
						echo '</p>';
					}
					?>
					<?php if ( get_post_meta( get_the_ID(), '_emmis_sponsored', true ) ) {
						echo '<span class="sponsored-story">Sponsored</span>';
					} else if ( get_post_meta( get_the_ID(), '_emmis_promo', true ) ) {
						echo '<span class="sponsored-story">Promotion</span>';
					} else if ( get_post_meta( get_the_ID(), '_emmis_custom_pub', true ) ) {
						echo '<span class="sponsored-story">Custom Publication</span>';
					} ?>
					<h2 class="story-title sans"><?php the_title(); ?></h2>
				</div>
			</a>
		</div><!-- .feat-grid-1-2 -->
	<?php
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
		<a href="<?php the_permalink(); ?>" class="story cover-story<?php if ( get_post_meta( get_the_ID(), '_emmis_sponsored', true ) || get_post_meta( get_the_ID(), '_emmis_promo', true ) || get_post_meta( get_the_ID(), '_emmis_custom_pub', true ) ) { echo ' sponsored'; } ?>">
			<?php if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'medium-large' );
			} else if ( $image = emmis_get_images( get_the_ID(), 'medium-large' ) ) {
				echo wp_kses_post( $image[0] );
			} ?>
			<div class="cover-story-meta">
				<?php
				$terms = get_the_terms( get_the_ID(), 'emmis-blog' );
				if ( $terms ) {
					$output = '';
					echo '<p class="story-tags">';
					foreach ( $terms as $term ) {
						$output = esc_html( $term->name ) . ', ';
					}
					echo rtrim( $output, ', ' );
					echo '</p>';
				}
				?>
				<?php if ( get_post_meta( get_the_ID(), '_emmis_sponsored', true ) ) {
					echo '<span class="sponsored-story">Sponsored</span>';
				} else if ( get_post_meta( get_the_ID(), '_emmis_promo', true ) ) {
					echo '<span class="sponsored-story">Promotion</span>';
				} else if ( get_post_meta( get_the_ID(), '_emmis_custom_pub', true ) ) {
					echo '<span class="sponsored-story">Custom Publication</span>';
				} ?>
				<h2 class="story-title sans"><?php the_title(); ?></h2>
			</div>
		</a>
	<?php
	}

}
