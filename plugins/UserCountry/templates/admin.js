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

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.setLoadingElement(loading);
        ajaxRequest.addParams({
            module: 'UserCountry',
            action: 'setCurrentLocationProvider',
            id:     $(this).val()
        }, 'get');
        ajaxRequest.setCallback(
            function () {
                ajaxSuccess.fadeIn(1000, function() {
                    setTimeout(function() {
                        ajaxSuccess.fadeOut(1000);
                    }, 2000);
                });
            }
        );
        ajaxRequest.send(false);
	});
	
	// handle 'refresh location' link click
	$('.refresh-loc').click(function(e) {
		e.preventDefault();
		
		var cell = $(this).parent().parent(),
			loading = $('.loadingPiwik', cell),
			location = $('.location', cell);
		
		location.css('visibility', 'hidden');

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.setLoadingElement(loading);
        ajaxRequest.addParams({
            module: 'UserCountry',
            action: 'getLocationUsingProvider',
            id:     $(this).attr('data-impl-id')
        }, 'get');
        ajaxRequest.setCallback(
            function (response) {
                location.html('<strong><em>' + response + '</em></strong>').css('visibility', 'visible');
            }
        );
        ajaxRequest.setFormat('html');
        ajaxRequest.send(false);

		return false;
	});
});
