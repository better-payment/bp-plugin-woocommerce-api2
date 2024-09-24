const paypalData = window.wc.wcSettings.getPaymentMethodData('betterpayment_paypal');

const paypalLabel = paypalData.title;
const paypalContent = () => {
    return paypalData.description;
};

window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: 'betterpayment_paypal',
    label: paypalLabel,
    content: Object( window.wp.element.createElement )( paypalContent, null ),
    edit: Object( window.wp.element.createElement )( paypalContent, null ),
    canMakePayment: () => true,
    ariaLabel: paypalLabel,
});