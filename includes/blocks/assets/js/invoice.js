const invoiceData = window.wc.wcSettings.getPaymentMethodData('betterpayment_kar');

const invoiceLabel = invoiceData.title;

const invoiceIsGenderCollected = invoiceData.isGenderCollected;
const invoiceIsDateOfBirthCollected = invoiceData.isDateOfBirthCollected;
const invoiceIsRiskCheckAgreementRequired = invoiceData.isRiskCheckAgreementRequired;

const invoiceContent = (props) => {
    const { eventRegistration, emitResponse } = props;
    const { onPaymentProcessing } = eventRegistration;

    const [betterpayment_kar_gender, setGender] = React.useState('');
    const [betterpayment_kar_date_of_birth, setDateOfBirth] = React.useState('');
    const [betterpayment_kar_risk_check_agreement, setRiskCheckAgreement] = React.useState(false);

    React.useEffect(() => {
        const unsubscribe = onPaymentProcessing(async () => {
            const paymentMethodData = {};

            if (invoiceIsGenderCollected) {
                paymentMethodData.betterpayment_kar_gender = betterpayment_kar_gender;
            }

            if (invoiceIsDateOfBirthCollected) {
                paymentMethodData.betterpayment_kar_date_of_birth = betterpayment_kar_date_of_birth;
            }

            if (invoiceIsRiskCheckAgreementRequired) {
                paymentMethodData.betterpayment_kar_risk_check_agreement = betterpayment_kar_risk_check_agreement;
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
        betterpayment_kar_gender,
        betterpayment_kar_date_of_birth,
        betterpayment_kar_risk_check_agreement,
        invoiceIsGenderCollected,
        invoiceIsDateOfBirthCollected,
        invoiceIsRiskCheckAgreementRequired
    ] );

    return (invoiceIsGenderCollected || invoiceIsDateOfBirthCollected || invoiceIsRiskCheckAgreementRequired) &&
        React.createElement('div', null,
            React.createElement('h4', null, betterpayment_kar_l10n.label_risk_check_information),

            invoiceIsDateOfBirthCollected && React.createElement('div', null,
                React.createElement('label', {htmlFor: 'betterpayment_kar_date_of_birth'}, betterpayment_kar_l10n.label_date_of_birth),
                React.createElement('br'),
                React.createElement(
                    'input',
                    {type: 'date', id: 'betterpayment_kar_date_of_birth', value: betterpayment_kar_date_of_birth, onChange: (event) => { setDateOfBirth(event.target.value); } },
                )
            ),

            invoiceIsGenderCollected && React.createElement('div', null,
                React.createElement('label', {htmlFor: 'betterpayment_kar_gender'}, betterpayment_kar_l10n.label_gender),
                React.createElement('br'),
                React.createElement(
                    'select',
                    {id: 'betterpayment_kar_gender', value: betterpayment_kar_gender, onChange: (event) => { setGender(event.target.value); } },
                    React.createElement('option', {value: ''}, betterpayment_kar_l10n.option_select),
                    React.createElement('option', {value: 'm'}, betterpayment_kar_l10n.option_male),
                    React.createElement('option', {value: 'f'}, betterpayment_kar_l10n.option_female),
                    React.createElement('option', {value: 'd'}, betterpayment_kar_l10n.option_diverse),
                )
            ),

            invoiceIsRiskCheckAgreementRequired && React.createElement('div', null,
                React.createElement('input', { type: 'checkbox', id: 'betterpayment_kar_risk_check_agreement', value: betterpayment_kar_risk_check_agreement, onChange: (event) => { setRiskCheckAgreement(event.target.checked); } } ),
                React.createElement('label', { htmlFor: 'betterpayment_kar_risk_check_agreement' }, betterpayment_kar_l10n.label_risk_check_agreement)
            )
        );
};

window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: 'betterpayment_kar',
    label: invoiceLabel,
    content: Object( window.wp.element.createElement )( invoiceContent, null ),
    edit: Object( window.wp.element.createElement )( invoiceContent, null ),
    canMakePayment: () => true,
    ariaLabel: invoiceLabel,
});
