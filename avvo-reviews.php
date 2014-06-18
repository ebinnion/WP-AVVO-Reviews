<?php
/**
 * Plugin Name: WP AVVO Reviews
 * Plugin URI: http://crane-west.com
 * Description: Plugin that will help with retrieving lawyer reviews from the AVVO API.
 * Version: 0.1
 * Author: Eric Binnion
 * Author URI: http://manofhustle.com
 * License: GPLv2 or later
 * Text Domain: avvo-reviews
 */

class AVVO_Reviews {

	const API_URL = 'https://api.avvo.com/api/1';

	/**
	 * Since the AVVO API uses basic authentication, which requires passing the username and password
	 * with every request, the constructor requires that a username and password be passed to it.
	 *
	 * @param string $user     The lawyer's email address.
	 * @param string $password The lawyer's AVVO password.
	 */
	function __construct( $user = '', $password = '' ) {

		if ( empty( $user ) || empty( $password ) ) {
			wp_die( esc_html__( 'You must pass a username and a password to use the AVVO_Reviews class.', 'avvo-reviews' ) );
		}

		$this->user_email = $user;
		$this->password = $password;
	}

	function get_reviews_markup( $lawyer_id ) {
		$reviews = $this->get_reviews( $lawyer_id );

		if( ! empty( $reviews ) ) : ?>

			<div class="avvo-reviews">
				<h2>Testimonials for <?php the_title(); ?></h2>

				<?php foreach ( $reviews as $review) : ?>
					<div class="avvo-review">
						<div class="rating">
							<?php
								$total = 5;
								$rating = $review->overall_rating;

								for( $i = 0; $i < $total; $i++ ) {
									if ( $i < $rating ) {
										echo '<span>&#9733;</span>';
									} else {
										echo '<span>&#9734;</span>';
									}
								}
							?>
						</div>

						<h4><?php echo $review->title; ?></h4>

						<blockquote>
							<?php echo $review->body; ?>
							<a target="_blank" href="<?php echo $review->url; ?>" alt="Read more of <?php echo $review->posted_by; ?>&apos;s Review">Read&nbsp;More&nbsp;&rarr;</a>
							<br />
							<br />
							&mdash; <?php echo $review->posted_by; ?>
						</blockquote>

					</div>
				<?php endforeach; ?>
			</div>

		<?php endif;
	}

	function get_reviews( $lawyer_id ) {
		if( isset( $lawyer_id) ) {
			return $this->make_request( "/lawyers/{$lawyer_id}/reviews.json" );
		}
	}

	private function make_request( $endpoint ) {
		$request = wp_remote_get(
			self::API_URL . $endpoint,
			array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( "{$this->user_email}:{$this->password}" )
				)
			)
		);

		if ( is_wp_error( $request ) ) {
			return $request;
		} else {
			return json_decode( wp_remote_retrieve_body( $request ) );
		}
	}
}