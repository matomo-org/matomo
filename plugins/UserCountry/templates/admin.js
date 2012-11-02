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

        piwikHelper.ajaxCall(
            'UserCountry',
            'setCurrentLocationProvider',
            {id: $(this).val()},
            function() {
                loading.hide();
                ajaxSuccess.fadeIn(1000, function() {
                    setTimeout(function() {
                        ajaxSuccess.fadeOut(1000);
                    }, 2000);
                });
            }
        );
	});
	
	// handle 'refresh location' link click
	$('.refresh-loc').click(function(e) {
		e.preventDefault();
		
		var cell = $(this).parent().parent(),
			loading = $('.loadingPiwik', cell),
			location = $('.location', cell);
		
		location.css('visibility', 'hidden');
		loading.show();

        piwikHelper.ajaxCall(
            'UserCountry',
            'getLocationUsingProvider',
            {id: $(this).attr('data-impl-id')},
            function(response) {
                loading.hide();
                location.html('<strong><em>' + response + '</em></strong>').css('visibility', 'visible');
            },
            'html'
        );

		return false;
	});
});
