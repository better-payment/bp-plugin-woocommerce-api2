<?php
include_once 'abstract-betterpayment-gateway.php';

if (class_exists( 'Abstract_BetterPayment_Gateway' )) {
	class BetterPayment_Aiia_Pay extends Abstract_BetterPayment_Gateway {
		protected string $shortcode = 'aiia';
		protected bool $is_async = true;

		public $id = 'betterpayment_aiia';
		public $method_title = 'Aiia Pay (Better Payment)';
		public $method_description = 'Aiia Pay payment method of Better Payment Gateway';

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
					'default' => 'Aiia Pay (Better Payment)',
				]
			];
		}
	}
}
