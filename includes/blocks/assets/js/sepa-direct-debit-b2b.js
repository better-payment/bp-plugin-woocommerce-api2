const sepaDirectDebitB2BData = window.wc.wcSettings.getPaymentMethodData('betterpayment_dd_b2b');

const sepaDirectDebitB2BLabel = sepaDirectDebitB2BData.title;

const sepaDirectDebitB2BMandateDescription = sepaDirectDebitB2BData.mandateDescription;
const sepaDirectDebitB2BAccountHolder = sepaDirectDebitB2BData.accountHolder;
const sepaDirectDebitB2BMandateReference = sepaDirectDebitB2BData.mandateReference;

const sepaDirectDebitB2BIsRiskCheckAgreementRequired = sepaDirectDebitB2BData.isRiskCheckAgreementRequired;

const sepaDirectDebitB2BContent = (props) => {
    const { eventRegistration, emitResponse } = props;
    const { onPaymentProcessing } = eventRegistration;

    const [betterpayment_dd_b2b_iban, setIban] = React.useState('');
    const [betterpayment_dd_b2b_bic, setBic] = React.useState('');
    const [betterpayment_dd_b2b_account_holder] = React.useState(sepaDirectDebitB2BAccountHolder);
    const [betterpayment_dd_b2b_mandate_reference] = React.useState(sepaDirectDebitB2BMandateReference);
    const [betterpayment_dd_b2b_mandate_agreement, setMandateAgreement] = React.useState(false);
    const [betterpayment_dd_b2b_risk_check_agreement, setRiskCheckAgreement] = React.useState(false);

    React.useEffect(() => {
        const unsubscribe = onPaymentProcessing(async () => {
            const paymentMethodData = {
                betterpayment_dd_b2b_iban,
                betterpayment_dd_b2b_bic,
                betterpayment_dd_b2b_account_holder,
                betterpayment_dd_b2b_mandate_reference,
                betterpayment_dd_b2b_mandate_agreement,
            };

            if (sepaDirectDebitB2BIsRiskCheckAgreementRequired) {
                paymentMethodData.betterpayment_dd_b2b_risk_check_agreement = betterpayment_dd_b2b_risk_check_agreement;
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
        betterpayment_dd_b2b_iban,
        betterpayment_dd_b2b_bic,
        betterpayment_dd_b2b_account_holder,
        betterpayment_dd_b2b_mandate_reference,
        betterpayment_dd_b2b_mandate_agreement,
        betterpayment_dd_b2b_risk_check_agreement,
        sepaDirectDebitB2BIsRiskCheckAgreementRequired,
    ]);

    return React.createElement('div', null,
        React.createElement('div', null,
            React.createElement('label', { htmlFor: 'betterpayment_dd_b2b_iban' }, betterpayment_dd_b2b_i10n.label_iban),
            React.createElement('br'),
            React.createElement('input', { type: 'text', id: 'betterpayment_dd_b2b_iban', value: betterpayment_dd_b2b_iban, onChange: (event) => { setIban(event.target.value); }}),
            React.createElement('br'),
            React.createElement('label', { htmlFor: 'betterpayment_dd_b2b_bic' }, betterpayment_dd_b2b_i10n.label_bic),
            React.createElement('br'),
            React.createElement('input', { type: 'text', id: 'betterpayment_dd_b2b_bic', value: betterpayment_dd_b2b_bic, onChange: (event) => { setBic(event.target.value); }}),
            React.createElement('br'),
            React.createElement('span', { dangerouslySetInnerHTML: { __html: sepaDirectDebitB2BMandateDescription }}),
            React.createElement('div', null,
                React.createElement('input', { type: 'checkbox', id: 'betterpayment_dd_b2b_mandate_agreement', checked: betterpayment_dd_b2b_mandate_agreement, onChange: (event) => { setMandateAgreement(event.target.checked); }}),
                React.createElement('label', { htmlFor: 'betterpayment_dd_b2b_mandate_agreement' }, betterpayment_dd_b2b_i10n.label_mandate_agreement)
            )
        ),
        sepaDirectDebitB2BIsRiskCheckAgreementRequired && React.createElement('div', null,
            React.createElement('h4', null, betterpayment_dd_b2b_i10n.label_risk_check_information),
            React.createElement('input', { type: 'checkbox', id: 'betterpayment_dd_b2b_risk_check_agreement', checked: betterpayment_dd_b2b_risk_check_agreement, onChange: (event) => { setRiskCheckAgreement(event.target.checked); }}),
            React.createElement('label', { htmlFor: 'betterpayment_dd_b2b_risk_check_agreement' }, betterpayment_dd_b2b_i10n.label_risk_check_agreement)
        )
    );
};

window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: 'betterpayment_dd_b2b',
    label: sepaDirectDebitB2BLabel,
    content: Object( window.wp.element.createElement )( sepaDirectDebitB2BContent, null ),
    edit: Object( window.wp.element.createElement )( sepaDirectDebitB2BContent, null ),
    canMakePayment: () => true,
    ariaLabel: sepaDirectDebitB2BLabel,
});
