<?php
include_once 'abstract-betterpayment-gateway.php';

if (class_exists( 'Abstract_BetterPayment_Gateway' )) {
	abstract class Abstract_Async_BetterPayment_Gateway extends Abstract_BetterPayment_Gateway {
		public function __construct() {
			parent::__construct();
			
			add_action( 'woocommerce_thankyou', array($this, 'update_order_status_on_thankyou_page'));
		}

		public function process_payment( $order_id ) {
			$order = wc_get_order($order_id);

			$parameters = [];
			$parameters += $this->get_common_parameters($order_id);
			$parameters += $this->get_billing_address_parameters($order_id);
			$parameters += $this->get_shipping_address_parameters($order_id);
			$parameters += $this->get_redirect_url_parameters($order_id);

			$url = Config_Reader::get_api_url() . '/rest/payment';
			$body = wp_json_encode($parameters);
			$headers = [
				'Content-Type' => 'application/json',
				'Authorization' => 'Basic ' . base64_encode( Config_Reader::get_api_key() . ':' . Config_Reader::get_outgoing_key())
			];

			$response = wp_remote_post( $url, [
				'headers' => $headers,
				'body' => $body,
			]);

			if( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$responseBody = json_decode( wp_remote_retrieve_body( $response ), true );

				if ($responseBody['error_code'] == 0) {
					$order->set_transaction_id($responseBody['transaction_id']);
					$order->save();

					if (isset($responseBody['action_data']['url'])) {
						return [
							'result' => 'success',
							'redirect' => $responseBody['action_data']['url']
						];
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

		function update_order_status_on_thankyou_page( $order_id ): void {
			error_log(print_r($order_id, true));

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
	}
}