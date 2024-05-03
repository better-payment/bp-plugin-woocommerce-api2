<?php
if (class_exists('WC_Payment_Gateway')) {
	class WC_Better_Payment_Credit_Card extends WC_Payment_Gateway {
		public function __construct() {
			$this->id = 'better_payment_cc';
//			$this->icon = apply_filters('woocommerce_betterpayment_credit_card_icon', plugins_url());
			$this->has_fields = false;
			$this->method_title = 'Credit Card (Better Payment)';
			$this->method_description = 'Credit Card payment method of Better Payment Gateway';

			$this->title = $this->get_option('enabled');
			$this->title = $this->get_option('title');

			$this->init_form_fields();
			$this->init_settings();
		}

		public function init_form_fields(): void {
			$this->form_fields = apply_filters(
				'woo_betterpayment_fields', [
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
				]
			);
		}

		public function process_payment( $order_id ) {
			$order = wc_get_order($order_id);
			$order->update_status('pending-payment', 'Awaiting Credit Card payment');

			return [
				'result' => 'success',
				'redirect' => $this->get_return_url($order)
			];
		}
	}
}
