<?php

class BetterPayment_Google_Pay extends Abstract_BetterPayment_Gateway {
	protected string $shortcode = 'googlepay';

	public function __construct() {
		$this->id = 'betterpayment_googlepay';
		$this->method_title = __( 'Google Pay (Better Payment)', 'bp-plugin-woocommerce-api2' );
		$this->method_description = __( 'Google Pay payment method of Better Payment', 'bp-plugin-woocommerce-api2' );

		parent::__construct();
	}

	// Google Pay is not available in shortcode (legacy) checkout
	public function is_available(): bool {
		if (is_checkout() && !WC_Blocks_Utils::has_block_in_page(get_the_ID(), 'woocommerce/checkout'))
			return false;

		return true;
	}

	public function init_form_fields() {
		$this->form_fields = [
			'enabled' => [
				'title' => __( 'Enabled', 'bp-plugin-woocommerce-api2' ),
				'type'  => 'checkbox',
			],
			'title' => [
				'title'   => __( 'Title', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'text',
				'default' => __( 'Google Pay (Better Payment)', 'bp-plugin-woocommerce-api2' ),
			],
			'allowed_card_networks' => [
				'title'   => __( 'Allowed card networks', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'multiselect',
				'options' => [
					'VISA'       => 'VISA',
					'MASTERCARD' => 'MASTERCARD',
				],
				'default' => [
					'VISA',
					'MASTERCARD'
				]
			],
			'allowed_auth_methods' => [
				'title'   => __( 'Allowed auth methods', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'multiselect',
				'options' => [
					'PAN_ONLY'       => 'PAN_ONLY',
					'CRYPTOGRAM_3DS' => 'CRYPTOGRAM_3DS',
				],
				'default' => [
					'PAN_ONLY',
					'CRYPTOGRAM_3DS'
				],
				'description' => __( 'For more info please refer', 'bp-plugin-woocommerce-api2' ) .' <a target="_blank" href="https://developers.google.com/pay/api/web/reference/request-objects#CardParameters">Google Pay API documentation</a>'
			],
			'gateway' => [
				'title'   => __( 'Gateway', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'text',
			],
			'gateway_merchant_id' => [
				'title'   => __( 'Gateway merchant ID', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'text',
				'description' => __( 'More about', 'bp-plugin-woocommerce-api2' ) .' <a target="_blank" href="https://developers.google.com/pay/api/web/reference/request-objects#PaymentMethodTokenizationSpecifications">Tokenization Specification</a>'
			],
			'merchant_id' => [
				'title'   => __( 'Merchant ID', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'text',
			],
			'merchant_name' => [
				'title'   => __( 'Merchant name', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'text',
				'description' => __( 'More about', 'bp-plugin-woocommerce-api2' ) .' <a target="_blank" href="https://developers.google.com/pay/api/web/reference/request-objects#MerchantInfo">Merchant Info</a>'
			],
		];
	}
}
