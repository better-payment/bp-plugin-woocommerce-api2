const applePayData = window.wc.wcSettings.getPaymentMethodData('betterpayment_applepay');
const initialData = applePayData.initial_data;
const orderDescription = initialData.shop_name;

let transaction_id = null;
let transaction_status = null;

const APPLE_PAY_JS_API_VERSION = 14;
const REQUIRED_CONTACT_FIELDS = [
    "postalAddress",
    "email",
    "phone",
];

const ApplePayButton = (props) => {
    const { onClick, onClose, onSubmit, eventRegistration, emitResponse, billing, shippingData} = props;
    const { onPaymentProcessing } = eventRegistration;
    const { billingAddress } = billing;
    const { shippingAddress } = shippingData;
    const { data } = window.wp;
    const { CART_STORE_KEY, CHECKOUT_STORE_KEY } = window.wc.wcBlocksData;

    const buttonRef = React.useRef(null);
    const initApplePay = async () => {
        // WC Function
        // Provided to express payment methods that should be triggered when the payment method button is clicked
        // Which will signal to checkout the payment method has taken over payment processing
        onClick();

        const cartTotals = data.select(CART_STORE_KEY).getCartTotals();
        const currency = cartTotals.currency_code;
        const totalAmount = cartTotals.total_price / 100;
        const totalShipping = cartTotals.total_shipping / 100;
        const totalTax = cartTotals.total_tax / 100;

        if (!ApplePaySession) {
            onClose();
            return;
        }

        try {
            const requestBody = {
                countryCode: initialData.country ?? "DE",
                currencyCode: currency,
                merchantCapabilities: applePayData.supports3DS ? ["supports3DS"] : [],
                supportedNetworks: applePayData.supportedNetworks,
                total: {
                    label: orderDescription,
                    amount: totalAmount,
                },
                requiredShippingContactFields: REQUIRED_CONTACT_FIELDS,
                requiredBillingContactFields: REQUIRED_CONTACT_FIELDS,
            };

            const session = new ApplePaySession(
                APPLE_PAY_JS_API_VERSION,
                requestBody
            );

            session.onvalidatemerchant = () => {
                fetch('/wp-json/betterpayment/apple-pay-session', {
                    method: 'POST',
                })
                    .then(res => res.json())
                    .then(data => {
                        const merchantSession = JSON.parse(atob(data.applepay_payment_session_token));
                        session.completeMerchantValidation(merchantSession);
                    })
                    .catch(err => {
                        console.error("Error fetching merchant session", err);
                    });
            };

            session.onpaymentmethodselected = () => {
                const update = {
                    newTotal: {
                        label: orderDescription,
                        amount: totalAmount,
                    },
                };

                session.completePaymentMethodSelection(update);
            };

            session.onpaymentauthorized = async (event) => {
                billingAddress.first_name = event.payment?.billingContact?.givenName ?? null;
                billingAddress.last_name = event.payment?.billingContact?.familyName ?? null;
                billingAddress.address_1 = event.payment?.billingContact?.addressLines?.[0] ?? null;
                billingAddress.address_2 = event.payment?.billingContact?.addressLines?.[1] ?? null;
                billingAddress.city = event.payment?.billingContact?.locality ?? null;
                billingAddress.state = event.payment?.billingContact?.administrativeArea ?? null;
                billingAddress.country = event.payment?.billingContact?.countryCode ?? "DE";
                billingAddress.postcode = event.payment?.billingContact?.postalCode ?? null;
                billingAddress.email = event.payment?.shippingContact?.emailAddress ?? null;
                billingAddress.phone = event.payment?.shippingContact?.phoneNumber ?? null;

                shippingAddress.first_name = event.payment?.shippingContact?.givenName ?? null;
                shippingAddress.last_name = event.payment?.shippingContact?.familyName ?? null;
                shippingAddress.address_1 = event.payment?.shippingContact?.addressLines?.[0] ?? null;
                shippingAddress.address_2 = event.payment?.shippingContact?.addressLines?.[1] ?? null;
                shippingAddress.city = event.payment?.shippingContact?.locality ?? null;
                shippingAddress.state = event.payment?.shippingContact?.administrativeArea ?? null;
                shippingAddress.country = event.payment?.shippingContact?.countryCode ?? "DE";
                shippingAddress.postcode = event.payment?.shippingContact?.postalCode ?? null;
                shippingAddress.email = event.payment?.shippingContact?.emailAddress ?? null;
                shippingAddress.phone = event.payment?.shippingContact?.phoneNumber ?? null;

                const payload = {
                    // common parameters
                    applepay_token: btoa(JSON.stringify(event.payment?.token)),
                    amount: totalAmount,
                    currency: currency,
                    postback_url: initialData.postback_url,
                    shipping_costs: totalShipping,
                    vat: totalTax,
                    order_id: initialData.order_id,
                    merchant_reference: initialData.order_id + ' - ' + initialData.shop_name,
                    customer_id: initialData.customer_id,
                    customer_ip: initialData.customer_ip,
                    app_name: initialData.app_name,
                    app_version: initialData.app_version,

                    // billing address parameters
                    address: billingAddress.address_1,
                    address2: billingAddress.address_2,
                    city: billingAddress.city,
                    postal_code: billingAddress.postcode,
                    state: billingAddress.state,
                    country: billingAddress.country,
                    first_name: billingAddress.first_name,
                    last_name: billingAddress.last_name,
                    email: billingAddress.email,
                    phone: billingAddress.phone,

                    // shipping address parameters
                    shipping_address: shippingAddress.address_1,
                    shipping_address2: shippingAddress.address_2,
                    shipping_city: shippingAddress.city,
                    shipping_postal_code: shippingAddress.postcode,
                    shipping_state: shippingAddress.state,
                    shipping_country: shippingAddress.country,
                    shipping_first_name: shippingAddress.first_name,
                    shipping_last_name: shippingAddress.last_name,
                };

                const response = await fetch('/wp-json/betterpayment/payment', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });

                if (response.ok) {
                    const data = JSON.parse(await response.json());

                    if (data.error_code === 0) {
                        session.completePayment({
                            status: ApplePaySession.STATUS_SUCCESS
                        });

                        transaction_id = data.transaction_id;
                        transaction_status = data.status;

                        // WC Function
                        // Submits the checkout and begins processing
                        onSubmit();
                    }
                    else {
                        console.error('Payment Gateway request failed:', response.status, response.statusText);
                        console.error('Error details:', data);

                        session.completePayment({
                            status: ApplePaySession.STATUS_FAILURE
                        });

                        onClose();
                    }
                }
                else {
                    const errorData = await response.json();
                    console.error('Payment Gateway request failed:', response.status, response.statusText);
                    console.error('Error details:', errorData);

                    session.completePayment({
                        status: ApplePaySession.STATUS_FAILURE
                    });

                    onClose();
                }
            };

            session.oncancel = (event) => {
                onClose();
            };

            session.begin();
        } catch (e) {
            onClose();
        }
    }

    React.useEffect(() => {
        // Dynamically load the Apple Pay SDK script
        const script = document.createElement('script');
        script.src = 'https://applepay.cdn-apple.com/jsapi/1.latest/apple-pay-sdk.js';
        script.crossOrigin = 'anonymous';
        document.head.appendChild(script);

        // Inject CSS styles for <apple-pay-button>
        const style = document.createElement('style');
        style.textContent = `
            apple-pay-button {
                --apple-pay-button-width: 150px;
                --apple-pay-button-height: 30px;
                --apple-pay-button-border-radius: 3px;
                --apple-pay-button-padding: 0px 0px;
                --apple-pay-button-box-sizing: border-box;
            }
        `;
        document.head.appendChild(style);

        // Add click event listener
        const button = buttonRef.current;
        if (button) {
            button.addEventListener('click', initApplePay);
        }

        // TODO: Make it specific to Apple Pay
        // passes following metadata in other checkout payment methods (i.e. Credit Card)
        const unsubscribe = onPaymentProcessing(async () => {
            return {
                type: emitResponse.responseTypes.SUCCESS,
                meta: {
                    paymentMethodData: {
                        transaction_id: transaction_id ?? '',
                        transaction_status: transaction_status ?? '',
                        apple_pay_order_id: initialData.order_id,
                    },
                },
            };
        });

        return () => {
            // Cleanup script, style, and event listener
            document.head.removeChild(script);
            document.head.removeChild(style);
            if (button) {
                button.removeEventListener('click', initApplePay);
            }

            unsubscribe();
        };
    }, [
        emitResponse.responseTypes.SUCCESS,
        onPaymentProcessing,
    ]);

    // TODO: Hide Apple Pay button when unable to generate Apple Pay merchant session
    // Render the <apple-pay-button> element
    return React.createElement(
        'apple-pay-button',
        {
            buttonstyle: 'black',
            type: 'plain',
            locale: 'en-US',
            ref: buttonRef, // Use ref to access DOM element
        },
        null // No children
    );
};


window.wc.wcBlocksRegistry.registerExpressPaymentMethod({
    name: 'betterpayment_applepay',
    paymentMethodId: 'betterpayment_applepay',
    title: applePayData.title,
    description: applePayData.description,
    content: Object( window.wp.element.createElement )( ApplePayButton, null ),
    edit: Object( window.wp.element.createElement )( ApplePayButton, null ),
    canMakePayment: () => true,
});
