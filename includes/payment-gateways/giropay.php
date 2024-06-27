<?php
include_once 'abstract-betterpayment-gateway.php';

if (class_exists( 'Abstract_BetterPayment_Gateway' )) {
	class BetterPayment_Giropay extends Abstract_BetterPayment_Gateway {
		protected string $shortcode = 'giro';
		protected bool $is_async = true;

		public $id = 'betterpayment_giro';
		public $method_title = 'Giropay (Better Payment)';
		public $method_description = 'Giropay payment method of Better Payment Gateway';

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
					'default' => 'Giropay (Better Payment)',
				]
			];
		}
	}
}
