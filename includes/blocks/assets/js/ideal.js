const idealData = window.wc.wcSettings.getPaymentMethodData('betterpayment_ideal');

const idealLabel = idealData.title;
const idealContent = () => {
    return idealData.description;
};

window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: 'betterpayment_ideal',
    label: idealLabel,
    content: Object( window.wp.element.createElement )( idealContent, null ),
    edit: Object( window.wp.element.createElement )( idealContent, null ),
    canMakePayment: () => true,
    ariaLabel: idealLabel,
});