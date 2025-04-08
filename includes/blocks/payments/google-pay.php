<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\StoreApi\Payments\PaymentContext;
use Automattic\WooCommerce\StoreApi\Payments\PaymentResult;

final class BetterPayment_GooglePay_Block extends AbstractPaymentMethodType {
	protected $name = 'betterpayment_googlepay';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_' . $this->name . '_settings', [] );

		// Bypass postcode and city validations.
		add_filter('woocommerce_validate_postcode', '__return_true');
		add_filter('woocommerce_default_address_fields', function ($fields) {
			$fields['postcode']['required'] = false;
			$fields['city']['required'] = false;

			return $fields;
		});

		add_action(
			'woocommerce_rest_checkout_process_payment_with_context',
			function( PaymentContext $context, PaymentResult $result ) {
				if ( $context->payment_method === $this->name ) {
					// If the logic above was successful, we can set the status to success.
					$order = $context->order;
					$order->set_transaction_id($context->payment_data['transaction_id']);
					// $order->update_meta_data('apple_pay_order_id', $context->payment_data['apple_pay_order_id']);
					$order->save();

					$transaction_status = $context->payment_data['transaction_status'];

					// Map status from Better Payment to WooCommerce
					if ( $transaction_status == 'completed' ) {
						$order->payment_complete();
					} else {
						$status = match ( $transaction_status ) {
							'started', 'pending' => 'on-hold',
							'error', 'declined', 'canceled' => 'failed',
							'refunded', 'chargeback' => 'refunded',
							default => 'pending-payment',
						};

						$order->update_status( $status, 'Status updated from Payment Gateway.' );
					}

					$result->set_status( 'success' );
					$result->set_redirect_url($order->get_checkout_order_received_url());
				}
			},
			10,
			2
		);
	}

	public function is_active() {
		return filter_var( $this->get_setting( 'enabled', false ), FILTER_VALIDATE_BOOLEAN );
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			$this->name . '-blocks-integration',
			plugin_dir_url(__DIR__) . 'assets/js/google-pay.js',
			[],
			null,
			true
		);

		return [ $this->name . '-blocks-integration' ];
	}


	public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'allowedAuthMethods' => $this->get_setting( 'allowed_auth_methods' ),
			'allowedCardNetworks' => $this->get_setting( 'allowed_card_networks' ),
			'gateway' => $this->get_setting( 'gateway' ),
			'gatewayMerchantId' => $this->get_setting( 'gateway_merchant_id' ),
			'merchantId' => $this->get_setting( 'merchant_id' ),
			'merchantName' => $this->get_setting( 'merchant_name' ),
			'environment' => Config_Reader::get_app_environment()  == 'test' ? "TEST" : "PRODUCTION",
			'initial_data' => [
				'country' => WC()->countries->get_base_country(),
				'order_id' => wp_generate_uuid4(),
				'customer_id' => WC()->customer?->get_id(),
				'customer_ip' => WC_Geolocation::get_ip_address(),
				'shop_name' => get_bloginfo('name'),
				'postback_url' => Config_Reader::get_postback_url(),
				'app_name' => Config_Reader::get_app_name(),
				'app_version' => Config_Reader::get_app_version(),
			],
			'paymentUrl' =>  get_rest_url(null, '/betterpayment/payment')
		];
	}
}
