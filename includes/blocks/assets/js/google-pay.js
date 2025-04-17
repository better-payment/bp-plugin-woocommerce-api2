const googlePayData = window.wc.wcSettings.getPaymentMethodData(
  "betterpayment_googlepay"
);

let transaction_id_gpay = null;
let transaction_status_gpay = null;

const {
  initial_data: initialDataGooglePay,
  allowedCardNetworks,
  allowedAuthMethods,
  gateway,
  gatewayMerchantId,
  merchantId,
  merchantName,
  paymentUrl,
  environment,
  locale,
} = googlePayData;

function showNotice(message, type = "error") {
  const container =
    document.querySelector(".woocommerce-notices-wrapper") ||
    document.querySelector(".wc-block-components-notices");

  if (!container) return;
  // Remove existing notices
  const oldNotices = container.querySelectorAll(
    ".wc-block-components-notice-banner"
  );
  oldNotices.forEach((n) => n.remove());

  const notice = document.createElement("div");
  notice.className = `wc-block-components-notice-banner is-${type}`;
  notice.innerHTML =
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path></svg><div>' +
    message +
    "</div>";
  notice.setAttribute("role", "alert");

  container.prepend(notice);
}

const GooglePayBtn = ({
  onClose,
  onSubmit,
  onClick,
  eventRegistration,
  emitResponse,
  billing,
  shippingData,
}) => {
  const {onPaymentProcessing, onCheckoutValidation} = eventRegistration;
  const {billingAddress} = billing;
  const {shippingAddress} = shippingData;
  let paymentsClient = null;
  const {CART_STORE_KEY} = window.wc.wcBlocksData;
  const {data} = window.wp;

  const cartTotals = data.select(CART_STORE_KEY).getCartTotals();
  const currency = cartTotals.currency_code;
  const totalAmount = (cartTotals.total_price / 100).toFixed(2);
  const totalShipping = (cartTotals.total_shipping / 100).toFixed(2);
  const totalTax = (cartTotals.total_tax / 100).toFixed(2);

  const [scriptLoaded, setScriptLoaded] = React.useState(false);

  const onGooglePaymentButtonClicked = async () => {
    // WC Function
    // Provided to express payment methods that should be triggered when the payment method button is clicked
    // Which will signal to checkout the payment method has taken over payment processing
    onClick();
    const paymentDataRequest = {
      apiVersion: 2,
      apiVersionMinor: 0,
      allowedPaymentMethods: [
        {
          type: "CARD",
          parameters: {
            allowedAuthMethods,
            allowedCardNetworks,
            billingAddressRequired: true,
            billingAddressParameters: {
              format: "FULL",
              phoneNumberRequired: true,
            },
          },
          tokenizationSpecification: {
            type: "PAYMENT_GATEWAY",
            parameters: {
              gateway,
              gatewayMerchantId,
            },
          },
        },
      ],
      merchantInfo: {
        merchantId,
        merchantName,
      },
      transactionInfo: {
        totalPriceStatus: "FINAL",
        totalPrice: totalAmount,
        currencyCode: currency,
        checkoutOption: "COMPLETE_IMMEDIATE_PURCHASE",
      },
      emailRequired: true,
      shippingAddressRequired: true,
      shippingAddressParameters: {
        phoneNumberRequired: true,
      },
    };
    try {
      const paymentResponse = await paymentsClient.loadPaymentData(
        paymentDataRequest
      );

      const billingAddressGoogle =
        paymentResponse.paymentMethodData?.info?.billingAddress;

      const payload = {
        googlepay_token:
          paymentResponse.paymentMethodData?.tokenizationData?.token,
        amount: totalAmount,
        currency: currency,
        postback_url: initialDataGooglePay.postback_url,
        shipping_costs: totalShipping,
        vat: totalTax,
        order_id: initialDataGooglePay.order_id,
        merchant_reference:
          initialDataGooglePay.order_id +
          " - " +
          initialDataGooglePay.shop_name,
        customer_id: initialDataGooglePay.customer_id,
        customer_ip: initialDataGooglePay.customer_ip,
        app_name: initialDataGooglePay.app_name,
        app_version: initialDataGooglePay.app_version,

        // billing address parameters
        address: billingAddressGoogle?.address1 ?? null,
        address2: billingAddressGoogle?.address2 ?? null,
        city: billingAddressGoogle?.locality ?? null,
        postal_code: billingAddressGoogle?.postalCode ?? null,
        state: billingAddressGoogle?.administrativeArea ?? null,
        country: billingAddressGoogle?.countryCode ?? "DE",
        first_name: billingAddressGoogle?.name ?? null,
        last_name: billingAddressGoogle?.name ?? null,
        email: paymentResponse.email ?? null,
        phone: billingAddressGoogle?.phoneNumber ?? null,

        // shipping address parameters
        shipping_address: paymentResponse.shippingAddress?.address1 ?? null,
        shipping_address2: paymentResponse.shippingAddress?.address2 ?? null,
        shipping_city: paymentResponse.shippingAddress?.locality ?? null,
        shipping_postal_code:
          paymentResponse.shippingAddress?.postalCode ?? null,
        shipping_state:
          paymentResponse.shippingAddress?.administrativeArea ?? null,
        shipping_country: paymentResponse.shippingAddress?.countryCode ?? null,
        shipping_first_name: paymentResponse.shippingAddress?.name ?? null,
        shipping_last_name: paymentResponse.shippingAddress?.name ?? null,
        shipping_phone: paymentResponse.shippingAddress?.phoneNumber ?? null,
      };

      const response = await fetch(paymentUrl, {
        method: "POST",
        body: JSON.stringify(payload),
        headers: {
          "Content-Type": "application/json",
        },
      });
      

      billingAddress.first_name = billingAddressGoogle?.name ?? null;
      billingAddress.last_name = billingAddressGoogle?.name ?? null;
      billingAddress.address_1 = billingAddressGoogle?.address1 ?? null;
      billingAddress.address_2 = billingAddressGoogle?.address2 ?? null;
      billingAddress.city = billingAddressGoogle?.locality ?? null;
      billingAddress.state = billingAddressGoogle?.administrativeArea ?? null;
      billingAddress.country = billingAddressGoogle?.countryCode ?? "DE";
      billingAddress.postcode = billingAddressGoogle?.postalCode ?? null;
      billingAddress.email = paymentResponse.email ?? null;
      billingAddress.phone = billingAddressGoogle?.phoneNumber ?? null;

      shippingAddress.first_name =
        paymentResponse.shippingAddress?.name ?? null;
      shippingAddress.last_name = paymentResponse.shippingAddress?.name ?? null;
      shippingAddress.address_1 =
        paymentResponse.shippingAddress?.address1 ?? null;
      shippingAddress.address_2 =
        paymentResponse.shippingAddress?.address2 ?? null;
      shippingAddress.city = paymentResponse.shippingAddress?.locality ?? null;
      shippingAddress.state =
        paymentResponse.shippingAddress?.administrativeArea ?? null;
      shippingAddress.country =
        paymentResponse.shippingAddress?.countryCode ?? "DE";
      shippingAddress.postcode =
        paymentResponse.shippingAddress?.postalCode ?? null;
      shippingAddress.email = paymentResponse.email ?? null;
      shippingAddress.phone =
        paymentResponse.shippingAddress?.phoneNumber ?? null;


      if (response.ok) {
        const data = await response.json();

        if (data.error_code === 0) {
          transaction_id_gpay = data.transaction_id;
          transaction_status_gpay = data.status;
          
          // WC Function
          // Submits the checkout and begins processing
          onSubmit();
        } else {
          console.error(
            "Payment Gateway request failed:",
            response.status,
            response.statusText
          );
          console.error("Error details:", data);

          onClose();
        }
      } else {
        const errorData = await response.json();
        console.error(
          "Payment Gateway request failed:",
          response.status,
          response.statusText
        );
        console.error("Error details:", errorData);

        onClose();
      }
    } catch (err) {
      showNotice(err, "error");
      console.error("Payment Error: ", err);
      onClose();
    }
  };

  // Load Google Pay API script manually
  React.useEffect(() => {
    const scriptSrc = "https://pay.google.com/gp/p/js/pay.js";

    if (!document.querySelector(`script[src="${scriptSrc}"]`)) {
      const script = document.createElement("script");
      script.src = scriptSrc;
      script.async = true;
      script.onload = () => {
        setScriptLoaded(true);
      };
      document.head.appendChild(script);
    } else {
      setScriptLoaded(true);
    }

    const unsubscribe = onPaymentProcessing(async () => {
      return {
        type: emitResponse.responseTypes.SUCCESS,
        meta: {
          paymentMethodData: {
            transaction_id: transaction_id_gpay ?? "",
            transaction_status: transaction_status_gpay ?? "",
          },
        },
      };
    });

    return () => {
      unsubscribe();
    };
  }, [
    emitResponse.responseTypes.ERROR,
    emitResponse.responseTypes.SUCCESS,
    onPaymentProcessing,
  ]);

  React.useEffect(() => {
    if (scriptLoaded) {
      paymentsClient = new google.payments.api.PaymentsClient({
        environment,
      });
      const button = paymentsClient.createButton({
        onClick: onGooglePaymentButtonClicked,
        buttonLocale: locale?.slice(0, 2),
        buttonType: "checkout",
      });

      document.getElementById("google-pay-container").appendChild(button);
    }
  }, [scriptLoaded]);

  // Don't render the button until script is loaded
  if (!scriptLoaded) {
    return React.createElement("p", null, "Loading Google Pay button...");
  }

  return React.createElement("div", {id: "google-pay-container"});
};

// Register Google Pay with WooCommerce
window.wc.wcBlocksRegistry.registerExpressPaymentMethod({
  name: "betterpayment_googlepay",
  paymentMethodId: "betterpayment_googlepay",
  title: googlePayData.title,
  description: googlePayData.description,
  content: React.createElement(GooglePayBtn, null),
  edit: React.createElement(GooglePayBtn, null),
  canMakePayment: () => true,
});
