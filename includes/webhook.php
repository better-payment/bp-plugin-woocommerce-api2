<?php
include_once 'helpers/config-reader.php';

add_action( 'rest_api_init', function () {
	register_rest_route( 'betterpayment', 'webhook', array(
		'methods' => 'POST',
		'callback' => 'handle_webhook',
		'permission_callback' => '__return_true',
	) );
} );

function handle_webhook (WP_REST_Request $request): WP_REST_Response {
	$params = $request->get_body_params();

	// Calculate checksum without checksum parameter itself and sign it with INCOMING_KEY
	unset($params['checksum']);
	$query = http_build_query($params, '', '&');
	$checksum = sha1($query . Config_Reader::get_incoming_key());

	if ($checksum == $request->get_param('checksum')) {
		$transaction_id = $params['transaction_id'];
		$transaction_status = $params['status'];

		// Find order by transaction_id and update the order status
		$orders = wc_get_orders(['transaction_id' => $transaction_id]);
		if (count($orders) > 0) {
			$order = $orders[0];

			// Map status from Better Payment to WooCommerce
			if ($transaction_status == 'completed') {
				$order->payment_complete();
			}
			else {
				$status = match ($transaction_status) {
					'started', 'pending' => 'on-hold',
					'error', 'declined', 'canceled' => 'failed',
					'refunded', 'chargeback' => 'refunded',
					default => 'pending-payment',
				};

				$order->update_status($status, 'Status updated via webhook.');
			}

			return new WP_REST_Response('Status updated successfully!', 200);
		}
		else {
			return new WP_REST_Response('Order not found!', 404);
		}
	}
	else {
		return new WP_REST_Response('Checksum verification failed!', 401);
	}
}