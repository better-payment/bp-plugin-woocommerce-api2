<?php
if (class_exists('WC_Integration')) {
	class WC_BetterPayment_Plugin_Integration extends WC_Integration {
		/**
		 * Init and hook in the integration.
		 */
		public function __construct() {
			global $woocommerce;

			$this->id           = 'integration-demo';
			$this->method_title = __( 'Better Payment' );
//			$this->method_description = __( 'Base configuration');

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables.
//			$this->api_key = $this->get_option( 'api_key' );
//			$this->debug   = $this->get_option( 'debug' );

			// Actions.
			add_action( 'woocommerce_update_options_integration_' . $this->id, array(
				$this,
				'process_admin_options'
			) );
		}

		/**
		 * Initialize integration settings form fields.
		 */
		public function init_form_fields() {
			$this->form_fields = [
				'environment'           => [
					'title'   => __( 'Environment' ),
					'type'    => 'select',
					'default' => 'test',
					'options' => [
						'test'       => __( 'Test' ),
						'production' => __( 'Production' )
					]
				],
				'testAPIUrl'            => [
					'title'       => __( 'Test API URL' ),
					'type'        => 'text',
					'description' => __( 'You can find your test API url in test dashboard of the payment provider.' ),
					'desc_tip'    => true,
					'placeholder' => 'https://testapi.betterpayment.de',
					'default'     => 'https://testapi.betterpayment.de',
				],
				'testAPIKey'            => [
					'title'       => __( 'Test API key' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'You can find your test API key in test dashboard of the payment provider.' ),
				],
				'testOutgoingKey'       => [
					'title'       => __( 'Test Outgoing key' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'You can find your test Outgoing key in test dashboard of the payment provider.' ),
				],
				'testIncomingKey'       => [
					'title'       => __( 'Test Incoming key' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'You can find your test Outgoing key in test dashboard of the payment provider.' ),
				],
				'productionAPIUrl'      => [
					'title'       => __( 'Production API URL' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'You can find your API url in production dashboard of the payment provider.' ),
					'placeholder' => 'https://api.betterpayment.de',
					'default'     => 'https://api.betterpayment.de',
				],
				'productionAPIKey'      => [
					'title'       => __( 'Production API key' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'You can find your API key in production dashboard of the payment provider.' ),
				],
				'productionOutgoingKey' => [
					'title'       => __( 'Production Outgoing key' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'You can find your Outgoing key in production dashboard of the payment provider.' ),
				],
				'productionIncomingKey' => [
					'title'       => __( 'Production Incoming key' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'You can find your Incoming key in production dashboard of the payment provider.' ),
				],
			];
		}
	}
}