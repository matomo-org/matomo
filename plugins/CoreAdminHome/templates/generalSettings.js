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
 	request += '&mailPort=' + $('#mailPort').val();
 	request += '&mailHost=' + $('#mailHost').val();
 	request += '&mailType=' + $('#mailType').val();
 	request += '&mailUsername=' + $('#mailUsername').val();
 	request += '&mailPassword=' + $('#mailPassword').val();
	request += '&mailEncryption=' + $('#mailEncryption').val();
	ajaxRequest.data = request;
	return ajaxRequest;
}
function showSmtpSettings(value)
{
	if(value == 1)
	{
		$('#smtpSettings').show();
	}
	else
	{
		$('#smtpSettings').hide();
	}
}
function isSmtpEnabled()
{
	return $('input[name="mailUseSmtp"]:checked').attr('value');
}

$(document).ready( function() {
	showSmtpSettings(isSmtpEnabled());
	$('#generalSettingsSubmit').click( function() {
		$.ajax( getGeneralSettingsAJAX() );
	});

	$('input[name=mailUseSmtp]').click(function(){
		 showSmtpSettings($(this).attr('value'));
	});
	$('input').keypress( function(e) {
			var key=e.keyCode || e.which;
			if (key==13) {
				$('#generalSettingsSubmit').click();
			}
		}
	)
});
