<?php
include_once 'abstract-betterpayment-gateway.php';

if (class_exists('WC_BetterPayment_Gateway')) {
	class WC_BetterPayment_Sepa_Direct_Debit extends WC_BetterPayment_Gateway {
		protected string $shortcode = 'dd';

		public function __construct() {
			$this->id = 'betterpayment_dd';
			$this->icon = '';
			$this->has_fields = true;
			$this->method_title = 'Sepa Direct Debit (Better Payment)';
			$this->method_description = 'Sepa Direct Debit payment method of Better Payment Gateway';

			$this->init_form_fields();
			$this->init_settings();

			$this->enabled = $this->get_option('enabled');
			$this->title = $this->get_option('title');

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

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
				'creditorId' => [
					'title' => 'Creditor ID',
					'type' => 'text',
					'description' => 'You need to provide a valid Creditor ID, to be shown in mandate agreement on the checkout page.',
				],
				'companyName' => [
					'title' => 'Company name',
					'type' => 'text',
					'description' => 'You need to provide Company Name, to be shown in mandate reference agreement on the checkout page.',
				],
				'collectDateOfBirth' => [
					'title' => 'Collect date of birth',
					'type' => 'checkbox',
					'default' => false,
					'description' => 'If you have configured risk checks with the payment provider, it may require date of birth from your customers.',
				],
				'collectGender' => [
					'title' => 'Collect gender information',
					'type' => 'checkbox',
					'default' => false,
					'description' => 'If you have configured risk checks with the payment provider, it may require gender from your customers.'
				],
				'riskCheckAgreement' => [
					'title' => 'Require customers to agree to risk check processing',
					'type' => 'checkbox',
					'default' => false,
					'description' => 'If you turn this flag on, we will require the customer to agree to the risk check processing in the checkout page. 
									Without agreement, payments will not go through. You can turn this field off, in case you provide it as part of your terms and conditions.',
				]
			];
		}

		public function payment_fields() {
			woocommerce_form_field('iban', [
				'type' => 'text',
				'required' => true,
				'label' => __('IBAN:'),
			]);

			woocommerce_form_field('bic', [
				'type' => 'text',
				'label' => __('BIC:'),
			]);

			$account_holder = wp_get_current_user()->first_name . ' ' . wp_get_current_user()->last_name;
			$creditor_id = get_option('woocommerce_betterpayment_dd_settings')['creditorId'];
			$company_name = get_option('woocommerce_betterpayment_dd_settings')['companyName'];
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

			woocommerce_form_field('mandateAgreement', [
				'type' => 'checkbox',
				'label' => __('I agree to the following mandate'),
				'required' => true,
			]);

			if (get_option('woocommerce_betterpayment_dd_settings')['collectDateOfBirth'] == 'yes') {
				woocommerce_form_field('dateOfBirth', [
					'type' => 'date',
					'required' => true,
					'label' => __('Date of birth'),
				]);
			}

			if (get_option('woocommerce_betterpayment_dd_settings')['collectGender'] == 'yes') {
				woocommerce_form_field('gender', [
					'type' => 'select',
					'options' => [
						'm' => 'Male',
						'f' => 'Female',
						'd' => 'Diverse'
					],
					'required' => true,
					'label' => __('Gender'),
				]);
			}

			if (get_option('woocommerce_betterpayment_dd_settings')['riskCheckAgreement'] == 'yes') {
				woocommerce_form_field('riskCheckAgreement', [
					'type' => 'checkbox',
					'label' => __('Agree to risk check processing'),
				]);
			}
		}

		public function process_payment( $order_id ) {
			$order = wc_get_order($order_id);
			$parameters = [];
			$parameters += $this->get_common_parameters($order_id);
			$parameters += $this->get_billing_address_parameters($order_id);
			$parameters += $this->get_shipping_address_parameters($order_id);
			$parameters += $this->get_redirect_url_parameters();

//			$order->update_status('pending-payment', 'Awaiting Credit Card payment');

			return [
				'result' => 'success',
				'redirect' => $this->get_return_url($order)
			];
		}
	}
}
