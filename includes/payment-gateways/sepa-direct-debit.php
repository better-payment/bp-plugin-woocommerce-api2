<?php
include_once 'abstract-betterpayment-gateway.php';

if (class_exists( 'Abstract_BetterPayment_Gateway' )) {
	class BetterPayment_Sepa_Direct_Debit extends Abstract_BetterPayment_Gateway {
		protected string $shortcode = 'dd';

		public $id = 'betterpayment_dd';
		public $method_title = 'Sepa Direct Debit (Better Payment)';
		public $method_description = 'Sepa Direct Debit payment method of Better Payment Gateway';
		public $has_fields = true;

		public function init_form_fields() {
			$this->form_fields = [
				'enabled' => [
					'title' => 'Enabled',
					'type' => 'checkbox',
					'default' => false
				],
				'title' => [
					'title' => 'Title',
					'type' => 'text',
					'default' => 'Sepa Direct Debit (Better Payment)',
				],
				'creditor_id' => [
					'title' => 'Creditor ID',
					'type' => 'text',
					'description' => 'You need to provide a valid Creditor ID, to be shown in mandate agreement on the checkout page.',
				],
				'company_name' => [
					'title' => 'Company name',
					'type' => 'text',
					'description' => 'You need to provide Company Name, to be shown in mandate reference agreement on the checkout page.',
				],
				'collect_date_of_birth' => [
					'title' => 'Collect date of birth',
					'type' => 'checkbox',
					'default' => false,
					'description' => 'If you have configured risk checks with the payment provider, it may require date of birth from your customers.',
				],
				'collect_gender' => [
					'title' => 'Collect gender information',
					'type' => 'checkbox',
					'default' => false,
					'description' => 'If you have configured risk checks with the payment provider, it may require gender from your customers.'
				],
				'risk_check_agreement' => [
					'title' => 'Require customers to agree to risk check processing',
					'type' => 'checkbox',
					'default' => false,
					'description' => 'If you turn this flag on, we will require the customer to agree to the risk check processing in the checkout page. 
									Without agreement, payments will not go through. You can turn this field off, in case you provide it as part of your terms and conditions.',
				]
			];
		}

		private function is_date_of_birth_collected(): bool {
			return get_option('woocommerce_betterpayment_dd_settings')['collect_date_of_birth'] == 'yes';
		}

		private function is_gender_collected(): bool {
			return get_option('woocommerce_betterpayment_dd_settings')['collect_gender'] == 'yes';
		}

		private function is_risk_check_agreement_required(): bool {
			return get_option('woocommerce_betterpayment_dd_settings')['risk_check_agreement'] == 'yes';
		}

		public function payment_fields() {
			woocommerce_form_field($this->id . '_iban', [
				'type' => 'text',
				'required' => true,
				'label' => __('IBAN:'),
			]);

			woocommerce_form_field($this->id . '_bic', [
				'type' => 'text',
				'label' => __('BIC:'),
			]);

			$account_holder = wp_get_current_user()->first_name . ' ' . wp_get_current_user()->last_name;
			$creditor_id = get_option('woocommerce_betterpayment_dd_settings')['creditor_id'];
			$company_name = get_option('woocommerce_betterpayment_dd_settings')['company_name'];
			$mandate_reference = wp_generate_uuid4();

			$html = '<b>Account holder: </b>' . $account_holder;
			$html .= '<br>';
			$html .= '<b>Creditor ID: </b>' . $creditor_id;
			$html .= '<br>';
			$html .= '<b>Company name: </b>' . $company_name;
			$html .= '<br>';
			$html .= '<b>Mandate reference: </b>' . $mandate_reference;
			$html .= '<br>';
			$html .= '<br>';
			$html .= 'By signing this mandate form, you authorise (A) ' . $company_name . ' to send instructions to 
					your bank to debit your account and (B) your bank to debit your account in accordance with the 
					instructions from ' . $company_name . '. As part of your rights, you are entitled to a refund from 
					your bank under the terms and conditions of your agreement with your bank. A refund must be claimed 
					within eight weeks starting from the date on which your account was debited.';

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
				'label' => __('I agree to the following mandate'),
				'required' => true,
			]);

			// Risk check information
			if ($this->is_date_of_birth_collected() || $this->is_gender_collected() || $this->is_risk_check_agreement_required()) {
				$html = '<hr>';
				$html .= 'Risk check information';
				echo wpautop( wptexturize( $html ) );
			}

			if ($this->is_date_of_birth_collected()) {
				woocommerce_form_field($this->id . '_date_of_birth', [
					'type' => 'date',
					'required' => true,
					'label' => __('Date of birth'),
				]);
			}

			if ($this->is_gender_collected()) {
				woocommerce_form_field($this->id . '_gender', [
					'type' => 'select',
					'options' => [
						'' => __('Select...'),
						'm' => 'Male',
						'f' => 'Female',
						'd' => 'Diverse'
					],
					'required' => true,
					'label' => __('Gender'),
				]);
			}

			if ($this->is_risk_check_agreement_required()) {
				woocommerce_form_field($this->id . '_risk_check_agreement', [
					'type' => 'checkbox',
					'label' => __('Agree to risk check processing'),
					'required' => true,
				]);
			}
		}

		public function validate_fields() {
			if( empty($_POST[$this->id . '_iban']) ) {
				wc_add_notice( 'IBAN is required', 'error' );
			}

			if ( empty($_POST[$this->id . '_mandate_agreement']) ) {
				wc_add_notice( 'Mandate agreement is required', 'error' );
			}

			if ( $this->is_date_of_birth_collected() && empty($_POST[$this->id . '_date_of_birth']) ) {
				wc_add_notice( 'Date of birth is required', 'error' );
			}

			if ( $this->is_gender_collected() && empty($_POST[$this->id . '_gender']) ) {
				wc_add_notice( 'Gender is required', 'error' );
			}

			if ( $this->is_risk_check_agreement_required() && empty($_POST[$this->id . '_risk_check_agreement']) ) {
				wc_add_notice( 'Risk check agreement is required', 'error' );
			}
		}
	}
}
