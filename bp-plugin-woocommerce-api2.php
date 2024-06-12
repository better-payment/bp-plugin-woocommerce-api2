<?php
/**
 * Plugin Name: Better Payment WooCommerce Extension
 * Plugin URI: https://github.com/better-payment/bp-plugin-woocommerce-api2
 * Description: Better Payment plugin to implement payment methods using API2
 * Version: 1.0.0
 * Author: Better Payment
 * Author URI: https://betterpayment.de
 * Developer: Your Name
 * Developer URI: https://betterpayment.de
 * Text Domain: betterpayment
 * Domain Path: /languages
 *
 * Woo: 12345:342928dfsfhsf8429842374wdf4234sfd
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

//defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_BetterPayment_Plugin' ) ) {
	class WC_BetterPayment_Plugin {
		/**
		 * Construct the plugin.
		 */
		public function __construct() {
			// Checks if WooCommerce is installed.
			add_action('admin_notices', function () {
				if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
					echo "<div class='notice notice-error'><p>Better Payment WooCommerce Extension requires Woocommerce plugin to be activated</p></div>";
				}
			});

			add_action( 'plugins_loaded', array( $this, 'init' ) );
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
			include_once 'includes/payment-gateways/sepa-direct-debit.php';
			// Register payment methods
			add_filter('woocommerce_payment_gateways', array($this, 'add_betterpayment_gateways'));

			// Include webhook route endpoint
			include_once 'includes/webhook.php';
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
			$methods[] = 'BetterPayment_Sepa_Direct_Debit';

			return $methods;
		}
	}
}

$WC_BetterPayment_Plugin = new WC_BetterPayment_Plugin();