<?php
include_once 'abstract-betterpayment-gateway.php';

if (class_exists('WC_BetterPayment_Gateway')) {
	class WC_BetterPayment_Credit_Card extends WC_BetterPayment_Gateway {
		protected string $shortcode = 'cc';

		public function __construct() {
			$this->id = 'betterpayment_cc';
			$this->icon = '';
			$this->has_fields = false;
			$this->method_title = 'Credit Card (Better Payment)';
			$this->method_description = 'Credit Card payment method of Better Payment Gateway';

			$this->init_form_fields();
			$this->init_settings();

			$this->enabled = $this->get_option('enabled');
			$this->title = $this->get_option('title');

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		public function init_form_fields() {
			$this->form_fields = [
				'enabled' => [
					'title' => 'Enabled',
					'type' => 'checkbox',
					'default' => false
				],
				'title' => [
					'title' => 'Title',
					'type' => 'text',
					'default' => 'Credit Card (Better Payment)',
				]
			];
		}

		public function process_payment( $order_id ) {
			$order = wc_get_order($order_id);
			$parameters = [];
			$parameters += $this->get_common_parameters($order_id);
			$parameters += $this->get_billing_address_parameters($order_id);
			$parameters += $this->get_shipping_address_parameters($order_id);
			$parameters += $this->get_redirect_url_parameters();

			error_log(print_r($parameters, true));

//			$order->update_status('pending-payment', 'Awaiting Credit Card payment');

			return [
				'result' => 'success',
				'redirect' => $this->get_return_url($order)
			];
		}
	}
}
