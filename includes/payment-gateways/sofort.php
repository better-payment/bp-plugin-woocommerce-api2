<?php
include_once 'abstract-betterpayment-gateway.php';

if (class_exists( 'Abstract_BetterPayment_Gateway' )) {
	class BetterPayment_Sofort extends Abstract_BetterPayment_Gateway {
		protected string $shortcode = 'sofort';
		protected bool $is_async = true;

		public $id = 'betterpayment_sofort';
		public $method_title = 'Sofort (Better Payment)';
		public $method_description = 'Sofort payment method of Better Payment Gateway';

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
					'default' => 'Sofort (Better Payment)',
				]
			];
		}
	}
}
