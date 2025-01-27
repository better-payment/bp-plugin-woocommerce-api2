<?php
add_action('rest_api_init', function () {
	register_rest_route('betterpayment', 'apple-pay-session', array(
		'methods' => 'POST',
		'callback' => 'fetch_apple_pay_session',
		'permission_callback' => '__return_true', // Adjust permissions as needed
	));
});

function fetch_apple_pay_session(): WP_REST_Response {
	$url = Config_Reader::get_api_url() . '/db_apple_pay_merchants';

	$headers = [
		'Content-Type' => 'application/json',
		'Authorization' => 'Basic ' . base64_encode( Config_Reader::get_api_key() . ':' . Config_Reader::get_outgoing_key())
	];

	$body = wp_json_encode([
		'initiative_context' => parse_url(home_url(), PHP_URL_HOST),
	]);

	$response = wp_remote_post( $url, [
		'headers' => $headers,
		'body' => $body,
	]);

	if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
		$responseBody = json_decode( wp_remote_retrieve_body( $response ), true );

		return new WP_REST_Response($responseBody, 200);
	}
	else {
		return new WP_REST_Response(wp_remote_retrieve_body($response), wp_remote_retrieve_response_code($response));
	}
}
