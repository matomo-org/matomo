/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function() {
	function getRank()
	{
		piwikHelper.showAjaxLoading('ajaxLoadingSEO');
        piwikHelper.ajaxCall(
            'SEO',
            'getRank',
            {url:encodeURIComponent($('#seoUrl').val())},
            function (response) {
                piwikHelper.hideAjaxLoading('ajaxLoadingSEO');
                $('#SeoRanks').html(response);
            },
            'html',
            true
        );
	}  
	
	// click on Rank button
	$('#rankbutton').on('click', function() {
		getRank();
		return false ;
	});

});
