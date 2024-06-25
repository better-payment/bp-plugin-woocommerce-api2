<?php
include_once 'abstract-async-betterpayment-gateway.php';

if (class_exists( 'Abstract_Async_BetterPayment_Gateway' )) {
	class BetterPayment_Credit_Card extends Abstract_Async_BetterPayment_Gateway {
		protected string $shortcode = 'cc';

		public $id = 'betterpayment_cc';
		public $method_title = 'Credit Card (Better Payment)';
		public $method_description = 'Credit Card payment method of Better Payment Gateway';

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
	}
}
