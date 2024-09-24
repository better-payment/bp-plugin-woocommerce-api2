const paypalData = window.wc.wcSettings.getPaymentMethodData('betterpayment_paypal');

const creditCardLabel = paypalData.title;
const creditCardContent = () => {
    return paypalData.description;
};

window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: 'betterpayment_cc',
    label: creditCardLabel,
    content: Object( window.wp.element.createElement )( creditCardContent, null ),
    edit: Object( window.wp.element.createElement )( creditCardContent, null ),
    canMakePayment: () => true,
    ariaLabel: creditCardLabel,
    // supports: {
    //     features: settings.supports,
    // }
});