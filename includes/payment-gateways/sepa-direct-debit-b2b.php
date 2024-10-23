<?php
include_once 'abstract-betterpayment-gateway.php';

if (class_exists( 'Abstract_BetterPayment_Gateway' )) {
	class BetterPayment_Sepa_Direct_Debit_B2B extends Abstract_BetterPayment_Gateway {
		protected string $shortcode = 'dd_b2b';
		protected bool $is_b2b = true;

		public function __construct() {
			$this->id = 'betterpayment_dd_b2b';
			$this->method_title = __( 'Sepa Direct Debit B2B (Better Payment)', 'bp-plugin-woocommerce-api2' );
			$this->method_description = __( 'Sepa Direct Debit B2B payment method of Better Payment', 'bp-plugin-woocommerce-api2' );
			$this->has_fields = true;

			parent::__construct();
		}

		public function is_available() {
			$is_available = parent::is_available();

			$company = WC()->customer?->get_billing_company();

			return $is_available && $company;
		}

		public function init_form_fields() {
			$this->form_fields = [
				'enabled' => [
					'title' => __('Enabled', 'bp-plugin-woocommerce-api2'),
					'type' => 'checkbox',
					'default' => false
				],
				'title' => [
					'title' => __('Title', 'bp-plugin-woocommerce-api2'),
					'type' => 'text',
					'default' => __('Sepa Direct Debit B2B (Better Payment)', 'bp-plugin-woocommerce-api2'),
				],
				'creditor_id' => [
					'title' => __('Creditor ID', 'bp-plugin-woocommerce-api2'),
					'type' => 'text',
					'description' => __('You need to provide a valid Creditor ID, to be shown in mandate agreement on the checkout page.', 'bp-plugin-woocommerce-api2'),
				],
				'company_name' => [
					'title' => __('Company name', 'bp-plugin-woocommerce-api2'),
					'type' => 'text',
					'description' => __('You need to provide Company Name, to be shown in mandate reference agreement on the checkout page.', 'bp-plugin-woocommerce-api2'),
				],
				'risk_check_agreement' => [
					'title' => __('Require customers to agree to risk check processing', 'bp-plugin-woocommerce-api2'),
					'type' => 'checkbox',
					'default' => false,
					'description' => __('If you turn this flag on, we will require the customer to agree to the risk check processing in the checkout page. Without agreement, payments will not go through. You can turn this field off, in case you provide it as part of your terms and conditions.', 'bp-plugin-woocommerce-api2'),
				]
			];
		}

		private function is_risk_check_agreement_required(): bool {
			return get_option('woocommerce_betterpayment_dd_b2b_settings')['risk_check_agreement'] == 'yes';
		}

		public function payment_fields() {
			woocommerce_form_field($this->id . '_iban', [
				'type' => 'text',
				'required' => true,
				'label' => __('IBAN', 'bp-plugin-woocommerce-api2'),
			]);

			woocommerce_form_field($this->id . '_bic', [
				'type' => 'text',
				'label' => __('BIC', 'bp-plugin-woocommerce-api2'),
			]);

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

			echo wpautop( wptexturize( $html ) );

			woocommerce_form_field($this->id . '_account_holder', [
				'type' => 'hidden',
				'default' => $account_holder,
			]);

			woocommerce_form_field($this->id . '_mandate_reference', [
				'type' => 'hidden',
				'default' => $mandate_reference,
			]);

			woocommerce_form_field($this->id . '_mandate_agreement', [
				'type' => 'checkbox',
				'label' => __('I agree to the following mandate', 'bp-plugin-woocommerce-api2'),
				'required' => true,
			]);

			if ($this->is_risk_check_agreement_required()) {
				woocommerce_form_field($this->id . '_risk_check_agreement', [
					'type' => 'checkbox',
					'label' => __('Agree to risk check processing', 'bp-plugin-woocommerce-api2'),
					'required' => true,
				]);
			}
		}

		public function validate_fields() {
			if( empty($_POST[$this->id . '_iban']) ) {
				wc_add_notice( __('IBAN is required', 'bp-plugin-woocommerce-api2'), 'error' );
			}

			if ( empty($_POST[$this->id . '_mandate_agreement']) ) {
				wc_add_notice( __('Mandate agreement is required', 'bp-plugin-woocommerce-api2'), 'error' );
			}

			if ( $this->is_risk_check_agreement_required() && empty($_POST[$this->id . '_risk_check_agreement']) ) {
				wc_add_notice( __('Risk check agreement is required', 'bp-plugin-woocommerce-api2'), 'error' );
			}
		}
	}
}
