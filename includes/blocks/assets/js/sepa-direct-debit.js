const sepaDirectDebitData = window.wc.wcSettings.getPaymentMethodData('betterpayment_dd');

const sepaDirectDebitLabel = sepaDirectDebitData.title;
const sepaDirectDebitMandateDescription = sepaDirectDebitData.mandateDescription;
const sepaDirectDebitAccountHolder = sepaDirectDebitData.accountHolder;
const sepaDirectDebitMandateReference = sepaDirectDebitData.mandateReference;

const sepaDirectDebitIsGenderCollected = sepaDirectDebitData.isGenderCollected;
const sepaDirectDebitIsDateOfBirthCollected = sepaDirectDebitData.isDateOfBirthCollected;
const sepaDirectDebitIsRiskCheckAgreementRequired = sepaDirectDebitData.isRiskCheckAgreementRequired;

const sepaDirectDebitContent = (props) => {
    const { eventRegistration, emitResponse } = props;
    const { onPaymentProcessing } = eventRegistration;

    const [betterpayment_dd_iban, setIban] = React.useState('');
    const [betterpayment_dd_bic, setBic] = React.useState('');
    const [betterpayment_dd_account_holder] = React.useState(sepaDirectDebitAccountHolder);
    const [betterpayment_dd_mandate_reference] = React.useState(sepaDirectDebitMandateReference);

    const [betterpayment_dd_mandate_agreement, setMandateAgreement] = React.useState(false);

    const [betterpayment_dd_gender, setGender] = React.useState('');
    const [betterpayment_dd_date_of_birth, setDateOfBirth] = React.useState('');
    const [betterpayment_dd_risk_check_agreement, setRiskCheckAgreement] = React.useState(false);

    React.useEffect(() => {
        const unsubscribe = onPaymentProcessing(async () => {
            const paymentMethodData = {
                betterpayment_dd_iban,
                betterpayment_dd_bic,
                betterpayment_dd_account_holder,
                betterpayment_dd_mandate_reference,
                betterpayment_dd_mandate_agreement,
            };

            if (sepaDirectDebitIsGenderCollected) {
                paymentMethodData.betterpayment_dd_gender = betterpayment_dd_gender;
            }
            if (sepaDirectDebitIsDateOfBirthCollected) {
                paymentMethodData.betterpayment_dd_date_of_birth = betterpayment_dd_date_of_birth;
            }
            if (sepaDirectDebitIsRiskCheckAgreementRequired) {
                paymentMethodData.betterpayment_dd_risk_check_agreement = betterpayment_dd_risk_check_agreement;
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

        betterpayment_dd_iban,
        betterpayment_dd_bic,
        betterpayment_dd_account_holder,
        betterpayment_dd_mandate_reference,
        betterpayment_dd_mandate_agreement,

        betterpayment_dd_gender,
        betterpayment_dd_date_of_birth,
        betterpayment_dd_risk_check_agreement,
        sepaDirectDebitIsGenderCollected,
        sepaDirectDebitIsDateOfBirthCollected,
        sepaDirectDebitIsRiskCheckAgreementRequired
    ]);

    return React.createElement('div', null,
        React.createElement('div', null,
            React.createElement('label', { htmlFor: 'betterpayment_dd_iban' }, betterpayment_dd_i10n.label_iban),
            React.createElement('br'),
            React.createElement('input', { type: 'text', id: 'betterpayment_dd_iban', value: betterpayment_dd_iban, onChange: (event) => { setIban(event.target.value); } }),
            React.createElement('br'),
            React.createElement('label', { htmlFor: 'betterpayment_dd_bic' }, betterpayment_dd_i10n.label_bic),
            React.createElement('br'),
            React.createElement('input', { type: 'text', id: 'betterpayment_dd_bic', value: betterpayment_dd_bic, onChange: (event) => { setBic(event.target.value); } }),
            React.createElement('br'),
            React.createElement('span', { dangerouslySetInnerHTML: { __html: sepaDirectDebitMandateDescription } }),
            React.createElement('div', null,
                React.createElement('input', { type: 'checkbox', id: 'betterpayment_dd_mandate_agreement', checked: betterpayment_dd_mandate_agreement, onChange: (event) => { setMandateAgreement(event.target.checked); } }),
                React.createElement('label', { htmlFor: 'betterpayment_dd_mandate_agreement' }, betterpayment_dd_i10n.label_mandate_agreement)
            )
        ),
        (sepaDirectDebitIsGenderCollected || sepaDirectDebitIsDateOfBirthCollected || sepaDirectDebitIsRiskCheckAgreementRequired) &&
        React.createElement('h4', null, betterpayment_dd_i10n.label_risk_check_information),
        sepaDirectDebitIsGenderCollected && React.createElement('div', null,
            React.createElement('label', { htmlFor: 'betterpayment_dd_gender' }, betterpayment_dd_i10n.label_gender),
            React.createElement('br'),
            React.createElement(
                'select',
                { id: 'betterpayment_dd_gender', value: betterpayment_dd_gender, onChange: (event) => { setGender(event.target.value); }},
                React.createElement('option', { value: '' }, betterpayment_dd_i10n.option_select),
                React.createElement('option', { value: 'm' }, betterpayment_dd_i10n.option_male),
                React.createElement('option', { value: 'f' }, betterpayment_dd_i10n.option_female),
                React.createElement('option', { value: 'd' }, betterpayment_dd_i10n.option_diverse),
            )
        ),

        sepaDirectDebitIsDateOfBirthCollected && React.createElement('div', null,
            React.createElement('label', { htmlFor: 'betterpayment_dd_date_of_birth' }, betterpayment_dd_i10n.label_date_of_birth),
            React.createElement('br'),
            React.createElement(
                'input',
                { type: 'date', id: 'betterpayment_dd_date_of_birth', value: betterpayment_dd_date_of_birth, onChange: (event) => { setDateOfBirth(event.target.value); }},
            )
        ),

        sepaDirectDebitIsRiskCheckAgreementRequired && React.createElement('div', null,
            React.createElement('input', { type: 'checkbox', id: 'betterpayment_dd_risk_check_agreement', checked: betterpayment_dd_risk_check_agreement, onChange: (event) => { setRiskCheckAgreement(event.target.checked); }}),
            React.createElement('label', { htmlFor: 'betterpayment_dd_risk_check_agreement' }, betterpayment_dd_i10n.label_risk_check_agreement)
        )
    );
};

window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: 'betterpayment_dd',
    label: sepaDirectDebitLabel,
    content: Object( window.wp.element.createElement )( sepaDirectDebitContent, null ),
    edit: Object( window.wp.element.createElement )( sepaDirectDebitContent, null ),
    canMakePayment: () => true,
    ariaLabel: sepaDirectDebitLabel,
});
