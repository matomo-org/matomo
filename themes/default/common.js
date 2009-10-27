/**
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

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

piwikHelper.OFC = (function () {
	var _data = {};
	return {
		get: function (id) {
			return typeof _data[id] == 'undefined' ? '' : _data[id]; },
		set: function (id, data) { _data[id] = data; },
		jquery: {
			name: 'jQuery',
			rasterize: function (src, dst) { $('#'+dst).replaceWith(piwikHelper.OFC.jquery.image(src)); },
			image: function (src) { return '<img title="Piwik Graph" src="data:image/png;base64,' + $('#'+src)[0].get_img_binary() + '" />'; },
			popup: function (src) {
				var img_win = window.open('', 'ExportChartAsImage');
				img_win.document.write('<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" /><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>' + _pk_translate('General_ExportAsImage_js') + '</title></head><body>' + piwikHelper.OFC.jquery.image(src) + '<br /><br /><p>' + _pk_translate('General_SaveImageOnYourComputer_js') + '</p></body></html>');
				img_win.document.close();
			},
			load: function (dst, data) { $('#'+dst)[0].load(data || piwikHelper.OFC.get(dst)); }
		}
	};
})();

// Open Flash Charts 2 - callback when chart is being initialized
function open_flash_chart_data(chartId) {
	if (typeof chartId != 'undefined') {
		return piwikHelper.OFC.get(chartId);
	}
	return '';
}

// Open Flash Charts 2 - callback when user selects "Save Image Locally" (right click on Flash chart for pop-up menu)
function save_image(chartId) {
	if (typeof chartId != 'undefined') {
		piwikHelper.OFC.jquery.popup(chartId);
	}
}

String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g,"");
}
