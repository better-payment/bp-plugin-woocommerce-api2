<?php
include_once 'abstract-betterpayment-gateway.php';

if (class_exists( 'Abstract_BetterPayment_Gateway' )) {
	class BetterPayment_Credit_Card extends Abstract_BetterPayment_Gateway {
		protected string $shortcode = 'cc';
		protected bool $is_async = true;

		public function __construct() {
			$this->id = 'betterpayment_cc';
			$this->method_title = __( 'Credit Card (Better Payment)', 'bp-plugin-woocommerce-api2' );
			$this->method_description = __( 'Credit Card payment method of Better Payment', 'bp-plugin-woocommerce-api2' );

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
					'default' => __( 'Credit Card (Better Payment)', 'bp-plugin-woocommerce-api2' ),
				]
			];
		}
	}
}
