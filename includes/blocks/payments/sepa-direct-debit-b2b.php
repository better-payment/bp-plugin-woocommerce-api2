<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class BetterPayment_Sepa_Direct_Debit_B2B_Block extends AbstractPaymentMethodType {
	protected $name = 'betterpayment_dd_b2b';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_' . $this->name . '_settings', [] );
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			$this->name . '-blocks-integration',
			plugin_dir_url(__DIR__) . 'assets/js/sepa-direct-debit-b2b.js',
			[],
			null,
			true
		);

		return [ $this->name . '-blocks-integration' ];
	}

	public function get_payment_method_data() {
		$account_holder = wp_get_current_user()->first_name . ' ' . wp_get_current_user()->last_name;
		$creditor_id = get_option('woocommerce_betterpayment_dd_b2b_settings')['creditor_id'];
		$company_name = get_option('woocommerce_betterpayment_dd_b2b_settings')['company_name'];
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
		$html .= sprintf(__('By signing this mandate form, you authorise (A) %s to send instructions to your bank to debit your account and (B) your bank to debit your account in accordance with the instructions from %s. This mandate is only intended for business-to-business transactions. You are not entitled to a refund from your bank after your account has been debited, but you are entitled to request your bank not to debit your account up until the day on which the payment is due.', 'bp-plugin-woocommerce-api2'), $company_name, $company_name);

		return [
			'title' => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),

			// hidden fields to be passed to API
			'accountHolder' => $account_holder,
			'mandateReference' => $mandate_reference,

			// html text to be shown as detail
			'mandateDescription' => wpautop( wptexturize( $html ) ),

			// risk check flags
			'isRiskCheckAgreementRequired' => $this->get_setting('risk_check_agreement') == 'yes',
		];
	}
}
