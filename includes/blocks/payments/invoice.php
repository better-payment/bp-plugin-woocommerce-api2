<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class BetterPayment_Invoice_Block extends AbstractPaymentMethodType {
	protected $name = 'betterpayment_kar';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_' . $this->name . '_settings', [] );
	}

	public function is_active() {
		return true;
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			$this->name . '-blocks-integration',
			 plugin_dir_url(__DIR__) . 'assets/js/invoice.js',
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