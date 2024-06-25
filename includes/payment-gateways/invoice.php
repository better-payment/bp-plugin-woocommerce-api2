<?php
include_once 'abstract-betterpayment-gateway.php';

if (class_exists( 'Abstract_BetterPayment_Gateway' )) {
	class BetterPayment_Invoice extends Abstract_BetterPayment_Gateway {
		protected string $shortcode = 'kar';

		public $id = 'betterpayment_kar';
		public $method_title = 'Invoice (Better Payment)';
		public $method_description = 'Invoice payment method of Better Payment Gateway';

		/**
		 * @return bool
		 */
		public function has_fields(): bool {
			return $this->is_date_of_birth_collected() || $this->is_gender_collected() || $this->is_risk_check_agreement_required();
		}

		public function __construct() {
			parent::__construct();

			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
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
					'default' => 'Invoice (Better Payment)',
				],
				'$this->>this->id . c_ollect_date_of_birth' => [
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
				],
				'display_payment_instruction' => [
					'title' => 'Display payment instruction to the customer',
					'type' => 'checkbox',
					'default' => false,
					'description' => 'When activated, we will be instructing the customer that they should send ORDER_ID as a reference with amount due to the given bank account below.'
				],
				'iban' => [
					'title' => 'IBAN (optional)',
					'type' => 'text',
					'description' => 'IBAN of your company',
				],
				'bic' => [
					'title' => 'BIC (optional)',
					'type' => 'text',
					'description' => 'BIC of your company',
				]
			];
		}

		private function is_date_of_birth_collected(): bool {
			return get_option('woocommerce_betterpayment_kar_settings')['collect_date_of_birth'] == 'yes';
		}

		private function is_gender_collected(): bool {
			return get_option('woocommerce_betterpayment_kar_settings')['collect_gender'] == 'yes';
		}

		private function is_risk_check_agreement_required(): bool {
			return get_option('woocommerce_betterpayment_kar_settings')['risk_check_agreement'] == 'yes';
		}

		private function is_payment_instruction_displayed(): bool {
			return get_option('woocommerce_betterpayment_kar_settings')['display_payment_instruction'] == 'yes';
		}

		public function thankyou_page( $order_id ) {
			if ( $this->is_payment_instruction_displayed() ) {
				$html = '<h3>Invoice payment instructions</h3>';
				$html .= '<b>IBAN: </b>' . get_option('woocommerce_betterpayment_kar_settings')['iban'];
				$html .= '<br>';
				$html .= '<b>BIC: </b>' . get_option('woocommerce_betterpayment_kar_settings')['bic'];
				$html .= '<br>';
				$html .= '<b>Reference: </b>' . $order_id;
				$html .= '<p>Please, transfer the full invoice amount to the bank account displayed in this page. Include reference mentioned above, in your transfer. Your order will stay pending until the payment has been cleared.</p>';

				echo wpautop( wptexturize( $html ) );
			}
		}

		public function payment_fields() {
			// Risk check information
			if ($this->is_date_of_birth_collected() || $this->is_gender_collected() || $this->is_risk_check_agreement_required()) {
				$html = 'Risk check information';
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
