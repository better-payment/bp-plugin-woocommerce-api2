<?php

class BetterPayment_Invoice_B2B extends Abstract_BetterPayment_Gateway {
	protected string $shortcode = 'kar_b2b';
	protected bool $is_b2b = true;

	/**
	 * @return bool
	 */
	public function has_fields(): bool {
		return $this->is_risk_check_agreement_required();
	}

	public function __construct() {
		$this->id                 = 'betterpayment_kar_b2b';
		$this->method_title       = __( 'Invoice B2B (Better Payment)', 'bp-plugin-woocommerce-api2' );
		$this->method_description = __( 'Invoice B2B payment method of Better Payment', 'bp-plugin-woocommerce-api2' );

		parent::__construct();

		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
	}


	public function is_available() {
		$is_available = parent::is_available();

		$company = WC()->customer?->get_billing_company();

		return $is_available && $company;
	}

	public function init_form_fields() {
		$this->form_fields = [
			'enabled'                     => [
				'title'   => __( 'Enabled', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'checkbox',
				'default' => false
			],
			'title'                       => [
				'title'   => __( 'Title', 'bp-plugin-woocommerce-api2' ),
				'type'    => 'text',
				'default' => __( 'Invoice B2B (Better Payment)', 'bp-plugin-woocommerce-api2' ),
			],
			'risk_check_agreement'        => [
				'title'       => __( 'Require customers to agree to risk check processing', 'bp-plugin-woocommerce-api2' ),
				'type'        => 'checkbox',
				'default'     => false,
				'description' => __( 'If you turn this flag on, we will require the customer to agree to the risk check processing in the checkout page. Without agreement, payments will not go through. You can turn this field off, in case you provide it as part of your terms and conditions.', 'bp-plugin-woocommerce-api2' )
			],
			'display_payment_instruction' => [
				'title'       => __( 'Display payment instruction to the customer', 'bp-plugin-woocommerce-api2' ),
				'type'        => 'checkbox',
				'default'     => false,
				'description' => __( 'When activated, we will be instructing the customer that they should send ORDER_ID as a reference with amount due to the given bank account below.', 'bp-plugin-woocommerce-api2' )
			],
			'iban'                        => [
				'title'       => __( 'IBAN (optional)', 'bp-plugin-woocommerce-api2' ),
				'type'        => 'text',
				'description' => __( 'IBAN of your company', 'bp-plugin-woocommerce-api2' ),
			],
			'bic'                         => [
				'title'       => __( 'BIC (optional)', 'bp-plugin-woocommerce-api2' ),
				'type'        => 'text',
				'description' => __( 'BIC of your company', 'bp-plugin-woocommerce-api2' ),
			]
		];
	}

	private function is_risk_check_agreement_required(): bool {
		return get_option( 'woocommerce_betterpayment_kar_b2b_settings' )['risk_check_agreement'] == 'yes';
	}

	private function is_payment_instruction_displayed(): bool {
		return get_option( 'woocommerce_betterpayment_kar_b2b_settings' )['display_payment_instruction'] == 'yes';
	}

	public function thankyou_page( $order_id ) {
		if ( $this->is_payment_instruction_displayed() ) {
			$title           = __( 'Invoice payment instructions', 'bp-plugin-woocommerce-api2' );
			$iban_label      = __( 'IBAN: ', 'bp-plugin-woocommerce-api2' );
			$bic_label       = __( 'BIC: ', 'bp-plugin-woocommerce-api2' );
			$reference_label = __( 'Reference: ', 'bp-plugin-woocommerce-api2' );
			$description     = __( 'Please, transfer the full invoice amount to the bank account displayed in this page. Include reference mentioned above, in your transfer. Your order will stay pending until the payment has been cleared.', 'bp-plugin-woocommerce-api2' );

			$html = "<h3>$title</h3>";
			$html .= "<b>$iban_label</b>" . get_option( 'woocommerce_betterpayment_kar_b2b_settings' )['iban'];
			$html .= "<br>";
			$html .= "<b>$bic_label</b>" . get_option( 'woocommerce_betterpayment_kar_b2b_settings' )['bic'];
			$html .= "<br>";
			$html .= "<b>$reference_label</b>$order_id";
			$html .= "<p>$description</p>";

			echo wpautop( wptexturize( $html ) );
		}
	}

	public function payment_fields() {
		if ( $this->is_risk_check_agreement_required() ) {
			woocommerce_form_field( $this->id . '_risk_check_agreement', [
				'type'     => 'checkbox',
				'label'    => __( 'Agree to risk check processing', 'bp-plugin-woocommerce-api2' ),
				'required' => true,
			] );
		}
	}

	public function validate_fields() {
		if ( $this->is_risk_check_agreement_required() && empty( $_POST[ $this->id . '_risk_check_agreement' ] ) ) {
			wc_add_notice( __( 'Risk check agreement is required', 'bp-plugin-woocommerce-api2' ), 'error' );
		}
	}
}
