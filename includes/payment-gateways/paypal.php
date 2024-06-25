<?php
include_once 'abstract-betterpayment-gateway.php';

if (class_exists( 'Abstract_BetterPayment_Gateway' )) {
	class BetterPayment_PayPal extends Abstract_BetterPayment_Gateway {
		protected string $shortcode = 'paypal';
		protected bool $is_async = true;

		public $id = 'betterpayment_paypal';
		public $method_title = 'PayPal (Better Payment)';
		public $method_description = 'PayPal payment method of Better Payment Gateway';

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
