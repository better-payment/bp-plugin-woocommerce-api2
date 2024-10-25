<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class BetterPayment_Sepa_Direct_Debit_Block extends AbstractPaymentMethodType {
	protected $name = 'betterpayment_dd';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_' . $this->name . '_settings', [] );
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			$this->name . '-blocks-integration',
			plugin_dir_url(__DIR__) . 'assets/js/sepa-direct-debit.js',
			[],
			null,
			true
		);

		wp_localize_script($this->name . '-blocks-integration', $this->name . '_i10n', [
			'label_iban' => __('IBAN', 'bp-plugin-woocommerce-api2'),
			'label_bic' => __('BIC', 'bp-plugin-woocommerce-api2'),
			'label_mandate_agreement' => __('I agree to the following mandate', 'bp-plugin-woocommerce-api2'),
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
		$account_holder = wp_get_current_user()->first_name . ' ' . wp_get_current_user()->last_name;
		$creditor_id = get_option('woocommerce_betterpayment_dd_settings')['creditor_id'];
		$company_name = get_option('woocommerce_betterpayment_dd_settings')['company_name'];
		$mandate_reference = wp_generate_uuid4();

		$label_account_holder = __('Account holder: ', 'bp-plugin-woocommerce-api2');
		$label_creditor_id = __('Creditor ID: ', 'bp-plugin-woocommerce-api2');
		$label_company_name = __('Company name: ', 'bp-plugin-woocommerce-api2');
		$label_mandate_reference = __('Mandate reference: ', 'bp-plugin-woocommerce-api2');

		$html = "<b>$label_account_holder</b>$account_holder";
		$html .= "<br>";
		$html .= "<b>$label_creditor_id</b>$creditor_id";
		$html .= "<br>";
		$html .= "<b>$label_company_name</b>$company_name";
		$html .= "<br>";
		$html .= "<b>$label_mandate_reference</b>$mandate_reference";
		$html .= "<br>";
		$html .= "<br>";
		$html .= sprintf(__('By signing this mandate form, you authorise (A) %s to send instructions to your bank to debit your account and (B) your bank to debit your account in accordance with the instructions from %s. As part of your rights, you are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within eight weeks starting from the date on which your account was debited.', 'bp-plugin-woocommerce-api2'), $company_name, $company_name);

		return [
			'title' => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),

			// hidden fields to be passed to API
			'accountHolder' => $account_holder,
			'mandateReference' => $mandate_reference,

			// html text to be shown as detail
			'mandateDescription' => wpautop( wptexturize( $html ) ),

			// risk check flags
			'isGenderCollected' => $this->get_setting('collect_gender') == 'yes',
			'isDateOfBirthCollected' => $this->get_setting('collect_date_of_birth') == 'yes',
			'isRiskCheckAgreementRequired' => $this->get_setting('risk_check_agreement') == 'yes',
		];
	}
}
