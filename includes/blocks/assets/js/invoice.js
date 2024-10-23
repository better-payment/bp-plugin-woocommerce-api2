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

    // TODO: add check flags to avoid unnecessary data passing
    React.useEffect( () => {
        const unsubscribe = onPaymentProcessing( async () => {
            return {
                type: emitResponse.responseTypes.SUCCESS,
                meta: {
                    paymentMethodData: {
                        betterpayment_kar_gender,
                        betterpayment_kar_date_of_birth,
                        betterpayment_kar_risk_check_agreement
                    },
                },
            };
        } );

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
    ] );

    return (invoiceIsGenderCollected || invoiceIsDateOfBirthCollected || invoiceIsRiskCheckAgreementRequired)
        && React.createElement('div', null,
            React.createElement('h4', null, 'Risk check information'),
            invoiceIsGenderCollected && React.createElement('div', null,
                React.createElement('label', {htmlFor: 'betterpayment_kar_gender', required: true}, 'Gender: '),
                React.createElement(
                    'select',
                    {id: 'betterpayment_kar_gender', name: 'betterpayment_kar_gender', value: betterpayment_kar_gender, onChange: (event) => { setGender(event.target.value); }, className: ''},
                    React.createElement('option', {value: ''}, 'Select...'),
                    React.createElement('option', {value: 'm'}, 'male'),
                    React.createElement('option', {value: 'f'}, 'female'),
                    React.createElement('option', {value: 'd'}, 'diverse'),
                )
            ),

            invoiceIsDateOfBirthCollected && React.createElement('div', null,
                React.createElement('label', {htmlFor: 'betterpayment_kar_date_of_birth', required: true}, 'Date of birth: '),
                React.createElement(
                    'input',
                    {type: 'date', id: 'betterpayment_kar_date_of_birth', name: 'betterpayment_kar_date_of_birth', value: betterpayment_kar_date_of_birth, onChange: (event) => { setDateOfBirth(event.target.value); }, className: ''},
                )
            ),

            invoiceIsRiskCheckAgreementRequired && React.createElement('div', null,
                React.createElement('input', { type: 'checkbox', id: 'betterpayment_kar_risk_check_agreement', name: 'betterpayment_kar_risk_check_agreement', required: true, value: betterpayment_kar_risk_check_agreement, onChange: (event) => { setRiskCheckAgreement(event.target.checked); }, className: ''}),
                React.createElement('label', { htmlFor: 'agree' }, 'Agree risk check processing')
            )
        )
};

window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: 'betterpayment_kar',
    label: invoiceLabel,
    content: Object( window.wp.element.createElement )( invoiceContent, null ),
    edit: Object( window.wp.element.createElement )( invoiceContent, null ),
    canMakePayment: () => true,
    ariaLabel: invoiceLabel,
});
