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

	/**
	 * Returns the reviews markup for a lawyer when given a valid AVVO lawyer ID.
	 *
	 * @param  integer $lawyer_id The lawyer's AVVO ID.
	 */
	function get_reviews_markup( $lawyer_id ) {
		$reviews = $this->get_reviews( $lawyer_id );

		if( ! empty( $reviews ) ) : ?>

			<div class="avvo-reviews">
				<h2>Testimonials for <?php the_title(); ?></h2>

				<?php foreach ( $reviews as $review) : ?>
					<div class="avvo-review">
						<?php $this->generate_star_rating( $review->overall_rating ); ?>

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

	/**
	 * Returns an array of review objects when given a valid AVVO lawyer ID.
	 *
	 * @param  integer $lawyer_id the lawyer's AVVO ID.
	 */
	function get_reviews( $lawyer_id ) {
		$reviews = $this->fetch_results( "/lawyers/{$lawyer_id}/reviews.json" );

		if( is_wp_error( $reviews ) ) {
			$reviews = false;
		}

		return $reviews;
	}

	/**
	 * Generates the rating stars markup for.
	 *
	 * @param  integer $rating The rating of the current review.
	 */
	private function generate_star_rating( $rating = 0 ) {

		if( 0 != intval( $rating ) ) : ?>

			<div class="rating">
				<?php
					for( $i = 0; $i < 5; $i++ ) {
						if ( $i < $rating ) {
							echo '<span>&#9733;</span>';
						} else {
							echo '<span>&#9734;</span>';
						}
					}
				?>
			</div>

		<?php endif;
	}

	/**
	 * Will check if results have already been fetched for same request.
	 * @param  string $endpoint Endpoint representing what information to return.
	 * @return WP_Error|array   Return array of reviews objects on success or WP_Error on faillure.
	 */
	private function fetch_results( $endpoint ) {

		/*
		 * Check if a request for this endpoint has already been cached. If so, use those results.
		 * If not, fetch fresh results.
		 */
		if( false == ( $results = get_transient( $endpoint ) ) ) {
			$results = $this->make_request( $endpoint );

			// Only cache results if they are not an error.
			if( ! is_wp_error( $results ) ) {

				// Cache results for 1 hour.
				set_transient( $endpoint, $results, 1440 );
			}
		}

		return $results;
	}

	/**
	 * Makes the API request to AVVO's API.
	 * @param  string $endpoint Endpoint representing what information to return.
	 * @return WP_Error|array   Return array of reviews objects on success or WP_Error on faillure.
	 */
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
