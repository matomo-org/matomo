function getGeneralSettingsAJAX()
{
	var ajaxRequest = piwikHelper.getStandardAjaxConf('ajaxLoading', 'ajaxError');
	var enableBrowserTriggerArchiving = $('#enableBrowserTriggerArchiving').val();
	var todayArchiveTimeToLive = $('#todayArchiveTimeToLive').val();
	var request = '';
	request += 'module=CoreAdminHome';
	request += '&action=setGeneralSettings';
	request += '&format=json';
	request += '&enableBrowserTriggerArchiving='+enableBrowserTriggerArchiving;
	request += '&todayArchiveTimeToLive='+todayArchiveTimeToLive;
 	request += '&token_auth=' + piwik.token_auth;
	ajaxRequest.data = request;
	return ajaxRequest;
}

$(document).ready( function() {
	$('#generalSettingsSubmit').click( function() {
		$.ajax( getGeneralSettingsAJAX() );
	});

	$('input').keypress( function(e) {
			var key=e.keyCode || e.which;
			if (key==13) {
				$('#generalSettingsSubmit').click();
			}
		}
	)
});

