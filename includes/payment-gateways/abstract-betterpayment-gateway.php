<?php

if (class_exists('WC_Payment_Gateway')) {
	abstract class Abstract_BetterPayment_Gateway extends WC_Payment_Gateway {
		public $supports = ['refunds'];
		protected string $shortcode;
		protected bool $is_b2b = false;
		protected bool $is_async = false;

		public function __construct() {
			$this->init_form_fields();
			$this->init_settings();

			$this->enabled = $this->get_option('enabled');
			$this->title = $this->get_option('title');

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			if ($this->is_async) {
				add_action( 'woocommerce_thankyou', array($this, 'update_order_status_on_thankyou_page'));
			}
		}

		public function process_payment( $order_id ) {
			$order = wc_get_order($order_id);

			// Check whether b2c or b2b is correctly selected
			if (($this->is_b2b && $order->get_billing_company()) || (!$this->is_b2b && !$order->get_billing_company())) {
				return $this->send_payment_request($order_id);
			}
			else {
				if ($order->get_billing_company()) {
					wc_add_notice( 'Please select B2B type payment method', 'error' );
				}
				else {
					wc_add_notice( 'Please select non-B2B type payment method', 'error' );
				}
			}
		}

		private function send_payment_request($order_id) {
			$order = wc_get_order($order_id);

			$url     = Config_Reader::get_api_url() . '/rest/payment';
			$body    = wp_json_encode( $this->get_parameters($order_id) );
			$headers = [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . base64_encode( Config_Reader::get_api_key() . ':' . Config_Reader::get_outgoing_key() )
			];

			$response = wp_remote_post( $url, [
				'headers' => $headers,
				'body'    => $body,
			] );

			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$responseBody = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( $responseBody['error_code'] == 0 ) {
					$order->set_transaction_id( $responseBody['transaction_id'] );
					$order->save();

					$transaction_status = $responseBody['status'];

					// Map status from Better Payment to WooCommerce
					if ( $transaction_status == 'completed' ) {
						$order->payment_complete();
					} else {
						$status = match ( $transaction_status ) {
							'started', 'pending' => 'on-hold',
							'error', 'declined', 'canceled' => 'failed',
							'refunded', 'chargeback' => 'refunded',
							default => 'pending-payment',
						};

						$order->update_status( $status, 'Status updated from Payment Gateway.' );
					}

					return [
						'result'   => 'success',
						'redirect' => $responseBody['action_data']['url'] ?? $this->get_return_url( $order )
					];
				} else {
					$order->update_status( 'failed', $responseBody['error_message'] );
					wc_add_notice( $responseBody['error_message'], 'error' );
				}
			} else {
				$order->update_status( 'failed', 'Payment failed.' );
				wc_add_notice( 'Connection error.', 'error' );
			}
		}

		// Payment request order parameters
		private function get_parameters( $order_id ): array {
			$parameters = [];
			$parameters += $this->get_common_parameters( $order_id );
			$parameters += $this->get_billing_address_parameters( $order_id );
			$parameters += $this->get_shipping_address_parameters( $order_id );

			if ($this->is_async) {
				$parameters += $this->get_redirect_url_parameters( $order_id );
			}
			else {
				$parameters += $this->get_risk_check_parameters();
				$parameters += $this->get_additional_parameters();
				$parameters += $this->get_company_parameters( $order_id );
			}

			return $parameters;
		}

		private function get_common_parameters($order_id): array
		{
			$order = wc_get_order($order_id);

			return [
				// payment method shortcode
				'payment_type' => $this->shortcode,
				// always enabled
				'risk_check_approval' => '1',
				// The URL for updates about transaction status are posted
				'postback_url' => set_url_scheme(get_rest_url(path: 'betterpayment/webhook'), 'https'),
				// Any alphanumeric string to identify the Merchant’s order
				'order_id' => $order->get_order_number(),
				// Any alphanumeric string to provide the customer number of a Merchant’s order (up to 40 characters) for factoring or debt collection
				'customer_id' => $order->get_customer_id(),
				// See details about merchant reference - https://testdashboard.betterpayment.de/docs/#merchant-reference
				'merchant_reference' => $order->get_order_number() . ' - ' . get_bloginfo('name'),
				// Including possible shipping costs and VAT (float number)
				'amount' => $order->get_total(),
				// Should be set if the order includes any shipping costs (float number)
				'shipping_costs' => $order->get_shipping_total(),
				// VAT amount (float number) if known
				'vat' => $order->get_total_tax(),
				// 3-letter currency code (ISO 4217). Defaults to ‘EUR’
				'currency' => $order->get_currency(),
				// If the order includes a risk check, this field can be set to prevent customers from making multiple order attempts with different personal information.
				'customer_ip' => $order->get_customer_ip_address(),
				// The language of payment forms in Credit Card and Paypal. Possible locale values - https://testdashboard.betterpayment.de/docs/#locales
				// use substr to convert en_US to en
				'locale' => get_user_locale(),
				// module/plugin metadata
				'app_name' => Config_Reader::get_app_name(),
				'app_version' => Config_Reader::get_app_version(),
			];
		}

		private function get_billing_address_parameters($order_id): array
		{
			$order = wc_get_order($order_id);

			return [
				// Street address
				'address' => $order->get_billing_address_1(),
				// Second address line
				'address2' => $order->get_billing_address_2(),
				// The town, district or city of the billing address
				'city' => $order->get_billing_city(),
				// The postal code or zip code of the billing address
				'postal_code' => $order->get_billing_postcode(),
				// The county, state or region of the billing address
				'state' => $order->get_billing_state(),
				// Country Code in ISO 3166-1
				'country' => $order->get_billing_country(),
				// Customer’s first name
				'first_name' => $order->get_billing_first_name(),
				// Customer’s last name
				'last_name' => $order->get_billing_last_name(),
				// Customer’s last email. We suggest to provide an email when transaction's payment method type is CC(credit card) to avoid declines in 3DS2.
				'email' => $order->get_billing_email(),
				// Customer’s phone number
				'phone' => $order->get_billing_phone(),
			];
		}

		private function get_shipping_address_parameters($order_id): array
		{
			$order = wc_get_order($order_id);

			return [
				// Street address
				'shipping_address' => $order->get_shipping_address_1(),
				// Second address line
				'shipping_address2' => $order->get_shipping_address_2(),
				// Name of the company of the given shipping address
				'shipping_company' => $order->get_shipping_company(),
				// The town, district or city of the shipping address
				'shipping_city' => $order->get_shipping_city(),
				// The postal code or zip code of the shipping address
				'shipping_postal_code' => $order->get_shipping_postcode(),
				// The county, state or region of the shipping address
				'shipping_state' => $order->get_shipping_state(),
				// Country Code in ISO 3166-1 alpha2
				'shipping_country' => $order->get_shipping_country(),
				// Customer’s first name
				'shipping_first_name' => $order->get_shipping_first_name(),
				// Customer’s last name
				'shipping_last_name' => $order->get_shipping_last_name(),
			];
		}

		private function get_redirect_url_parameters($order_id): array
		{
			$order = wc_get_order( $order_id );

			return [
				'success_url' => $order->get_checkout_order_received_url(), // or we can use $this->get_return_url($order)
				'error_url' => $order->get_cancel_order_url(), // or we can use get_checkout_payment_url()
			];
		}

		private function get_company_parameters($order_id): array
		{
			if ($this->is_b2b) {
				$order = wc_get_order($order_id);

				return [
					// Company name
					'company' => $order->get_billing_company(),
					// Starts with ISO 3166-1 alpha2 followed by 2 to 11 characters. See more details about Vat - http://ec.europa.eu/taxation_customs/vies/
					'company_vat_id' => '',
				];
			}

			return [];
		}

		private function get_risk_check_parameters(): array
		{
			$parameters = [];

			if (!empty($_POST[$this->id . '_date_of_birth'])) {
				$parameters += [
					'date_of_birth' => $_POST[$this->id . '_date_of_birth']
				];
			}

			if (!empty($_POST[$this->id . '_gender'])) {
				$parameters += [
					'gender' => $_POST[$this->id . '_gender']
				];
			}

			return $parameters;
		}

		private function get_additional_parameters(): array
		{
			return match ($this->shortcode) {
				'dd', 'dd_b2b' => [
					'account_holder' => $_POST[$this->id . '_account_holder'],
					'iban' => $_POST[$this->id . '_iban'],
					'bic' => $_POST[$this->id . '_bic'],
					'sepa_mandate' => $_POST[$this->id . '_mandate_reference'],
				],
				// add other payment method specific additional data here
				default => [],
			};
		}
		// Payment request order parameters END

		public function update_order_status_on_thankyou_page( $order_id ): void {
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

		public function process_refund( $order_id, $amount = null, $reason = '' ) {
			$order = wc_get_order( $order_id );
			$parameters = [
				'transaction_id' => $order->get_transaction_id(),
				'amount' => $amount,
				'comment' => $reason,
			];

			$url     = Config_Reader::get_api_url() . '/rest/refund';
			$body    = wp_json_encode( $parameters );
			$headers = [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . base64_encode( Config_Reader::get_api_key() . ':' . Config_Reader::get_outgoing_key() )
			];

			$response = wp_remote_post( $url, [
				'headers' => $headers,
				'body'    => $body,
			] );

			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$responseBody = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( $responseBody['error_code'] == 0 ) {
					return true;
				} else {
					return new WP_Error( 'error', $responseBody['error_message'] );
				}
			} else {
				return new WP_Error( 'error', 'Connection error.' );
			}
		}
	}
}