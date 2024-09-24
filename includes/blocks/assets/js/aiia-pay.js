const aiiaPayData = window.wc.wcSettings.getPaymentMethodData('betterpayment_aiia');

const aiiaPayLabel = aiiaPayData.title;
const aiiaPayContent = () => {
    return aiiaPayData.description;
};

window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: 'betterpayment_aiia',
    label: aiiaPayLabel,
    content: Object( window.wp.element.createElement )( aiiaPayContent, null ),
    edit: Object( window.wp.element.createElement )( aiiaPayContent, null ),
    canMakePayment: () => true,
    ariaLabel: aiiaPayLabel,
});