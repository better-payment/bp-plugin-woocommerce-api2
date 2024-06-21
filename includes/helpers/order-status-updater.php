<?php
// Hook into the WooCommerce checkout order received URL - thankyou page
add_action( 'woocommerce_thankyou', 'update_order_status_on_successful_payment');

function update_order_status_on_successful_payment( $order_id ): void {
	if ( ! $order_id ) {
		return;
	}

	$order = wc_get_order( $order_id );
	$transaction_id = $order->get_transaction_id();

	if ($transaction_id) {
		$url = Config_Reader::get_api_url() . '/rest/transactions/' . $transaction_id;
		$headers = [
			'Content-Type' => 'application/json',
			'Authorization' => 'Basic ' . base64_encode( Config_Reader::get_api_key() . ':' . Config_Reader::get_outgoing_key())
		];

		$response = wp_remote_get( $url, [
			'headers' => $headers,
		]);

		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			$responseBody = json_decode( wp_remote_retrieve_body( $response ), true );

			if (!isset($responseBody['error_code'])) {
				$transaction_status = $responseBody['status'];

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

					$order->update_status($status, 'Status updated from Payment Gateway.');
				}
			}
			else {
				$order->update_status('failed', $responseBody['error_message']);
				wc_add_notice($responseBody['error_message'], 'error');
			}
		} else {
			$order->update_status('failed', 'Payment failed.');
			wc_add_notice( 'Connection error.', 'error' );
		}
	}
}

