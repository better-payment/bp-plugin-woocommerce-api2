<?php
include_once 'abstract-betterpayment-gateway.php';

if (class_exists( 'Abstract_BetterPayment_Gateway' )) {
	abstract class Abstract_Async_BetterPayment_Gateway extends Abstract_BetterPayment_Gateway {
		public function process_payment( $order_id ) {
			$order = wc_get_order($order_id);
			$parameters = [];
			$parameters += $this->get_common_parameters($order_id);
			$parameters += $this->get_billing_address_parameters($order_id);
			$parameters += $this->get_shipping_address_parameters($order_id);
			$parameters += $this->get_redirect_url_parameters();

			error_log(print_r($parameters, true));

//			$order->update_status('pending-payment', 'Awaiting Credit Card payment');

			return [
				'result' => 'success',
				'redirect' => $this->get_return_url($order)
			];
		}


	}
}