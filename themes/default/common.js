function piwikHelper()
{
}
/*
 *  Returns query string for an object of key,values
 *  Note: we don't use $.param from jquery as it doesn't return array values the PHP way (returns a=v1&a=v2 instead of a[]=v1&a[]=v2)
 *  Example:
 *  	piwikHelper.getQueryStringFromParameters({"a":"va","b":["vb","vc"],"c":1})
 *  Returns:
 *  	a=va&b[]=vb&b[]=vc&c=1
 */
piwikHelper.getQueryStringFromParameters = function(parameters)
{
	var queryString = '';
	if(parameters.length==0) {
		return queryString;
	}
	for(var name in parameters) {
		value = parameters[name];
		if(typeof value == 'object') {
			for(var i in value) {
				queryString += name + '[]=' + value[i] + '&';
			}
		} else {
			queryString += name + '=' + value + '&';
		}
	}
	return queryString.substring(0, queryString.length-1);
}

piwikHelper.findSWFGraph = function(name) {
  if (navigator.appName.indexOf("Microsoft")!= -1) {
    return window[name];
  } else {
    return document[name];
  }
}

piwikHelper.redirectToUrl = function(url) {
	window.location = url;
}

piwikHelper.ajaxHandleError = function()
{
	$('#loadingError').show();
	setTimeout( function(){ 
		$('#loadingError').fadeOut('slow');
		}, 2000);
}

piwikHelper.ajaxShowError = function( string )
{
	$('#ajaxError').html(string).show();
}

piwikHelper.ajaxHideError = function()
{
	$('#ajaxError').hide();
}

piwikHelper.ajaxHandleResponse = function(response)
{
	if(response.result == "error") 
	{
		piwikHelper.ajaxShowError(response.message);
	}
	else
	{
		window.location.reload();
	}
	piwikHelper.toggleAjaxLoading();
}

piwikHelper.toggleAjaxLoading = function()
{
	$('#ajaxLoading').toggle();
}

piwikHelper.getStandardAjaxConf = function()
{
	var ajaxRequest = {};
	ajaxRequest.type = 'GET';
	ajaxRequest.url = 'index.php';
	ajaxRequest.dataType = 'json';
	ajaxRequest.error = piwikHelper.ajaxHandleError;
	ajaxRequest.success = piwikHelper.ajaxHandleResponse;
	return ajaxRequest;
}

// Scrolls the window to the jquery element 'elem' if necessary.
// "time" specifies the duration of the animation in ms
piwikHelper.lazyScrollTo = function(elem, time)
{
	var elemTop = $(elem).offset().top;
	//only scroll the page if the graph is not visible 
	if(elemTop < $(window).scrollTop()
	|| elemTop > $(window).scrollTop()+$(window).height())
	{
		//scroll the page smoothly to the graph
		$.scrollTo(elem, time);
	}
}

String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g,"");
}
