<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class BetterPayment_Invoice_Block extends AbstractPaymentMethodType {
	protected $name = 'betterpayment_kar';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_' . $this->name . '_settings', [] );
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			$this->name . '-blocks-integration',
			plugin_dir_url(__DIR__) . 'assets/js/invoice.js',
			[],
			null,
			true
		);

		wp_localize_script($this->name . '-blocks-integration', $this->name . '_i10n', [
			'label_risk_check_information' => __('Risk check information', 'bp-plugin-woocommerce-api2'),
			'label_gender' => __('Gender', 'bp-plugin-woocommerce-api2'),
			'option_select' => __('Select...', 'bp-plugin-woocommerce-api2'),
			'option_male' => __('Male', 'bp-plugin-woocommerce-api2'),
			'option_female' => __('Female', 'bp-plugin-woocommerce-api2'),
			'option_diverse' => __('Diverse', 'bp-plugin-woocommerce-api2'),
			'label_date_of_birth' => __('Date of birth', 'bp-plugin-woocommerce-api2'),
			'label_risk_check_agreement' => __('Agree to risk check processing', 'bp-plugin-woocommerce-api2'),
		]);

		return [ $this->name . '-blocks-integration' ];
	}

	public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'isGenderCollected' => $this->get_setting('collect_gender') == 'yes',
			'isDateOfBirthCollected' => $this->get_setting('collect_date_of_birth') == 'yes',
			'isRiskCheckAgreementRequired' => $this->get_setting('risk_check_agreement') == 'yes',
		];
	}
}
