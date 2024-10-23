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

    // TODO: add check flags to avoid unnecessary data passing
    React.useEffect( () => {
        const unsubscribe = onPaymentProcessing( async () => {
            return {
                type: emitResponse.responseTypes.SUCCESS,
                meta: {
                    paymentMethodData: {
                        betterpayment_dd_iban,
                        betterpayment_dd_bic,
                        betterpayment_dd_account_holder,
                        betterpayment_dd_mandate_reference,
                        betterpayment_dd_mandate_agreement,

                        betterpayment_dd_gender,
                        betterpayment_dd_date_of_birth,
                        betterpayment_dd_risk_check_agreement,
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

        betterpayment_dd_iban,
        betterpayment_dd_bic,
        betterpayment_dd_account_holder,
        betterpayment_dd_mandate_reference,
        betterpayment_dd_mandate_agreement,

        betterpayment_dd_gender,
        betterpayment_dd_date_of_birth,
        betterpayment_dd_risk_check_agreement,
    ] );

    return React.createElement('div', null,
        React.createElement('div', null,
            React.createElement('label', {htmlFor: 'iban'}, 'IBAN: '),
            React.createElement('input', {type: 'text', id: 'betterpayment_dd_iban', value: betterpayment_dd_iban, onChange: (event) => { setIban(event.target.value); } } ),
            React.createElement('br'),
            React.createElement('label', {htmlFor: 'bic'}, 'BIC (optional): '),
            React.createElement('input', {type: 'text', id: 'betterpayment_dd_bic', value: betterpayment_dd_bic, onChange: (event) => { setBic(event.target.value); } } ),
            React.createElement('br'),
            React.createElement('span', { dangerouslySetInnerHTML: { __html: sepaDirectDebitMandateDescription } } ),
            React.createElement('div', null,
                React.createElement('input', { type: 'checkbox', id: 'betterpayment_dd_mandate_agreement', checked: betterpayment_dd_mandate_agreement, onChange: (event) => { setMandateAgreement(event.target.checked); } }),
                React.createElement('label', { htmlFor: 'mandate_agreement' }, 'I agree to the following mandate')
            )
        ),
        (sepaDirectDebitIsGenderCollected || sepaDirectDebitIsDateOfBirthCollected || sepaDirectDebitIsRiskCheckAgreementRequired) &&
        React.createElement('h4', null, 'Risk check information'),
            sepaDirectDebitIsGenderCollected && React.createElement('div', null,
                React.createElement('label', {htmlFor: 'betterpayment_dd_gender'}, 'Gender: '),
                React.createElement(
                    'select',
                    {id: 'betterpayment_dd_gender', value: betterpayment_dd_gender, onChange: (event) => { setGender(event.target.value); }, className: ''},
                    React.createElement('option', {value: ''}, 'Select...'),
                    React.createElement('option', {value: 'm'}, 'male'),
                    React.createElement('option', {value: 'f'}, 'female'),
                    React.createElement('option', {value: 'd'}, 'diverse'),
                )
            ),

            sepaDirectDebitIsDateOfBirthCollected && React.createElement('div', null,
                React.createElement('label', {htmlFor: 'betterpayment_dd_date_of_birth'}, 'Date of birth: '),
                React.createElement(
                    'input',
                    {type: 'date', id: 'betterpayment_dd_date_of_birth', value: betterpayment_dd_date_of_birth, onChange: (event) => { setDateOfBirth(event.target.value); }, className: ''},
                )
            ),

            sepaDirectDebitIsRiskCheckAgreementRequired && React.createElement('div', null,
                React.createElement('input', { type: 'checkbox', id: 'betterpayment_dd_risk_check_agreement', checked: betterpayment_dd_risk_check_agreement, onChange: (event) => { setRiskCheckAgreement(event.target.checked); }, className: ''}),
                React.createElement('label', { htmlFor: 'agree' }, 'Agree risk check processing')
            )
        )
};

window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: 'betterpayment_dd',
    label: sepaDirectDebitLabel,
    content: Object( window.wp.element.createElement )( sepaDirectDebitContent, null ),
    edit: Object( window.wp.element.createElement )( sepaDirectDebitContent, null ),
    canMakePayment: () => true,
    ariaLabel: sepaDirectDebitLabel,
});
