define(
    [
        'underscore',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
        'mage/translate'
    ],
    function (_, Component, creditCardData, cardNumberValidator, $t) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Ime_CcSave/payment/ccsave',
                creditCardName: '',
                creditCardType: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardNumber: '',
                creditCardSsStartMonth: '',
                creditCardSsStartYear: '',
                creditCardVerificationNumber: '',
                selectedCardType: null
            },
            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardName',
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardVerificationNumber',
                        'creditCardSsStartMonth',
                        'creditCardSsStartYear',
                        'selectedCardType'
                    ]);
                return this;
            },

            initialize: function() {
                var self = this;
                this._super();

                //Set credit card number to credit card data object
                this.creditCardNumber.subscribe(function(value) {
                    var result;
                    self.selectedCardType(null);

                    if (value == '' || value == null) {
                        return false;
                    }
                    result = cardNumberValidator(value);

                    if (!result.isPotentiallyValid && !result.isValid) {
                        return false;
                    }
                    if (result.card !== null) {
                        self.selectedCardType(result.card.type);
                        creditCardData.creditCard = result.card;
                    }

                    if (result.isValid) {
                        creditCardData.creditCardNumber = value;
                        self.creditCardType(result.card.type);
                    }
                });

                //Set expiration year to credit card data object
                this.creditCardExpYear.subscribe(function(value) {
                    creditCardData.expirationYear = value;
                });

                //Set expiration month to credit card data object
                this.creditCardExpMonth.subscribe(function(value) {
                    creditCardData.expirationYear = value;
                });

                //Set cvv code to credit card data object
                this.creditCardVerificationNumber.subscribe(function(value) {
                    creditCardData.cvvCode = value;
                });
            },

            isActive: function () {
                return true;
            },

            getCcAvailableTypes: function() {
                return window.checkoutConfig.payment.ccsave.availableTypes['ccsave'];
            },

            getCcMonths: function() {
                return window.checkoutConfig.payment.ccsave.months['ccsave'];
            },

            getCcYears: function() {
                return window.checkoutConfig.payment.ccsave.years['ccsave'];
            },

            hasVerification: function() {
                return window.checkoutConfig.payment.ccsave.hasVerification['ccsave'];
            },

            getCcAvailableTypesValues: function() {
                return _.map(this.getCcAvailableTypes(), function(value, key) {
                    return {
                        'value': key,
                        'type': value
                    }
                });
            },
            getCcMonthsValues: function() {
                return _.map(this.getCcMonths(), function(value, key) {
                    return {
                        'value': key,
                        'month': value
                    }
                });
            },
            getCcYearsValues: function() {
                return _.map(this.getCcYears(), function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },

            getData: function () {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'cc_name': this.creditCardName(),
                        'cc_number': this.creditCardNumber(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_type': this.creditCardType(),
                        'cc_cvv': this.creditCardVerificationNumber()
                    }
                };
            }

        });
    }
);