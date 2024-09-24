<?php
/**
 * Plugin Name: Better Payment WooCommerce Extension
 * Plugin URI: https://github.com/better-payment/bp-plugin-woocommerce-api2
 * Description: Better Payment plugin to implement payment methods using API2
 * Version: 1.1.0
 * Author: Better Payment
 * Author URI: https://betterpayment.de
 * Text Domain: bp-plugin-woocommerce-api2
 * Domain Path: /languages
 */

//defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! class_exists( 'WC_BetterPayment_Plugin' ) ) {
	class WC_BetterPayment_Plugin {
		/**
		 * Construct the plugin.
		 */
		public function __construct() {
			// Checks if WooCommerce is installed.
			add_action('admin_notices', function () {
				if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
					$message = __('Better Payment WooCommerce Extension requires Woocommerce plugin to be activated', 'bp-plugin-woocommerce-api2');
					echo "<div class='notice notice-error'><p>$message</p></div>";
				}
			});

			add_action( 'plugins_loaded', array( $this, 'init' ) );
			add_action( 'before_woocommerce_init', array( $this, 'declare_cart_checkout_blocks_compatibility' ) );
			add_action( 'woocommerce_blocks_loaded', array( $this, 'betterpayment_blocks_support' ) );
		}

		/**
		 * Initialize the plugin.
		 */
		public function init() {
			// Include our integration class.
			include_once 'includes/integration.php';
			// Register the integration.
			add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );

			// Include payment methods
			include_once 'includes/payment-gateways/credit-card.php';
			include_once 'includes/payment-gateways/paypal.php';
			include_once 'includes/payment-gateways/paydirekt.php';
			include_once 'includes/payment-gateways/giropay.php';
			include_once 'includes/payment-gateways/sofort.php';
			include_once 'includes/payment-gateways/request-to-pay.php';
			include_once 'includes/payment-gateways/aiia-pay.php';
			include_once 'includes/payment-gateways/ideal.php';
			include_once 'includes/payment-gateways/sepa-direct-debit.php';
			include_once 'includes/payment-gateways/sepa-direct-debit-b2b.php';
			include_once 'includes/payment-gateways/invoice.php';
			include_once 'includes/payment-gateways/invoice-b2b.php';
			// Register payment methods
			add_filter('woocommerce_payment_gateways', array($this, 'add_betterpayment_gateways'));

			// Include helpers
			include_once 'includes/helpers/config-reader.php';

			// Include webhook route endpoint
			include_once 'includes/webhook.php';

			// Load translations
			load_plugin_textdomain( 'bp-plugin-woocommerce-api2', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Add a new integration to WooCommerce.
		 *
		 * @param Array of integrations.
		 */
		public function add_integration($integrations) {
			$integrations[] = 'BetterPayment_Plugin_Integration';

			return $integrations;
		}

		/**
		 * Add a new payment methods to WooCommerce.
		 *
		 * @param Array of methods.
		 */
		public function add_betterpayment_gateways($methods) {
			$methods[] = 'BetterPayment_Credit_Card';
			$methods[] = 'BetterPayment_PayPal';
			$methods[] = 'BetterPayment_Paydirekt';
			$methods[] = 'BetterPayment_Giropay';
			$methods[] = 'BetterPayment_Sofort';
			$methods[] = 'BetterPayment_Request_To_Pay';
			$methods[] = 'BetterPayment_Aiia_Pay';
			$methods[] = 'BetterPayment_Ideal';
			$methods[] = 'BetterPayment_Sepa_Direct_Debit';
			$methods[] = 'BetterPayment_Sepa_Direct_Debit_B2B';
			$methods[] = 'BetterPayment_Invoice';
			$methods[] = 'BetterPayment_Invoice_B2B';

			return $methods;
		}

		public function declare_cart_checkout_blocks_compatibility() {
			if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
				FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
			}
		}

		public function betterpayment_blocks_support() {
			if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
				include_once 'includes/blocks/payments/credit-card.php';
				include_once 'includes/blocks/payments/paypal.php';
				include_once 'includes/blocks/payments/aiia-pay.php';
				include_once 'includes/blocks/payments/giropay.php';
				include_once 'includes/blocks/payments/ideal.php';

				add_action(
					'woocommerce_blocks_payment_method_type_registration',
					function( PaymentMethodRegistry $payment_method_registry ) {
						$payment_method_registry->register( new BetterPayment_Credit_Card_Block() );
						$payment_method_registry->register( new BetterPayment_PayPal_Block() );
						$payment_method_registry->register( new BetterPayment_AiiaPay_Block() );
						$payment_method_registry->register( new BetterPayment_Giropay_Block() );
						$payment_method_registry->register( new BetterPayment_Ideal_Block() );
					}
				);
			}
		}
	}
}

$WC_BetterPayment_Plugin = new WC_BetterPayment_Plugin();