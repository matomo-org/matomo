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
					url: encodeURIComponent( $('#url').val() ), 
					idSite: piwik.idSite
				}
		};
		$.ajax( ajaxRequest );
	}  
	
	// click on Rank button
	$('#rankbutton').bind('click', function() {
		getRank();
		return false ;
	});
});