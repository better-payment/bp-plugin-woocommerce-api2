<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class BetterPayment_Invoice_B2B_Block extends AbstractPaymentMethodType {
	protected $name = 'betterpayment_kar_b2b';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_' . $this->name . '_settings', [] );
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			$this->name . '-blocks-integration',
			plugin_dir_url(__DIR__) . 'assets/js/invoice-b2b.js',
			[],
			null,
			true
		);

		wp_localize_script($this->name . '-blocks-integration', $this->name . '_l10n', [
			'label_risk_check_agreement' => __('Agree to risk check processing', 'bp-plugin-woocommerce-api2')
		]);

		return [ $this->name . '-blocks-integration' ];
	}

	public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'isRiskCheckAgreementRequired' => $this->get_setting('risk_check_agreement') == 'yes',
		];
	}
}
