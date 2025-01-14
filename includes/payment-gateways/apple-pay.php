<?php

if (class_exists( 'WC_Payment_Gateway' )) {
	class BetterPayment_Apple_Pay extends WC_Payment_Gateway {
		protected string $shortcode = 'applepay';

		public function __construct() {
			$this->id = 'betterpayment_applepay';
			$this->method_title = __( 'Apple Pay (Better Payment)', 'bp-plugin-woocommerce-api2' );
			$this->method_description = __( 'Apple Pay payment method of Better Payment', 'bp-plugin-woocommerce-api2' );

			$this->init_form_fields();
			$this->init_settings();

			$this->enabled = $this->get_option('enabled');
			$this->title = $this->get_option('title');
			$this->description = $this->get_option('description');

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
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
					'default' => __( 'Apple Pay (Better Payment)', 'bp-plugin-woocommerce-api2' ),
				]
			];
		}
	}
}
