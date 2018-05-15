/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ManageMobilePhoneNumbersController', ManageMobilePhoneNumbersController);

    ManageMobilePhoneNumbersController.$inject = ['piwikApi', 'piwik'];

    function ManageMobilePhoneNumbersController(piwikApi, piwikk) {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var self = this;
        this.isAddingPhonenumber = false;
        this.canAddNumber = false;
        this.isActivated = {};

        this.validateActivationCode = function(phoneNumber, index) {
            if (!this.validationCode || !this.validationCode[index] || this.validationCode[index] == '') {
                return;
            }

            var verificationCode = this.validationCode[index];

            var success = function (response) {

                self.isChangingPhoneNumber = false;

                var UI = require('piwik/UI');
                var notification = new UI.Notification();

                if (!response || !response.value) {
                    var message = _pk_translate('MobileMessaging_Settings_InvalidActivationCode');
                    notification.show(message, {
                        context: 'error',
                        id: 'MobileMessaging_ValidatePhoneNumber'
                    });
                }
                else {
                    var message = _pk_translate('MobileMessaging_Settings_PhoneActivated')
                    notification.show(message, {
                        context: 'success',
                        id: 'MobileMessaging_ValidatePhoneNumber'
                    });

                    self.isActivated[index] = true;
                }

                notification.scrollToNotification();
            };

            this.isChangingPhoneNumber = true;

            piwikApi.post(
                {method: 'MobileMessaging.validatePhoneNumber'},
                {phoneNumber: phoneNumber, verificationCode: verificationCode},
                {placeat: '#invalidVerificationCodeAjaxError'}
            ).then(success, function () {
                self.isChangingPhoneNumber = false;
            });
            
        }

        this.removePhoneNumber = function (phoneNumber) {
            if (!phoneNumber) {
                return;
            }

            this.isChangingPhoneNumber = true;

            piwikApi.post(
                {method: 'MobileMessaging.removePhoneNumber'},
                {phoneNumber: phoneNumber},
                {placeat: '#invalidVerificationCodeAjaxError'}
            ).then(function () {
                self.isChangingPhoneNumber = false;
                piwik.helper.redirect();
            }, function () {
                self.isChangingPhoneNumber = false;
            });
        }

        this.validateNewPhoneNumberFormat = function () {
            this.showSuspiciousPhoneNumber = $.trim(this.newPhoneNumber).lastIndexOf('0', 0) === 0;
            this.canAddNumber = !!this.newPhoneNumber && this.newPhoneNumber != '';
        };

        this.addPhoneNumber = function() {
            var phoneNumber = '+' + this.countryCallingCode + this.newPhoneNumber;

            if (this.canAddNumber && phoneNumber.length > 1) {
                this.isAddingPhonenumber = true;

                piwikApi.post(
                    {method: 'MobileMessaging.addPhoneNumber'},
                    {phoneNumber: phoneNumber},
                    {placeat: '#ajaxErrorAddPhoneNumber'}
                ).then(function () {
                    self.isAddingPhonenumber = false;
                    piwik.helper.redirect();
                }, function () {
                    self.isAddingPhonenumber = false;
                });
            }
        }

    }
})();