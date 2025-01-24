<?php
add_action('rest_api_init', function () {
	register_rest_route('betterpayment', 'payment', array(
		'methods' => 'POST',
		'callback' => 'payment',
		'permission_callback' => '__return_true', // Adjust permissions as needed
	));
});

// Only used for Apple Pay
function payment(WP_REST_Request $request): WP_REST_Response {
	$url     = Config_Reader::get_api_url() . '/rest/payment';
	$body    = $request->get_body();

	// TODO: Pass payment type here = applepay

	$headers = [
		'Content-Type'  => 'application/json',
		'Authorization' => 'Basic ' . base64_encode( Config_Reader::get_api_key() . ':' . Config_Reader::get_outgoing_key() )
	];

	$response = wp_remote_post( $url, [
		'headers' => $headers,
		'body'    => $body,
	] );

	return new WP_REST_Response(wp_remote_retrieve_body($response), wp_remote_retrieve_response_code($response));
}
