<?php
/**
 * Plugin Name: Better Payment WooCommerce Extension
 * Plugin URI: https://github.com/better-payment/bp-plugin-woocommerce-api2
 * Description: Better Payment plugin to implement payment methods using API2
 * Version: 1.0.1
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

if ( ! class_exists( 'WC_BetterPayment_Plugin' ) ) :
	/**
	 * Integration demo class.
	 */
	class WC_BetterPayment_Plugin {
		/**
		 * Construct the plugin.
		 */
		public function __construct() {
			// Checks if WooCommerce is installed.
			add_action('admin_notices', function () {
				if (!is_plugin_active('woocommerce/woocommerce.php')) {
					echo "<div class='notice notice-error'><p>Better Payment WooCommerce Extension requires Woocommerce plugin to be activated</p></div>";
				}
			});

			add_action( 'before_woocommerce_init', function() {
				if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, false );
				}
			} );

			add_action( 'plugins_loaded', array( $this, 'init' ) );
		}

		/**
		 * Initialize the plugin.
		 */
		public function init() {
			// Include our integration class.
			include_once 'src/config-integration.php';
			// Register the integration.
			add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );

			// Include payment methods
			include_once 'payment-methods/credit-card.php';
			include_once 'payment-methods/paypal.php';
			// Register payment methods
			add_filter('woocommerce_payment_gateways', array($this, 'add_better_payment_gateway_methods'));
		}

		/**
		 * Add a new integration to WooCommerce.
		 *
		 * @param Array of integrations.
		 */
		public function add_integration($integrations) {
			$integrations[] = 'WC_BetterPayment_Plugin_Integration';
			return $integrations;
		}

		/**
		 * Add a new payment methods to WooCommerce.
		 *
		 * @param Array of methods.
		 */
		public function add_better_payment_gateway_methods($methods) {
			$methods[] = 'WC_Better_Payment_Credit_Card';
			$methods[] = 'WC_Better_Payment_PayPal';
			return $methods;
		}
	}
endif;

$WC_BetterPayment_Plugin = new WC_BetterPayment_Plugin( __FILE__ );