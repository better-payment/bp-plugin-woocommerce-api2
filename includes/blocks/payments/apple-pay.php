<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\StoreApi\Payments\PaymentContext;
use Automattic\WooCommerce\StoreApi\Payments\PaymentResult;

final class BetterPayment_ApplePay_Block extends AbstractPaymentMethodType {
	protected $name = 'betterpayment_applepay';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_' . $this->name . '_settings', [] );

		add_action(
			'woocommerce_rest_checkout_process_payment_with_context',
			function( PaymentContext $context, PaymentResult $result ) {
				if ( $context->payment_method === $this->name ) {
					// If the logic above was successful, we can set the status to success.
					$order = $context->order;
					// TODO: review
					// Set transaction ID to random (!) fails for some reason
					// ERROR: Transaction not found.
					$order->set_transaction_id($context->payment_data['transaction_id']);
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
		return 'yes' == $this->get_setting('enabled');
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			$this->name . '-blocks-integration',
			 plugin_dir_url(__DIR__) . 'assets/js/apple-pay.js',
			[
				'wc-blocks-checkout'
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
			'initial_data' => [
				'order_id' => WC()->session->get( 'order_awaiting_payment' ),
				'customer_id' => WC()->customer->get_id(),
				'customer_ip' => WC_Geolocation::get_ip_address(),
				'shop_name' => get_bloginfo('name'),
				'postback_url' => Config_Reader::get_postback_url(),
				'app_name' => Config_Reader::get_app_name(),
				'app_version' => Config_Reader::get_app_version(),
			],
		];
	}
}
