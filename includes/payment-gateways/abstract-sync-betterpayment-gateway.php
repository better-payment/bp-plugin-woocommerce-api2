<?php
include_once 'abstract-betterpayment-gateway.php';
include_once plugin_dir_path( __FILE__ ) . '../helpers/config-reader.php';

if (class_exists( 'Abstract_BetterPayment_Gateway' )) {
	abstract class Abstract_Sync_BetterPayment_Gateway extends Abstract_BetterPayment_Gateway {
		public function process_payment( $order_id ) {
			$order = wc_get_order($order_id);

			$parameters = [];
			$parameters += $this->get_common_parameters($order_id);
			$parameters += $this->get_billing_address_parameters($order_id);
			$parameters += $this->get_shipping_address_parameters($order_id);
			$parameters += $this->get_risk_check_parameters();
			$parameters += $this->get_additional_parameters();
			// TODO: when to include company_parameters?

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
					// TODO: Update to corresponding status received from response ?
					$order->update_status('on-hold', 'Awaiting payment confirmation.');

					$order->set_transaction_id($responseBody['transaction_id']);
					$order->save();

					return [
						'result' => 'success',
						'redirect' => $this->get_return_url($order)
					];
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