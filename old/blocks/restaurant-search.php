<?php
if ( ! class_exists( 'Tenup_Content_Block' ) ) {
	return;
}

class Emmis_Restaurant_Search_Content_Block extends Emmis_Listing_Search_Content_Block {

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
		<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="restaurant-search" />

		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]">Title (or <a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>" target="_blank">image URL</a>)</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]" class="widefat" value="<?php echo isset( $data['title'] ) ? esc_attr( $data['title'] ) : ''; ?>"/>
		</p>
	<?php
	}

	/**
	 * Display block.
	 *
	 * @param array $data Data saved in block.
	 * @param string $area Current area.
	 * @return void
	 */
	public static function display( $data, $area ) {
	?>
		<div class="module module-listing-search">
			<?php
			if ( isset( $data['title'] ) && '' !== trim( $data['title'] ) ) {
				echo '<h2 class="h2">';
				if ( 'http' === substr( $data['title'], 0, 4 ) || 'www' === substr( $data['title'], 0, 3 ) ) {
					echo '<img src="'. esc_url( $data['title'] ) .'">';
				} else {
					echo esc_html( $data['title'] );
				}
				echo '</h2>';
			}
			?>
			<form action="/business-category/restaurants/" method="get" id="business-map-form">
				<input type="text" name="key" id="key" placeholder="Find a great place to dine!">
				<select name="loc" id="loc">
					<option value="">Select Location</option>
					<?php
					$location_sections = get_terms( 'emmis-location', array( 'fields' => 'id=>name', 'hide_empty' => 0, ) );
					foreach ( $location_sections as $parent_id => $section ) : ?>
						<option value="<?php echo esc_attr( $parent_id ); ?>"><?php echo esc_html( $section ); ?></option>
						<?php
						$child_locations = get_terms( 'emmis-location', array( 'fields' => 'id=>name', 'parent' => $parent_id ) );
						foreach ( $child_locations as $child_id => $child_loc ) : ?>
							<option value="<?php echo esc_attr( $child_id ); ?>">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo esc_html( $child_loc ); ?></option>
							<?php
							$grandchild_locations = get_terms( 'emmis-location', array( 'fields' => 'id=>name', 'parent' => $child_id ) );
							foreach ( $grandchild_locations as $grandchild_id => $grandchild_loc ) : ?>
								<option value="<?php echo esc_attr( $grandchild_id ); ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo esc_html( $grandchild_loc ); ?></option>
								<?php
								$greatgrandchild_locations = get_terms( 'emmis-location', array( 'fields' => 'id=>name', 'parent' => $grandchild_id ) );
								foreach ( $greatgrandchild_locations as $greatgrandchild_id => $greatgrandchild_loc ) : ?>
									<option value="<?php echo esc_attr( $greatgrandchild_id ); ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo esc_html( $greatgrandchild_loc ); ?></option>
								<?php endforeach; ?>
							<?php endforeach; ?>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</select>
				<select name="bcat" id="bcat">
					<option value="">Select Category</option>
					<?php
					$restaurant = get_term_by( 'slug', 'restaurants', 'business-category' );
					$categories = get_terms( 'business-category', array( 'fields' => 'id=>name', 'parent' => $restaurant->term_id ) );
					foreach ( $categories as $id => $category ) : ?>
						<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $category ); ?></option>
					<?php endforeach; ?>
				</select>
				<select name="price" id="price">
					<option value="">Select Price</option>
					<option value="1">$ Under $10</option>
					<option value="2">$$ 10-20</option>
					<option value="3">$$$ 20-30</option>
					<option value="4">$$$$ 30+</option>
				</select>
				<input type="submit" value="Search">
			</form>
		</div><!-- .module-listing-search -->
	<?php
	}

}
