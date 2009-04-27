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

//TODO all piwik global functions should be static of piwikHelper 
function findSWFGraph(name) {
  if (navigator.appName.indexOf("Microsoft")!= -1) {
    return window[name];
  } else {
    return document[name];
  }
}

function redirectToUrl(url) {
	window.location = url;
}
function ajaxHandleError()
{
	$('#loadingError').show();
	setTimeout( function(){ 
		$('#loadingError').fadeOut('slow');
		}, 2000);
}

function ajaxShowError( string )
{
	$('#ajaxError').html(string).show();
}

function ajaxHideError()
{
	$('#ajaxError').hide();
}

function ajaxHandleResponse(response)
{
	if(response.result == "error") 
	{
		ajaxShowError(response.message);
	}
	else
	{
		window.location.reload();
	}
	toggleAjaxLoading();
}

function toggleAjaxLoading()
{
	$('#ajaxLoading').toggle();
}

String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g,"");
}

function getStandardAjaxConf()
{
	var ajaxRequest = new Object;

	//prepare the ajax request
	ajaxRequest.type = 'GET';
	ajaxRequest.url = 'index.php';
	ajaxRequest.dataType = 'json';
	ajaxRequest.error = ajaxHandleError;
	ajaxRequest.success = ajaxHandleResponse;

	return ajaxRequest;
}

//scroll the window to the jquery element 'elem' if necessary
//time specify the duration of the animation in ms
function lazyScrollTo(elem, time)
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
