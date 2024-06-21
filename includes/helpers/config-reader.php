<?php

class Config_Reader {
	public static function get_app_name(): string
	{
		return 'WooCommerce';
	}

	public static function get_app_version(): string
	{
		global $woocommerce;
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');

		$plugin_file = 'wp-content/plugins/bp-plugin-woocommerce-api2/bp-plugin-woocommerce-api2.php';
		$plugin_data = get_plugin_data($plugin_file);

		return 'WooCommerce ' . $woocommerce->version . ', Plugin ' . $plugin_data['Version'];
	}

	public static function get_api_url() {
		$settings = get_option('woocommerce_betterpayment_settings');
		$api_url = $settings['environment'] == 'test' ? $settings['testAPIUrl'] : $settings['productionAPIUrl'];

		return rtrim($api_url, '/');
	}

	public static function get_api_key() {
		$settings = get_option('woocommerce_betterpayment_settings');

		return $settings['environment'] == 'test' ? $settings['testAPIKey'] : $settings['productionAPIKey'];
	}

	public static function get_outgoing_key() {
		$settings = get_option('woocommerce_betterpayment_settings');

		return $settings['environment'] == 'test' ? $settings['testOutgoingKey'] : $settings['productionOutgoingKey'];
	}

	public static function get_incoming_key() {
		$settings = get_option('woocommerce_betterpayment_settings');

		return $settings['environment'] == 'test' ? $settings['testIncomingKey'] : $settings['productionIncomingKey'];
	}
}