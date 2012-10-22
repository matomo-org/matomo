/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function getGeneralSettingsAJAX()
{
	var ajaxRequest = piwikHelper.getStandardAjaxConf('ajaxLoading', 'ajaxError');
	var enableBrowserTriggerArchiving = $('input[name=enableBrowserTriggerArchiving]:checked').val();
	var todayArchiveTimeToLive = $('#todayArchiveTimeToLive').val();
	var request = '';
	request += 'module=CoreAdminHome';
	request += '&action=setGeneralSettings';
	request += '&format=json';
	request += '&enableBrowserTriggerArchiving='+enableBrowserTriggerArchiving;
	request += '&todayArchiveTimeToLive='+todayArchiveTimeToLive;
 	request += '&token_auth=' + piwik.token_auth;
 	request += '&mailUseSmtp=' + isSmtpEnabled();
 	request += '&mailPort=' + encodeURIComponent($('#mailPort').val());
 	request += '&mailHost=' + encodeURIComponent($('#mailHost').val());
 	request += '&mailType=' + $('#mailType').val();
 	request += '&mailUsername=' + encodeURIComponent($('#mailUsername').val());
 	request += '&mailPassword=' + encodeURIComponent($('#mailPassword').val());
	request += '&mailEncryption=' + $('#mailEncryption').val();
	request += '&useCustomLogo=' + isCustomLogoEnabled();
	
	var trustedHosts = [];
	$('input[name=trusted_host]').each(function () {
		trustedHosts.push($(this).val());
	});
	request += '&trustedHosts=' + encodeURIComponent(JSON.stringify(trustedHosts));
	ajaxRequest.data = request;
	return ajaxRequest;
}
function showSmtpSettings(value)
{
	$('#smtpSettings').toggle(value==1);
}
function isSmtpEnabled()
{
	return $('input[name="mailUseSmtp"]:checked').val();
}
function showCustomLogoSettings(value)
{
	$('#logoSettings').toggle(value==1);
}
function isCustomLogoEnabled()
{
	return $('input[name="useCustomLogo"]:checked').val();
}

function refreshCustomLogo() {
	var imageDiv = $("#currentLogo");
	if(imageDiv && imageDiv.attr("src")) {
		var logoUrl = imageDiv.attr("src").split("?")[0];
		imageDiv.attr("src", logoUrl+"?"+ (new Date()).getTime());
	}
}

$(document).ready( function() {
	var originalTrustedHostCount = $('input[name=trusted_host]').length;
	
	showSmtpSettings(isSmtpEnabled());
	showCustomLogoSettings(isCustomLogoEnabled());
	$('#generalSettingsSubmit').click( function() {
		var doSubmit = function()
		{
			$.ajax( getGeneralSettingsAJAX() );
		};
		
		var hasTrustedHostsChanged = false,
			hosts = $('input[name=trusted_host]');
		if (hosts.length != originalTrustedHostCount)
		{
			hasTrustedHostsChanged = true;
		}
		else
		{
			hosts.each(function() {
				hasTrustedHostsChanged |= this.defaultValue != this.value;
			});
		}
		
		// if trusted hosts have changed, make sure to ask for confirmation
		if (hasTrustedHostsChanged)
		{
			piwikHelper.modalConfirm('#confirmTrustedHostChange', {yes: doSubmit});
		}
		else
		{
			doSubmit();
		}
	});

	$('input[name=mailUseSmtp]').click(function(){
		 showSmtpSettings($(this).val());
	});
	$('input[name=useCustomLogo]').click(function(){
		refreshCustomLogo();
		showCustomLogoSettings($(this).val());
	});
	$('input').keypress( function(e) {
			var key=e.keyCode || e.which;
			if (key==13) {
				$('#generalSettingsSubmit').click();
			}
		}
	);
	
	$("#logoUploadForm").submit( function(data) {
		var submittingForm = $( this );
		var frameName = "upload"+(new Date()).getTime();
		var uploadFrame = $("<iframe name=\""+frameName+"\" />");
		uploadFrame.css("display", "none");
		uploadFrame.load(function(data){
		setTimeout(function(){
			refreshCustomLogo();
			uploadFrame.remove();},1000);
		});
		$("body:first").append(uploadFrame);
		submittingForm.attr("target", frameName);
	});
	
	$('#customLogo').change(function(){$("#logoUploadForm").submit()});
	
	// trusted hosts event handling
	$('#trustedHostSettings .adminTable').on('click', '.remove-trusted-host', function(e) {
		e.preventDefault();
		$(this).parent().parent().remove();
		return false;
	});
	$('#trustedHostSettings .add-trusted-host').click(function(e) {
		e.preventDefault();
		
		// append new row to the table
		$('#trustedHostSettings tbody').append('<tr>'
		  + '<td><input name="trusted_host" type="text" value=""/></td>'
		  + '<td><a href="#" class="remove-trusted-host">x</a></td>'
		  + '</tr>');
		return false;
	});
});
