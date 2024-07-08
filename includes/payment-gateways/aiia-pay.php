<?php
include_once 'abstract-betterpayment-gateway.php';

if (class_exists( 'Abstract_BetterPayment_Gateway' )) {
	class BetterPayment_Aiia_Pay extends Abstract_BetterPayment_Gateway {
		protected string $shortcode = 'aiia';
		protected bool $is_async = true;

		public function __construct() {
			$this->id = 'betterpayment_aiia';
			$this->method_title = __( 'Aiia Pay (Better Payment)', 'bp-plugin-woocommerce-api2' );
			$this->method_description = __( 'Aiia Pay payment method of Better Payment', 'bp-plugin-woocommerce-api2' );

			parent::__construct();
		}

		public function init_form_fields() {
			$this->form_fields = [
				'enabled' => [
					'title' => __( 'Enabled', 'bp-plugin-woocommerce-api2' ),
					'type' => 'checkbox',
					'default' => false
				],
				'title' => [
					'title' => __( 'Title', 'bp-plugin-woocommerce-api2' ),
					'type' => 'text',
					'default' => __( 'Aiia Pay (Better Payment)', 'bp-plugin-woocommerce-api2' ),
				]
			];
		}
	}
}
