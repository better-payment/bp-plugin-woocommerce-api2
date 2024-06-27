<?php
include_once 'abstract-betterpayment-gateway.php';

if (class_exists( 'Abstract_BetterPayment_Gateway' )) {
	class BetterPayment_Request_To_Pay extends Abstract_BetterPayment_Gateway {
		protected string $shortcode = 'rtp';
		protected bool $is_async = true;

		public $id = 'betterpayment_rtp';
		public $method_title = 'Request To Pay (Better Payment)';
		public $method_description = 'Request To Pay payment method of Better Payment Gateway';

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
					'default' => 'Request To Pay (Better Payment)',
				]
			];
		}
	}
}
