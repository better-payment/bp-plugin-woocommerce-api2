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
	$body    = $request->get_json_params();
	// Add the payment type to the body
	$body['payment_type'] = $body['googlepay_token'] ? 'googlepay' : 'applepay';

	$headers = [
		'Content-Type'  => 'application/json',
		'Authorization' => 'Basic ' . base64_encode( Config_Reader::get_api_key() . ':' . Config_Reader::get_outgoing_key() )
	];
	try {
		$response = wp_remote_post( $url, [
			'headers' => $headers,
			'body'    => wp_json_encode($body),
		] );

		$response_body = json_decode(wp_remote_retrieve_body($response),true);

		return new WP_REST_Response($response_body, wp_remote_retrieve_response_code($response));

	} catch ( Exception $e ) {
		return new WP_REST_Response( [
		'success' => false,
		'message' => __('Something went wrong', 'bp-plugin-woocommerce-api2')
		], 500 );
	}
}
