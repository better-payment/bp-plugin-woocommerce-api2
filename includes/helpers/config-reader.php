<?php

class Config_Reader {
	public static function get_app_name(): string
	{
		return 'WooCommerce';
	}

	public static function get_app_version(): string
	{
		global $woocommerce, $wp_version;
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');

		$plugin_file = ABSPATH . 'wp-content/plugins/bp-plugin-woocommerce-api2/bp-plugin-woocommerce-api2.php';
		$plugin_data = get_plugin_data($plugin_file);

		return 'WordPress ' . $wp_version . ', WooCommerce ' . $woocommerce->version . ', Plugin ' . $plugin_data['Version'];
	}

	public static function get_postback_url(): string
	{
		return set_url_scheme(get_rest_url(path: 'betterpayment/webhook'), 'https');
	}

	public static function get_api_url(): string
	{
		$settings = get_option('woocommerce_betterpayment_settings');
		$api_url = $settings['environment'] == 'test' ? $settings['testAPIUrl'] : $settings['productionAPIUrl'];

		return rtrim($api_url, '/');
	}

	public static function get_api_key(): string
	{
		$settings = get_option('woocommerce_betterpayment_settings');

		return $settings['environment'] == 'test' ? $settings['testAPIKey'] : $settings['productionAPIKey'];
	}

	public static function get_outgoing_key(): string
	{
		$settings = get_option('woocommerce_betterpayment_settings');

		return $settings['environment'] == 'test' ? $settings['testOutgoingKey'] : $settings['productionOutgoingKey'];
	}

	public static function get_incoming_key(): string
	{
		$settings = get_option('woocommerce_betterpayment_settings');

		return $settings['environment'] == 'test' ? $settings['testIncomingKey'] : $settings['productionIncomingKey'];
	}

	public static function get_app_environment(): string
	{
		$settings = get_option('woocommerce_betterpayment_settings');

		return $settings['environment'];
	}
}