/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// TODO when UI stabilized, factorize ajax boiler plate accros MobileMessagingSettings javascript functions
var MobileMessagingSettings = MobileMessagingSettings || (function () {

    /************************************************************
     * Private data
     ************************************************************/
    var
        delegatedManagementSelector = 'input[name=delegatedManagement]',
        apiAccountSubmitSelector = '#apiAccountSubmit',
        addPhoneNumberSubmitSelector = '#addPhoneNumberSubmit',
        providersSelector = '#smsProviders',
        providerDescriptionsSelector = '.providerDescription',
        apiKeySelector = '#apiKey',
        countriesSelector = '#countries',
        countryCallingCodeSelector = '#countryCallingCode',
        newPhoneNumberSelector = '#newPhoneNumber',
        suspiciousPhoneNumberSelector = '#suspiciousPhoneNumber',
        validatePhoneNumberSubmitSelector = '.validatePhoneNumberSubmit',
        formDescriptionSelector = '.form-description',
        removePhoneNumberSubmitSelector = '.removePhoneNumberSubmit',
        verificationCodeSelector = '.verificationCode',
        phoneNumberSelector = '.phoneNumber',
        deleteAcountSelector = '#deleteAccount',
        confirmDeleteAccountSelector = '#confirmDeleteAccount',
        accountFormSelector = '#accountForm',
        displayAccountFormSelector = '#displayAccountForm',
        phoneNumberActivatedSelector = '#phoneNumberActivated',
        invalidActivationCodeMsgSelector = '#invalidActivationCode',
        ajaxErrorsSelector = '#ajaxErrorMobileMessagingSettings',
        invalidVerificationCodeAjaxErrorSelector = '#invalidVerificationCodeAjaxError',
        ajaxLoadingSelector = '#ajaxLoadingMobileMessagingSettings';

    /************************************************************
     * Private methods
     ************************************************************/

    function initUIEvents() {

        $(delegatedManagementSelector).change(updateDelegatedManagement);
        $(apiAccountSubmitSelector).click(updateApiAccount);
        $(deleteAcountSelector).click(confirmDeleteApiAccount);
        $(displayAccountFormSelector).click(displayAccountForm);
        $(addPhoneNumberSubmitSelector).click(addPhoneNumber);
        $(newPhoneNumberSelector).keyup(updateSuspiciousPhoneNumberMessage);
        $(validatePhoneNumberSubmitSelector).click(validatePhoneNumber);
        $(removePhoneNumberSubmitSelector).click(removePhoneNumber);
        $(countryCallingCodeSelector).keyup(updateCountry);
        $(countriesSelector).change(updateCountryCallingCode);
        updateCountryCallingCode();
        $(providersSelector).change(updateProviderDescription);
        updateProviderDescription();
    }

    function updateCountry() {
        var countryCallingCode = $(countryCallingCodeSelector).val();
        if (countryCallingCode != null && countryCallingCode != '') {
            var countryToSelect = $(countriesSelector + ' option[value=' + countryCallingCode + ']');
            if (countryToSelect.size() > 0) {
                countryToSelect.attr('selected', 'selected');
            }
            else {
                $(countriesSelector + ' option:selected').removeAttr('selected');
            }
        }
    }

    function displayAccountForm() {
        $(accountFormSelector).show();
    }

    function validatePhoneNumber(event) {
        var phoneNumberContainer = $(event.target).parent();
        var verificationCodeContainer = phoneNumberContainer.find(verificationCodeSelector);
        var verificationCode = verificationCodeContainer.val();
        var phoneNumber = phoneNumberContainer.find(phoneNumberSelector).html();

        if (verificationCode != null && verificationCode != '') {
            var success =
                function (response) {

                    var UI = require('piwik/UI');
                    var notification = new UI.Notification();

                    $(phoneNumberActivatedSelector).hide();
                    if (!response.value) {
                        var message = $(invalidActivationCodeMsgSelector).html();
                        notification.show(message, {
                            context: 'error',
                            id: 'MobileMessaging_ValidatePhoneNumber',
                            style: {marginTop: '10px'}
                        });
                    }
                    else {
                        var message = $(phoneNumberActivatedSelector).html();
                        notification.show(message, {
                            context: 'success',
                            id: 'MobileMessaging_ValidatePhoneNumber',
                            style: {marginTop: '10px'}
                        });

                        $(verificationCodeContainer).remove();
                        $(phoneNumberContainer).find(validatePhoneNumberSubmitSelector).remove();
                        $(phoneNumberContainer).find(formDescriptionSelector).remove();
                    }

                    notification.scrollToNotification();
                };

            var ajaxHandler = new ajaxHelper();
            ajaxHandler.addParams({
                module: 'API',
                format: 'json',
                method: 'MobileMessaging.validatePhoneNumber'
            }, 'GET');
            ajaxHandler.addParams({phoneNumber: phoneNumber, verificationCode: verificationCode}, 'POST');
            ajaxHandler.setCallback(success);
            ajaxHandler.setLoadingElement(ajaxLoadingSelector);
            ajaxHandler.setErrorElement(invalidVerificationCodeAjaxErrorSelector);
            ajaxHandler.send(true);
        }
    }

    function removePhoneNumber(event) {
        var phoneNumberContainer = $(event.target).parent();
        var phoneNumber = phoneNumberContainer.find(phoneNumberSelector).html();

        var ajaxHandler = new ajaxHelper();
        ajaxHandler.addParams({
            module: 'API',
            format: 'json',
            method: 'MobileMessaging.removePhoneNumber'
        }, 'GET');
        ajaxHandler.addParams({phoneNumber: phoneNumber}, 'POST');
        ajaxHandler.redirectOnSuccess();
        ajaxHandler.setLoadingElement(ajaxLoadingSelector);
        ajaxHandler.setErrorElement(ajaxErrorsSelector);
        ajaxHandler.send(true);
    }

    function updateSuspiciousPhoneNumberMessage() {
        var newPhoneNumber = $(newPhoneNumberSelector).val();

        // check if number starts with 0
        if ($.trim(newPhoneNumber).lastIndexOf('0', 0) === 0) {
            $(suspiciousPhoneNumberSelector).show();
        }
        else {
            $(suspiciousPhoneNumberSelector).hide();
        }
    }

    function addPhoneNumber() {
        var newPhoneNumber = $(newPhoneNumberSelector).val();
        var countryCallingCode = $(countryCallingCodeSelector).val();

        var phoneNumber = '+' + countryCallingCode + newPhoneNumber;

        if (newPhoneNumber != null && newPhoneNumber != '') {
            var ajaxHandler = new ajaxHelper();
            ajaxHandler.addParams({
                module: 'API',
                format: 'json',
                method: 'MobileMessaging.addPhoneNumber'
            }, 'GET');
            ajaxHandler.addParams({phoneNumber: phoneNumber}, 'POST');
            ajaxHandler.redirectOnSuccess();
            ajaxHandler.setLoadingElement(ajaxLoadingSelector);
            ajaxHandler.setErrorElement(ajaxErrorsSelector);
            ajaxHandler.send(true);
        }
    }

    function updateCountryCallingCode() {
        $(countryCallingCodeSelector).val($(countriesSelector + ' option:selected').val());
    }

    function updateProviderDescription() {
        $(providerDescriptionsSelector).hide();
        $('#' + $(providersSelector + ' option:selected').val() + providerDescriptionsSelector).show();
    }

    function updateDelegatedManagement() {
        setDelegatedManagement(getDelegatedManagement());
    }

    function confirmDeleteApiAccount() {
        piwikHelper.modalConfirm(confirmDeleteAccountSelector, {yes: deleteApiAccount});
    }

    function deleteApiAccount() {
        var ajaxHandler = new ajaxHelper();
        ajaxHandler.addParams({
            module: 'API',
            format: 'json',
            method: 'MobileMessaging.deleteSMSAPICredential'
        }, 'GET');
        ajaxHandler.redirectOnSuccess();
        ajaxHandler.setLoadingElement(ajaxLoadingSelector);
        ajaxHandler.setErrorElement(ajaxErrorsSelector);
        ajaxHandler.send(true);
    }

    function updateApiAccount() {

        var provider = $(providersSelector + ' option:selected').val();
        var apiKey = $(apiKeySelector).val();

        if (apiKey != '') {
            var ajaxHandler = new ajaxHelper();
            ajaxHandler.addParams({
                module: 'API',
                format: 'json',
                method: 'MobileMessaging.setSMSAPICredential'
            }, 'GET');
            ajaxHandler.addParams({provider: provider, apiKey: apiKey}, 'POST');
            ajaxHandler.redirectOnSuccess();
            ajaxHandler.setLoadingElement(ajaxLoadingSelector);
            ajaxHandler.setErrorElement(ajaxErrorsSelector);
            ajaxHandler.send(true);
        }
    }

    function setDelegatedManagement(delegatedManagement) {
        var ajaxHandler = new ajaxHelper();
        ajaxHandler.addParams({
            module: 'API',
            format: 'json',
            method: 'MobileMessaging.setDelegatedManagement'
        }, 'GET');
        ajaxHandler.addParams({delegatedManagement: delegatedManagement}, 'POST');
        ajaxHandler.redirectOnSuccess();
        ajaxHandler.setLoadingElement(ajaxLoadingSelector);
        ajaxHandler.setErrorElement(ajaxErrorsSelector);
        ajaxHandler.send(true);
    }

    function getDelegatedManagement() {
        return $(delegatedManagementSelector + ':checked').val();
    }

    /************************************************************
     * Public data and methods
     ************************************************************/

    return {

        /**
         * Initialize UI events
         */
        initUIEvents: function () {
            initUIEvents();
        }
    };

}());

$(document).ready(function () {
    MobileMessagingSettings.initUIEvents();
});
