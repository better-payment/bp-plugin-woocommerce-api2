const giropayData = window.wc.wcSettings.getPaymentMethodData('betterpayment_giro');

const giropayLabel = giropayData.title;
const giropayContent = () => {
    return giropayData.description;
};

window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: 'betterpayment_giro',
    label: giropayLabel,
    content: Object( window.wp.element.createElement )( giropayContent, null ),
    edit: Object( window.wp.element.createElement )( giropayContent, null ),
    canMakePayment: () => true,
    ariaLabel: giropayLabel,
});