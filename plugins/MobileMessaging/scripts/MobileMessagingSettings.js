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
		usernameSelector = '#username',
		passwordSelector = '#password',
		countriesSelector = '#countries',
		countryCallingCodeAttribute = 'calling-code',
		countryCallingCodeSelector = '#countryCallingCode',
		newPhoneNumberSelector = '#newPhoneNumber',
		suspiciousPhoneNumberSelector = '#suspiciousPhoneNumber',
		validatePhoneNumberSubmitSelector = '.validatePhoneNumberSubmit',
		removePhoneNumberSubmitSelector = '.removePhoneNumberSubmit',
		verificationCodeSelector = '.verificationCode',
		phoneNumberSelector = '.phoneNumber',
		deleteAcountSelector = '#deleteAccount',
		ajaxErrorsSelector = 'ajaxErrorMobileMessagingSettings',
		ajaxLoadingSelector = 'ajaxLoadingMobileMessagingSettings';

	/************************************************************
	 * Private methods
	 ************************************************************/

	function initUIEvents() {

		$(delegatedManagementSelector).change(updateDelegatedManagement);
		$(apiAccountSubmitSelector).click(updateApiAccount);
		$(deleteAcountSelector).click(deleteApiAccount);
		$(addPhoneNumberSubmitSelector).click(addPhoneNumber);
		$(newPhoneNumberSelector).keyup(updateSuspiciousPhoneNumberMessage);
		$(validatePhoneNumberSubmitSelector).click(validatePhoneNumber);
		$(removePhoneNumberSubmitSelector).click(removePhoneNumber);

		var defaultCountry = getSelectedCountry();
		$(countriesSelector).selectToAutocomplete();
		// previous function seems to reset the pre-selected option
		defaultCountry.attr('selected', 'selected');

		$(countriesSelector).change(updateCountryCallingCode);
		updateCountryCallingCode();
	}

	function validatePhoneNumber(event)
	{
		var phoneNumberContainer = $(event.target).parent();
		var verificationCode = phoneNumberContainer.find(verificationCodeSelector).val();
		var phoneNumber = phoneNumberContainer.find(phoneNumberSelector).html();

		if(verificationCode != null && verificationCode != '')
		{
			var ajaxRequest = piwikHelper.getStandardAjaxConf(ajaxLoadingSelector, ajaxErrorsSelector);

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

		if(phoneNumber != null && phoneNumber != '')
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

	function getSelectedCountry()
	{
		return $(countriesSelector + ' option:selected');
	}

	function updateCountryCallingCode()
	{
		$(countryCallingCodeSelector).val(
				getSelectedCountry().attr(countryCallingCodeAttribute)
		);
	}

	function updateDelegatedManagement() {
		setDelegatedManagement(getDelegatedManagement());
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
		var username = $(usernameSelector).val();
		var password = $(passwordSelector).val();

		if(username != '' && password != '') {

			var ajaxRequest = piwikHelper.getStandardAjaxConf(ajaxLoadingSelector, ajaxErrorsSelector);

			var parameters = {};
			ajaxRequest.data = parameters;

			parameters.module = 'API';
			parameters.format = 'json';
			parameters.method =  'MobileMessaging.setSMSAPICredential';
			parameters.provider =  provider;
			parameters.username =  username;
			parameters.password =  password;
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
