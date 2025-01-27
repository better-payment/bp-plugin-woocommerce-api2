<?php

class BetterPayment_Apple_Pay extends Abstract_BetterPayment_Gateway {
	protected string $shortcode = 'applepay';

	public function __construct() {
		$this->id = 'betterpayment_applepay';
		$this->method_title = __( 'Apple Pay (Better Payment)', 'bp-plugin-woocommerce-api2' );
		$this->method_description = __( 'Apple Pay payment method of Better Payment', 'bp-plugin-woocommerce-api2' );

		parent::__construct();
	}

	// Apple Pay is not available in shortcode (legacy) checkout
	public function is_available(): bool {
		if (is_checkout() && !WC_Blocks_Utils::has_block_in_page(get_the_ID(), 'woocommerce/checkout'))
			return false;

		return true;
	}

	public function init_form_fields() {
		$this->form_fields = [
			'enabled'            => [
				'title' => __( 'Enabled', 'bp-plugin-woocommerce-api2' ),
				'type'  => 'checkbox',
			],
			'title'              => [
				'title'   => __( 'Title', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'text',
				'default' => __( 'Apple Pay (Better Payment)', 'bp-plugin-woocommerce-api2' ),
			],
			'supports3DS'        => [
				'title'   => __( '3DS Enabled', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'checkbox',
				'default' => 'yes'
			],
			'supported_networks' => [
				'title'   => __( 'Supported Networks', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'multiselect',
				'options' => [
					'visa'       => 'VISA',
					'masterCard' => 'MASTERCARD',
				],
				'default' => [
					'visa',
					'masterCard'
				]
			]
		];
	}
}
