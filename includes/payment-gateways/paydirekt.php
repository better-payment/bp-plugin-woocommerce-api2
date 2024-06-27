<?php
include_once 'abstract-betterpayment-gateway.php';

if (class_exists( 'Abstract_BetterPayment_Gateway' )) {
	class BetterPayment_Paydirekt extends Abstract_BetterPayment_Gateway {
		protected string $shortcode = 'paydirekt';
		protected bool $is_async = true;

		public $id = 'betterpayment_paydirekt';
		public $method_title = 'Paydirekt (Better Payment)';
		public $method_description = 'Paydirekt payment method of Better Payment Gateway';

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
					'default' => 'Paydirekt (Better Payment)',
				]
			];
		}
	}
}
