<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class BetterPayment_Credit_Card_Block extends AbstractPaymentMethodType {
	protected $name = 'betterpayment_cc';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_' . $this->name . '_settings', [] );
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			$this->name . '-blocks-integration',
			plugin_dir_url(__DIR__) . 'assets/js/credit-card.js',
			[],
			null,
			true
		);

		return [ $this->name . '-blocks-integration' ];
	}

	public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
		];
	}
}
