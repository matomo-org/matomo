/**
 * Piwik - Open source web analytics
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
		ajaxErrorsSelector = 'ajaxErrorMobileMessagingSettings',
		invalidVerificationCodeAjaxErrorSelector = 'invalidVerificationCodeAjaxError',
		ajaxLoadingSelector = 'ajaxLoadingMobileMessagingSettings';

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

	function updateCountry()
	{
		var countryCallingCode = $(countryCallingCodeSelector).val();
		if(countryCallingCode != null && countryCallingCode != '')
		{
			var countryToSelect = $(countriesSelector + ' option[value='+countryCallingCode+']');
			if(countryToSelect.size() > 0)
			{
				countryToSelect.attr('selected', 'selected');
			}
			else
			{
				$(countriesSelector + ' option:selected').removeAttr('selected');
			}
		}
	}

	function displayAccountForm()
	{
		$(accountFormSelector).show();
	}

	function validatePhoneNumber(event)
	{
		var phoneNumberContainer = $(event.target).parent();
		var verificationCodeContainer = phoneNumberContainer.find(verificationCodeSelector);
		var verificationCode = verificationCodeContainer.val();
		var phoneNumber = phoneNumberContainer.find(phoneNumberSelector).html();

		if(verificationCode != null && verificationCode != '')
		{
			var ajaxRequest = piwikHelper.getStandardAjaxConf(ajaxLoadingSelector, invalidVerificationCodeAjaxErrorSelector);

			ajaxRequest.success =
				function(response)
				{
					piwikHelper.hideAjaxLoading(ajaxLoadingSelector);
					$(phoneNumberActivatedSelector).hide();
					if(!response.value)
					{
						piwikHelper.showAjaxError($(invalidActivationCodeMsgSelector).html(), invalidVerificationCodeAjaxErrorSelector);
					}
					else
					{
						piwikHelper.hideAjaxError(invalidVerificationCodeAjaxErrorSelector);
						$(phoneNumberActivatedSelector).show();
						$(verificationCodeContainer).remove();
						$(phoneNumberContainer).find(validatePhoneNumberSubmitSelector).remove();
						$(phoneNumberContainer).find(formDescriptionSelector).remove();
					}
				};

			var parameters = {};
			ajaxRequest.data = parameters;

			parameters.module = 'API';
			parameters.format = 'json';
			parameters.method =  'MobileMessaging.validatePhoneNumber';
			parameters.phoneNumber =  phoneNumber;
			parameters.verificationCode =  verificationCode;
			parameters.token_auth = piwik.token_auth;

			$.ajax(ajaxRequest);
		}
	}

	function removePhoneNumber(event)
	{
		var phoneNumberContainer = $(event.target).parent();
		var phoneNumber = phoneNumberContainer.find(phoneNumberSelector).html();

		var ajaxRequest = piwikHelper.getStandardAjaxConf(ajaxLoadingSelector, ajaxErrorsSelector);

		var parameters = {};
		ajaxRequest.data = parameters;

		parameters.module = 'API';
		parameters.format = 'json';
		parameters.method =  'MobileMessaging.removePhoneNumber';
		parameters.phoneNumber =  phoneNumber;
		parameters.token_auth = piwik.token_auth;

		$.ajax(ajaxRequest);
	}

	function updateSuspiciousPhoneNumberMessage()
	{
		var newPhoneNumber = $(newPhoneNumberSelector).val();

		// check if number starts with 0
		if(newPhoneNumber.lastIndexOf('0', 0) === 0)
		{
			$(suspiciousPhoneNumberSelector).show();
		}
		else
		{
			$(suspiciousPhoneNumberSelector).hide();
		}
	}

	function addPhoneNumber()
	{
		var newPhoneNumber = $(newPhoneNumberSelector).val();
		var countryCallingCode = $(countryCallingCodeSelector).val();

		var phoneNumber = '+' + countryCallingCode + newPhoneNumber;

		if(newPhoneNumber != null && newPhoneNumber != '')
		{
			var ajaxRequest = piwikHelper.getStandardAjaxConf(ajaxLoadingSelector, ajaxErrorsSelector);

			var parameters = {};
			ajaxRequest.data = parameters;

			parameters.module = 'API';
			parameters.format = 'json';
			parameters.method =  'MobileMessaging.addPhoneNumber';
			parameters.phoneNumber =  phoneNumber;
			parameters.token_auth = piwik.token_auth;

			$.ajax(ajaxRequest);
		}
	}

	function updateCountryCallingCode()
	{
		$(countryCallingCodeSelector).val($(countriesSelector + ' option:selected').val());
	}

	function updateProviderDescription()
	{
		$(providerDescriptionsSelector).hide();
		$('#' + $(providersSelector + ' option:selected').val() + providerDescriptionsSelector).show();
	}

	function updateDelegatedManagement() {
		setDelegatedManagement(getDelegatedManagement());
	}

	function confirmDeleteApiAccount()
	{
		piwikHelper.modalConfirm(confirmDeleteAccountSelector, {yes: deleteApiAccount});
	}
	
	function deleteApiAccount() {

		var ajaxRequest = piwikHelper.getStandardAjaxConf(ajaxLoadingSelector, ajaxErrorsSelector);

		var parameters = {};
		ajaxRequest.data = parameters;

		parameters.module = 'API';
		parameters.format = 'json';
		parameters.method =  'MobileMessaging.deleteSMSAPICredential';
		parameters.token_auth = piwik.token_auth;

		$.ajax(ajaxRequest);
	}

	function updateApiAccount() {

		var provider = $(providersSelector + ' option:selected').val();
		var apiKey = $(apiKeySelector).val();

		if(apiKey != '') {

			var ajaxRequest = piwikHelper.getStandardAjaxConf(ajaxLoadingSelector, ajaxErrorsSelector);

			var parameters = {};
			ajaxRequest.data = parameters;

			parameters.module = 'API';
			parameters.format = 'json';
			parameters.method = 'MobileMessaging.setSMSAPICredential';
			parameters.provider = provider;
			parameters.apiKey = apiKey;
			parameters.token_auth = piwik.token_auth;

			$.ajax(ajaxRequest);
		}
	}

	function setDelegatedManagement(delegatedManagement) {

		var ajaxRequest = piwikHelper.getStandardAjaxConf(ajaxLoadingSelector, ajaxErrorsSelector);

		var parameters = {};
		ajaxRequest.data = parameters;

		parameters.module = 'API';
		parameters.format = 'json';
		parameters.method =  'MobileMessaging.setDelegatedManagement';
		parameters.token_auth = piwik.token_auth;
		parameters.delegatedManagement = delegatedManagement;

		$.ajax(ajaxRequest);
	}

	function getDelegatedManagement() {
		return $(delegatedManagementSelector + ':checked').val();
	}

	/************************************************************
	 * Public data and methods
	 ************************************************************/

	return {

		/*
		 * Initialize UI events
		 */
		initUIEvents: function () {
			initUIEvents();
		}
	};

}());

$(document).ready( function() {
	MobileMessagingSettings.initUIEvents();
});
