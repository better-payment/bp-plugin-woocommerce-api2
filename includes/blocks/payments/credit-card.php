<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class BetterPayment_Credit_Card_Block extends AbstractPaymentMethodType {
	protected $name = 'betterpayment_cc';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_betterpayment_cc_settings', [] );
	}

	public function is_active() {
		return true;
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			'betterpayment_cc-blocks-integration',
			 plugin_dir_url(__DIR__) . 'assets/js/index.js',
			[
				'wc-blocks-registry',
				'wc-settings',
				'wp-element',
				'wp-html-entities',
				'wp-i18n',
			],
			null,
			true
		);

//		if( function_exists( 'wp_set_script_translations' ) ) {
//			wp_set_script_translations( 'betterpayment_cc-blocks-integration');
//		}

		return [ 'betterpayment_cc-blocks-integration' ];
	}

//	public function get_supported_features() {
//		$gateway = new BetterPayment_Credit_Card();
//		return array_filter( $gateway->supports, [ $gateway, 'supports' ] );
//	}

//	private function get_enable_for_methods() {
//		$enable_for_methods = $this->get_setting( 'enable_for_methods', [] );
//		if ( '' === $enable_for_methods ) {
//			return [];
//		}
//		return $enable_for_methods;
//	}

	public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
//			'supports'    => $this->get_supported_features(),
//			'enableForShippingMethods' => $this->get_enable_for_methods(),
		];
	}
}
