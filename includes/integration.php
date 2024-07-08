<?php
if (class_exists('WC_Integration')) {
	class BetterPayment_Plugin_Integration extends WC_Integration {
		/**
		 * Init and hook in the integration.
		 */
		public function __construct() {
			$this->id           = 'betterpayment';
			$this->method_title = 'Better Payment';

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			add_action('woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));
		}

		/**
		 * Initialize integration settings form fields.
		 */
		public function init_form_fields() {
			$this->form_fields = [
				'environment'           => [
					'title'   => __( 'Environment', 'bp-plugin-woocommerce-api2' ),
					'type'    => 'select',
					'default' => 'test',
					'options' => [
						'test'       => __( 'Test', 'bp-plugin-woocommerce-api2' ),
						'production' => __( 'Production', 'bp-plugin-woocommerce-api2' )
					]
				],
				'testAPIUrl'            => [
					'title'       => __( 'Test API URL', 'bp-plugin-woocommerce-api2' ),
					'type'        => 'text',
					'description' => __( 'You can find your test API url in test dashboard of the payment provider.', 'bp-plugin-woocommerce-api2' ),
					'desc_tip'    => true,
					'placeholder' => 'https://testapi.betterpayment.de',
					'default'     => 'https://testapi.betterpayment.de',
				],
				'testAPIKey'            => [
					'title'       => __( 'Test API key', 'bp-plugin-woocommerce-api2' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'You can find your test API key in test dashboard of the payment provider.', 'bp-plugin-woocommerce-api2' ),
				],
				'testOutgoingKey'       => [
					'title'       => __( 'Test Outgoing key', 'bp-plugin-woocommerce-api2' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'You can find your test Outgoing key in test dashboard of the payment provider.', 'bp-plugin-woocommerce-api2' ),
				],
				'testIncomingKey'       => [
					'title'       => __( 'Test Incoming key', 'bp-plugin-woocommerce-api2' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'You can find your test Outgoing key in test dashboard of the payment provider.', 'bp-plugin-woocommerce-api2' ),
				],
				'productionAPIUrl'      => [
					'title'       => __( 'Production API URL', 'bp-plugin-woocommerce-api2' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'You can find your API url in production dashboard of the payment provider.', 'bp-plugin-woocommerce-api2' ),
					'placeholder' => 'https://api.betterpayment.de',
					'default'     => 'https://api.betterpayment.de',
				],
				'productionAPIKey'      => [
					'title'       => __( 'Production API key', 'bp-plugin-woocommerce-api2' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'You can find your API key in production dashboard of the payment provider.', 'bp-plugin-woocommerce-api2' ),
				],
				'productionOutgoingKey' => [
					'title'       => __( 'Production Outgoing key', 'bp-plugin-woocommerce-api2' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'You can find your Outgoing key in production dashboard of the payment provider.', 'bp-plugin-woocommerce-api2' ),
				],
				'productionIncomingKey' => [
					'title'       => __( 'Production Incoming key', 'bp-plugin-woocommerce-api2' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'You can find your Incoming key in production dashboard of the payment provider.', 'bp-plugin-woocommerce-api2' ),
				],
			];
		}
	}
}