<?php

class BetterPayment_Paydirekt extends Abstract_BetterPayment_Gateway {
	protected string $shortcode = 'paydirekt';
	protected bool $is_async = true;

	public function __construct() {
		$this->id                 = 'betterpayment_paydirekt';
		$this->method_title       = __( 'Paydirekt (Better Payment)', 'bp-plugin-woocommerce-api2' );
		$this->method_description = __( 'Paydirekt payment method of Better Payment', 'bp-plugin-woocommerce-api2' );

		parent::__construct();
	}

	public function init_form_fields() {
		$this->form_fields = [
			'enabled' => [
				'title'   => __( 'Enabled', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'checkbox',
				'default' => false
			],
			'title'   => [
				'title'   => __( 'Title', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'text',
				'default' => __( 'Paydirekt (Better Payment)', 'bp-plugin-woocommerce-api2' ),
			]
		];
	}
}
