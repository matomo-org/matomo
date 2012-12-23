/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function($) {

$(document).ready(function() {
	
	var headerMessageParent = $('#header_message').parent();
	
	// when 'check for updates...' link is clicked, force a check & display the result
	headerMessageParent.on('click', '#updateCheckLinkContainer', function(e) {
		e.preventDefault();
		
		var headerMessage = $(this).closest('#header_message');
		
		var ajaxRequest = new ajaxHelper();
		ajaxRequest.setLoadingElement('#header_message .loadingPiwik');
		ajaxRequest.addParams({
			module: 'CoreHome',
			action: 'checkForUpdates',
			token_auth: piwik.token_auth
		}, 'get');
		ajaxRequest.setCallback(function(response) {
			headerMessage.fadeOut('slow', function() {
				headerMessage.html(_pk_translate('CoreHome_YouAreUsingTheLatestVersion_js')).show();
				setTimeout(function() {
					headerMessage.fadeOut('slow', function() {
						headerMessage.replaceWith(response);
					});
				}, 4000);
			});
		});
		ajaxRequest.setFormat('html');
		ajaxRequest.send(false);
		
		return false;
	});
	
	// when clicking the header message, show the long message w/o needing to hover
	headerMessageParent.on('click', '#header_message', function(e) {
		if (e.target.tagName.toLowerCase() != 'a')
		{
			$(this).toggleClass('active');
		}
	});
	
});

}(jQuery));
