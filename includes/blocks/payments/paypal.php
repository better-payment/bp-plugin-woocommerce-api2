<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class BetterPayment_PayPal_Block extends AbstractPaymentMethodType {
	protected $name = 'betterpayment_paypal';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_betterpayment_paypal_settings', [] );
	}

	public function is_active() {
		return true;
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			'betterpayment_paypal-blocks-integration',
			 plugin_dir_url(__DIR__) . 'assets/js/paypal.js',
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

		return [ 'betterpayment_paypal-blocks-integration' ];
	}

	public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
		];
	}
}
