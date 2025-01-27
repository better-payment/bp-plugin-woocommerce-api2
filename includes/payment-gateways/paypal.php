<?php

class BetterPayment_PayPal extends Abstract_BetterPayment_Gateway {
	protected string $shortcode = 'paypal';
	protected bool $is_async = true;

	public function __construct() {
		$this->id                 = 'betterpayment_paypal';
		$this->method_title       = __( 'PayPal (Better Payment)', 'bp-plugin-woocommerce-api2' );
		$this->method_description = __( 'PayPal payment method of Better Payment', 'bp-plugin-woocommerce-api2' );

		parent::__construct();
	}

	public function init_form_fields(): void {
		$this->form_fields = [
			'enabled' => [
				'title'   => __( 'Enabled', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'checkbox',
				'default' => false
			],
			'title'   => [
				'title'   => __( 'Title', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'text',
				'default' => __( 'PayPal (Better Payment)', 'bp-plugin-woocommerce-api2' ),
			]
		];
	}
}
