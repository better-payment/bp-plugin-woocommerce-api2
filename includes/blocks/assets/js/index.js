const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

const settings = window.wc.wcSettings.getSetting( 'betterpayment_cc_data', {} );
const label = settings.title;

const Content = () => {
    return settings.description;
};

registerPaymentMethod({
    name: 'betterpayment_cc',
    label: label,
    content: Object( window.wp.element.createElement )( Content, null ),
    edit: Object( window.wp.element.createElement )( Content, null ),
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    }
});