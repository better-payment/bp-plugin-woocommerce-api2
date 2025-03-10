const invoiceB2BData = window.wc.wcSettings.getPaymentMethodData('betterpayment_kar_b2b');

const invoiceB2BLabel = invoiceB2BData.title;
const invoiceB2BIsRiskCheckAgreementRequired = invoiceB2BData.isRiskCheckAgreementRequired;

const invoiceB2BContent = (props) => {
    const { eventRegistration, emitResponse } = props;
    const { onPaymentProcessing } = eventRegistration;

    const [betterpayment_kar_b2b_risk_check_agreement, setRiskCheckAgreement] = React.useState(false);

    React.useEffect(() => {
        const unsubscribe = onPaymentProcessing(async () => {
            const paymentMethodData = {};

            if (invoiceB2BIsRiskCheckAgreementRequired) {
                paymentMethodData.betterpayment_kar_b2b_risk_check_agreement = betterpayment_kar_b2b_risk_check_agreement;
            }

            return {
                type: emitResponse.responseTypes.SUCCESS,
                meta: {
                    paymentMethodData,
                },
            };
        });

        // Unsubscribes when this component is unmounted.
        return () => {
            unsubscribe();
        };
    }, [
        emitResponse.responseTypes.SUCCESS,
        onPaymentProcessing,
        betterpayment_kar_b2b_risk_check_agreement,
        invoiceB2BIsRiskCheckAgreementRequired
    ]);

    return invoiceB2BIsRiskCheckAgreementRequired && React.createElement('div', null,
        React.createElement('input', { type: 'checkbox', id: 'betterpayment_kar_b2b_risk_check_agreement', value: betterpayment_kar_b2b_risk_check_agreement, onChange: (event) => { setRiskCheckAgreement(event.target.checked); } }),
        React.createElement('label', { htmlFor: 'betterpayment_kar_b2b_risk_check_agreement' }, betterpayment_kar_b2b_l10n.label_risk_check_agreement)
    );
};

window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: 'betterpayment_kar_b2b',
    label: invoiceB2BLabel,
    content: Object( window.wp.element.createElement )( invoiceB2BContent, null ),
    edit: Object( window.wp.element.createElement )( invoiceB2BContent, null ),
    canMakePayment: () => true,
    ariaLabel: invoiceB2BLabel,
});
