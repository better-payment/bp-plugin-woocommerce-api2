const googlePayData = window.wc.wcSettings.getPaymentMethodData(
    "betterpayment_googlepay"
  );
  
  const {initial_data: initialDataGooglePay, allowedCardNetworks, allowedAuthMethods, gateway, gatewayMerchantId, merchantId, merchantName, paymentUrl, environment} = googlePayData;
  
  const GooglePayBtn = ({onClose, onSubmit, onClick}) => {
    let paymentsClient = null;
    const {CART_STORE_KEY} = window.wc.wcBlocksData;
    const {data} = window.wp;
  
    const cartTotals = data.select(CART_STORE_KEY).getCartTotals();
    const currency = cartTotals.currency_code;
    const totalAmount = (cartTotals.total_price / 100).toFixed(2);
    const totalShipping = (cartTotals.total_shipping / 100).toFixed(2);
    const totalTax = (cartTotals.total_tax / 100).toFixed(2);
  
    const [scriptLoaded, setScriptLoaded] = React.useState(false);
  
    const onGooglePaymentButtonClicked = () => {
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
          merchantName
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
  
      paymentsClient
        .loadPaymentData(paymentDataRequest)
        .then(async (paymentResponse) => {
          const billingAddress =
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
              initialDataGooglePay.order_id + " - " + initialDataGooglePay.shop_name,
            customer_id: initialDataGooglePay.customer_id,
            customer_ip: initialDataGooglePay.customer_ip,
            app_name: initialDataGooglePay.app_name,
            app_version: initialDataGooglePay.app_version,
  
            // billing address parameters
            address: billingAddress?.address1 ?? null,
            address2: billingAddress?.address2 ?? null,
            city: billingAddress?.locality ?? null,
            postal_code: billingAddress?.postalCode ?? null,
            state: billingAddress?.administrativeArea ?? null,
            country: billingAddress?.countryCode ?? "DE",
            first_name: billingAddress?.name ?? null,
            last_name: billingAddress?.name ?? null,
            email: paymentResponse.email ?? null,
            phone: billingAddress?.phoneNumber ?? null,
  
            // shipping address parameters
            shipping_address: paymentResponse.shippingAddress?.address1 ?? null,
            shipping_address2: paymentResponse.shippingAddress?.address2 ?? null,
            shipping_city: paymentResponse.shippingAddress?.locality ?? null,
            shipping_postal_code:
              paymentResponse.shippingAddress?.postalCode ?? null,
            shipping_state:
              paymentResponse.shippingAddress?.administrativeArea ?? null,
            shipping_country:
              paymentResponse.shippingAddress?.countryCode ?? null,
            shipping_first_name: paymentResponse.shippingAddress?.name ?? null,
            shipping_last_name: paymentResponse.shippingAddress?.name ?? null,
            shipping_phone: paymentResponse.shippingAddress?.phoneNumber ?? null,
          };
       
          const response = await fetch(paymentUrl, {
            method: "POST",
            body: JSON.stringify(payload),
            headers: {
              'Content-Type': 'application/json',
            },
          });
  
          if (response.ok) {
            const data = JSON.parse(await response.json());
  
            if (data.error_code === 0) {
              transaction_id = data.transaction_id;
              transaction_status = data.status;
  
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
        })
        .catch((err) => {
          console.error("Payment Error: ", err);
        });
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
          paymentsClient = new google.payments.api.PaymentsClient({
            environment,
          });
  
          const button = paymentsClient.createButton({
            onClick: onGooglePaymentButtonClicked,
            buttonLocale: 'en',
            buttonType: 'checkout',
            // allowedPaymentMethods,
          });
  
          document.getElementById("google-pay-container").appendChild(button);
        };
        document.head.appendChild(script);
      } else {
        setScriptLoaded(true);
      }
    }, []);
  
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
  