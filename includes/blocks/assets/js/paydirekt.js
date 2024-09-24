const paydirektData = window.wc.wcSettings.getPaymentMethodData('betterpayment_paydirekt');

const paydirektLabel = paydirektData.title;
const paydirektContent = () => {
    return paydirektData.description;
};

window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: 'betterpayment_paydirekt',
    label: paydirektLabel,
    content: Object( window.wp.element.createElement )( paydirektContent, null ),
    edit: Object( window.wp.element.createElement )( paydirektContent, null ),
    canMakePayment: () => true,
    ariaLabel: paydirektLabel,
    // supports: {
    //     features: settings.supports,
    // }
});