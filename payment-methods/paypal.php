<?php
if (class_exists('WC_Payment_Gateway')) {
	class WC_Better_Payment_PayPal extends WC_Payment_Gateway {
		public function __construct() {
			$this->id = 'better_payment_paypal';
			$this->icon = '';
			$this->has_fields = false;
			$this->method_title = 'PayPal (Better Payment)';
			$this->method_description = 'PayPal payment method of Better Payment Gateway';

			$this->title = $this->get_option('title');

			$this->init_form_fields();
			$this->init_settings();
		}

		public function init_form_fields(): void {
			$this->form_fields = apply_filters(
				'woo_better_payment_fields', [
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
				]
			);
		}

//		public function process_payment( $order_id ) {
//			$order = wc_get_order($order_id);
//			$order->update_status('pending-payment', 'Awaiting Credit Card payment');
//
//			return [
//				'result' => 'success',
//				'redirect' => $this->get_return_url($order)
//			];
//		}
	}
}
