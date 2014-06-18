# WP Avvo Reviews

This plugin acts as a wrapper to simplify connecting to the AVVO API for WordPress developers.

Below is a basic use case I recently implemented for a client.

```php
	<?php
		$lawyer_id = get_post_meta( $post->ID, 'avvo_lawyer_id', true );

		if( ! empty( $lawyer_id ) ) {
			$avvo_api = new AVVO_Reviews( 'email@gmail.com', 'password' );
			echo $avvo_api->get_reviews_markup( $lawyer_id, true );
		}
	?>
```

## Future Updates

As of now, this plugin is mostly useful for developers, but it can be expanded to be more useful for end users by adding shortcodes and more.