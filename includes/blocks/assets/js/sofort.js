const sofortData = window.wc.wcSettings.getPaymentMethodData('betterpayment_sofort');

const sofortLabel = sofortData.title;
const sofortContent = () => {
    return sofortData.description;
};

window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: 'betterpayment_sofort',
    label: sofortLabel,
    content: Object( window.wp.element.createElement )( sofortContent, null ),
    edit: Object( window.wp.element.createElement )( sofortContent, null ),
    canMakePayment: () => true,
    ariaLabel: sofortLabel,
});