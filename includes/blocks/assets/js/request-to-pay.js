const requestToPayData = window.wc.wcSettings.getPaymentMethodData('betterpayment_rtp');

const requestToPayLabel = requestToPayData.title;
const requestToPayContent = () => {
    return requestToPayData.description;
};

window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: 'betterpayment_rtp',
    label: requestToPayLabel,
    content: Object( window.wp.element.createElement )( requestToPayContent, null ),
    edit: Object( window.wp.element.createElement )( requestToPayContent, null ),
    canMakePayment: () => true,
    ariaLabel: requestToPayLabel,
});