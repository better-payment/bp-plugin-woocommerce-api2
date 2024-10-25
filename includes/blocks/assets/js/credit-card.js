const creditCardData = window.wc.wcSettings.getPaymentMethodData('betterpayment_cc');

const creditCardLabel = creditCardData.title;
const creditCardContent = () => {
    return creditCardData.description;
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