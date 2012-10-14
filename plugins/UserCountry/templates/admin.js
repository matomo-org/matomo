/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function() {
	// handle switch current location provider
	$('.current-location-provider').change(function() {
		if (!$(this).is(':checked')) return; // only handle radio buttons that get checked
		
		var parent = $(this).parent(),
			loading = $('.loadingPiwik', parent),
			ajaxSuccess = $('.ajaxSuccess', parent);
		loading.show();
		
		$.ajax({
			type: 'POST',
			url: 'index.php',
			data: {
				module: 'UserCountry',
				action: 'setCurrentLocationProvider',
				token_auth: piwik.token_auth,
				id: $(this).val()
			},
			async: true,
			error: piwikHelper.ajaxHandleError,		// Callback when the request fails
			success: function() {
				loading.hide();
				ajaxSuccess.fadeIn(1000, function() {
					setTimeout(function() {
						ajaxSuccess.fadeOut(1000);
					}, 2000);
				});
			}
		});
	});
	
	// handle 'refresh location' link click
	$('.refresh-loc').click(function(e) {
		e.preventDefault();
		
		var cell = $(this).parent().parent(),
			loading = $('.loadingPiwik', cell),
			location = $('.location', cell);
		
		location.css('visibility', 'hidden');
		loading.show();
		
		$.ajax({
			type: 'GET',
			url: 'index.php',
			data: {
				module: 'UserCountry',
				action: 'getLocationUsingProvider',
				id: $(this).attr('data-impl-id')
			},
			async: true,
			error: piwikHelper.ajaxHandleError,		// Callback when the request fails
			success: function(response) {
				loading.hide();
				location.html('<strong><em>' + response + '</em></strong>').css('visibility', 'visible');
			}
		});
		
		return false;
	});
});
