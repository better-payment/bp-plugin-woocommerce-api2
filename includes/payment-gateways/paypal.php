<?php
include_once 'abstract-async-betterpayment-gateway.php';

if (class_exists( 'Abstract_Async_BetterPayment_Gateway' )) {
	class BetterPayment_PayPal extends Abstract_Async_BetterPayment_Gateway {
		public function __construct() {
			$this->id = 'betterpayment_paypal';
			$this->icon = '';
			$this->has_fields = false;
			$this->method_title = 'PayPal (Better Payment)';
			$this->method_description = 'PayPal payment method of Better Payment Gateway';

			$this->init_form_fields();
			$this->init_settings();

			$this->enabled = $this->get_option('enabled');
			$this->title = $this->get_option('title');

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		public function init_form_fields(): void {
			$this->form_fields = [
				'enabled' => [
					'title' => 'Enabled',
					'type' => 'checkbox',
					'default' => false
				],
				'title' => [
					'title' => 'Title',
					'type' => 'text',
					'default' => 'PayPal (Better Payment)',
				]
			];
		}
	}
}
