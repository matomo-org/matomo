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
	showSmtpSettings(isSmtpEnabled());
	showCustomLogoSettings(isCustomLogoEnabled());
	$('#generalSettingsSubmit').click( function() {
		$.ajax( getGeneralSettingsAJAX() );
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
});
