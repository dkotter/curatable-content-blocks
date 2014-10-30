<?php
if ( ! class_exists( 'Tenup_Content_Block' ) ) {
	return;
}

class Emmis_Newsletter_Content_Block extends Tenup_Content_Block {

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
		<input type="hidden" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][type]" value="newsletter" />
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]">Title (or <a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>" target="_blank">image URL</a>)</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][title]" class="widefat" value="<?php echo isset( $data['title'] ) ? esc_attr( $data['title'] ) : ''; ?>"/>
		</p>
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][sub_title]">Sub Title (or <a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>" target="_blank">image URL</a>)</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][sub_title]" class="widefat" value="<?php echo isset( $data['sub_title'] ) ? esc_attr( $data['sub_title'] ) : ''; ?>"/>
		</p>
		<p>
			<label for="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][image]">Image</label>
			<input type="text" name="tenup_content_blocks[<?php echo esc_attr( $row ); ?>][<?php echo esc_attr( $area ); ?>][<?php echo esc_attr( $column ); ?>][<?php echo esc_attr( $iterator ); ?>][image]" class="widefat" value="<?php echo isset( $data['image'] ) ? esc_attr( $data['image'] ) : ''; ?>"/>
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
		$new = array();

		$new['pause'] = isset( $data['pause'] ) ? 'y' : '';
		$new['type'] = sanitize_key( $data['type'] );

		$new['title'] = isset( $data['title'] ) ? wp_filter_post_kses( $data['title'] ) : '';
		$new['sub_title'] = isset( $data['sub_title'] ) ? wp_filter_post_kses( $data['sub_title'] ) : '';
		$new['image'] = isset( $data['image'] ) ? esc_url_raw( $data['image'] ) : '';

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
	?>
		<div id="newsletter" class="module module-newsletter">
			<?php if ( isset( $data['title'] ) && '' !== trim( $data['title'] ) ) : ?>
				<h2 class="h2"><?php echo esc_html( $data['title'] ); ?></h2>
			<?php endif; ?>
			<?php if ( isset( $data['sub_title'] ) && '' !== trim( $data['sub_title'] ) ) : ?>
				<p class="h3"><?php echo esc_html( $data['sub_title'] ); ?></p>
			<?php endif; ?>

			<?php if ( isset( $_GET['newsletter'] ) && 'success' === $_GET['newsletter'] ) : ?>
				<div class="success">Thanks for signing up! Expect to see some newsletters soon.</div>
			<?php elseif ( isset( $_GET['signup'] ) ) : ?>
				<?php if ( 'success' === $_GET['signup'] ) : ?>
					<div class="success">Thanks for signing up! Check your e-mail to confirm your Insider account.</div>
				<?php elseif ( 'failure' === $_GET['signup'] ) : ?>
					<div class="success">Account not created! Email already exists. <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">Forgot password</a>?</div>
				<?php endif; ?>
			<?php else : ?>
				<form action="/" method="post" autocomplete="off">
					<?php if ( isset( $data['image'] ) && '' !== trim ( $data['image'] ) ) : ?>
						<img src="<?php echo esc_url( $data['image'] ); ?>" alt="" class="alignleft">
					<?php endif; ?>
					<p class="clear">Choose your newsletters and join the conversation:</p>
					<?php do_action( 'emmis_output_newsletters' ); ?>
					<i class="icon-envelope"></i>
					<input type="text" name="nl-email" id="nl-email">
					<input type="checkbox" name="nl-insider" id="nl-insider">
					<label for="nl-insider" class="nl-insider">I want access to Insider-only stories and contests.</label>
					<div class="insider">
						<h3><?php echo apply_filters( 'emmis_newsletter_insider_text', 'Become an IM Insider' ); ?></h3>
						<p>We just need a little more information to serve you better.</p>
						<div class="row">
							<div class="input-group col-1-2">
								<label for="nl-zip">Zip</label>
								<input type="text" name="nl-zip" id="nl-zip" value="" autocomplete="off">
							</div>
							<div class="input-group col-1-2">
								<label for="nl-dob">Date of Birth</label>
								<input type="text" name="nl-dob" id="nl-dob" value="" placeholder="MM/DD/YY" autocomplete="off">
							</div>
						</div>
						<label for="nl-password">Password</label>
						<input type="password" name="nl-password" id="nl-password" value="" autocomplete="off">
					</div><!-- .insider -->
					<div>
						<input type="submit" name="nl-subscribe" id="nl-subscribe" value="Sign me up">
					</div>
				</form>
			<?php endif; ?>
		</div><!-- .module-newsletter -->
	<?php
	}

}