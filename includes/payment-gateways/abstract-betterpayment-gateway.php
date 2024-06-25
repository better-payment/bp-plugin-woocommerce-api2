<?php

if (class_exists('WC_Payment_Gateway')) {
	abstract class Abstract_BetterPayment_Gateway extends WC_Payment_Gateway {
		protected string $shortcode;
		protected bool $is_b2b = false;

		public function __construct() {
			$this->init_form_fields();
			$this->init_settings();

			$this->enabled = $this->get_option('enabled');
			$this->title = $this->get_option('title');

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		protected function get_common_parameters($order_id): array
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
				'locale' => 'en', // TODO: fetch dynamically
				// module/plugin metadata
				'app_name' => Config_Reader::get_app_name(),
				'app_version' => Config_Reader::get_app_version(),
			];
		}

		protected function get_billing_address_parameters($order_id): array
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

		protected function get_shipping_address_parameters($order_id): array
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

		protected function get_redirect_url_parameters($order_id): array
		{
			$order = wc_get_order( $order_id );

			return [
				'success_url' => $order->get_checkout_order_received_url(), // or we can use $this->get_return_url($order)
				'error_url' => $order->get_cancel_order_url(), // or we can use get_checkout_payment_url()
			];
		}

		protected function get_company_parameters($order_id): array
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

		protected function get_risk_check_parameters(): array
		{
			$parameters = [];

			if (!empty($_POST['date_of_birth'])) {
				$parameters += [
					'date_of_birth' => $_POST['date_of_birth']
				];
			}

			if (!empty($_POST['gender'])) {
				$parameters += [
					'gender' => $_POST['gender']
				];
			}

			return $parameters;
		}

		protected function get_additional_parameters(): array
		{
			return match ($this->shortcode) {
				'dd', 'dd_b2b' => [
					'account_holder' => $_POST['account_holder'],
					'iban' => $_POST['iban'],
					'bic' => $_POST['bic'],
					'sepa_mandate' => $_POST['mandate_reference'],
				],
				// add other payment method specific additional data here
				default => [],
			};
		}
	}
}