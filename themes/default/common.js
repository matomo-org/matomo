/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function piwikHelper()
{
}

piwikHelper.htmlDecode = function(value)
{
	return $('<div/>').html(value).text();
}


/*
 * Displays a Modal dialog. Text will be taken from the DOM node domSelector.
 * When user clicks Yes in Modal,onValidate() will be executed.
 * 
 * On clicking No, or esc key, the dialog will fade out.
 */
piwikHelper.windowModal = function( domSelector, onValidate )
{
    var question = $(domSelector);
    var buttons = {};
    var textYes = $('#yes', question).attr('value');
    if(textYes) {
        buttons[textYes] = function(){$(this).dialog("close"); onValidate()};
        $('#yes', question).hide();
    }
    var textNo = $('#no', question).attr('value');
    if(textNo) {
        buttons[textNo] = function(){$(this).dialog("close");};
        $('#no', question).hide();
    }
    question.dialog({
        resizable: false,
        modal: true,
        buttons: buttons,
        width: 650,
        position: ['center', 90]
    });
}

var globalAjaxQueue = [];
piwikHelper.queueAjaxRequest = function( request )
{
	globalAjaxQueue.push(request);
}
piwikHelper.abortQueueAjax = function()
{
	for(var request in globalAjaxQueue) {
		globalAjaxQueue[request].abort();
	}
	globalAjaxQueue = [];
	return true;
}
piwikHelper.getCurrentQueryStringWithParametersModified = function(newparams)
{
	var parameters = String(window.location.search);
	if(newparams) {
		if(parameters != '') {
			var r, i, keyvalue, keysvalues = newparams.split('&');
			for(i in keysvalues) {
				keyvalue = keysvalues[i].split('=');
				r = new RegExp('(^|[?&])'+keyvalue[0]+'=[^&]*');
				parameters = parameters.replace(r, '');
			}
			parameters += '&' + newparams;
			if(parameters[0] == '&') {
				parameters = '?' + parameters.substring(1);
			}
		} else {
			parameters = '?' + newparams;
		}
	}
	return String(window.location.pathname) + parameters;
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
	if(!parameters || parameters.length==0) {
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
	if(document.getElementById)
		return document.getElementById(name);
	if(document.layers)
		return document[id];
	if(document.all)
		return document.all[id];
	return null;
}

piwikHelper.showAjaxError = function( string, errorDivID )
{
	errorDivID = errorDivID || 'ajaxError';
	$('#'+errorDivID).html(string).show();
}

piwikHelper.hideAjaxError = function(errorDivID)
{
	errorDivID = errorDivID || 'ajaxError';
	$('#'+errorDivID).hide();
}

piwikHelper.showAjaxLoading = function(loadingDivID)
{
	loadingDivID = loadingDivID || 'ajaxLoading';
	$('#'+loadingDivID).show();
}
piwikHelper.hideAjaxLoading = function(loadingDivID)
{
	loadingDivID = loadingDivID || 'ajaxLoading';
	$('#'+loadingDivID).hide();
}

piwikHelper.getStandardAjaxConf = function(loadingDivID, errorDivID, params)
{
	piwikHelper.showAjaxLoading(loadingDivID);
	piwikHelper.hideAjaxError(errorDivID);
	var ajaxRequest = {};
	ajaxRequest.type = 'GET';
	ajaxRequest.url = 'index.php';
	ajaxRequest.dataType = 'json';
	ajaxRequest.error = piwikHelper.ajaxHandleError;
	ajaxRequest.success = function(response) { piwikHelper.ajaxHandleResponse(response, loadingDivID, errorDivID, params); };
	return ajaxRequest;
}

piwikHelper.refreshAfter = function(timeoutPeriod) 
{
	if(timeoutPeriod == 0) {
		location.reload(true);
	} else {
		setTimeout("location.reload(true);",timeoutPeriod);
	}
}

piwikHelper.redirectToUrl = function(url) 
{
	window.location = url;
}

piwikHelper.ajaxHandleError = function()
{
	$('#loadingError').show();
	setTimeout( function(){ 
		$('#loadingError').fadeOut('slow');
		}, 2000);
}

piwikHelper.ajaxHandleResponse = function(response, loadingDivID, errorDivID, params)
{
	if(response.result == "error") 
	{
		piwikHelper.hideAjaxLoading(loadingDivID);
		piwikHelper.showAjaxError(response.message, errorDivID);
	}
	else
	{
		// add updated=1 to the URL so that a "Your changes have been saved" message is displayed
		var urlToRedirect = piwikHelper.getCurrentQueryStringWithParametersModified(params);
		var updatedUrl = new RegExp('&updated=([0-9]+)');
		var updatedCounter = updatedUrl.exec(urlToRedirect);
		if(!updatedCounter)
		{
			urlToRedirect += '&updated=1';
		}
		else
		{
			updatedCounter = 1 + parseInt(updatedCounter[1]);
			urlToRedirect = urlToRedirect.replace(new RegExp('(&updated=[0-9]+)'), '&updated=' + updatedCounter);
		}
		var currentHashStr = window.location.hash;
		if(currentHashStr.length > 0) {
			urlToRedirect += currentHashStr;
		}
		piwikHelper.redirectToUrl(urlToRedirect);
	}
}

/**
 * Scrolls the window to the jquery element 'elem' 
 * if the top of the element is not currently visible on screen
 * @param elem Selector for the DOM node to scroll to, eg. '#myDiv'  
 * @param time Specifies the duration of the animation in ms
 */ 
piwikHelper.lazyScrollTo = function(elem, time)
{
	var elemTop = $(elem).offset().top;
	// only scroll the page if the graph is not visible 
	if(elemTop < $(window).scrollTop()
	|| elemTop > $(window).scrollTop()+$(window).height())
	{
		// scroll the page smoothly to the graph
		$.scrollTo(elem, time);
	}
}

piwikHelper.getApiFormatTextarea = function (textareaContent)
{
	return textareaContent.trim().split("\n").join(',');
}

String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g,"");
}

//Helper function :
//returns true if the event keypress passed in parameter is the ENTER key
function isEnterKey(e)
{
	return (window.event?window.event.keyCode:e.which)==13; 
}
