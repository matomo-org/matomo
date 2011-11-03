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
		var ajaxRequest = 
		{
			type: 'GET',
			url: 'index.php',
			dataType: 'html',
			error: piwikHelper.ajaxHandleError,		
			success: function (response) {
				piwikHelper.hideAjaxLoading('ajaxLoadingSEO');
				$('#SeoRanks').html(response);
			},
			data: { 
				module: 'SEO',
				action :'getRank',
				url: encodeURIComponent( $('#seoUrl').val() ), 
				idSite: piwik.idSite
			}
		};
		piwikHelper.queueAjaxRequest( $.ajax( ajaxRequest ) );
	}  
	
	// click on Rank button
	$('#rankbutton').on('click', function() {
		getRank();
		return false ;
	});

});
