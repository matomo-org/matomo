/*   broadcast object is to help maintain a hash for link clicks and ajax calls
 *   so we can have back button and refresh button working.
 *
 *   Other file that currently depending on this are:
 *     calendar.js
 *     period_select.tpl
 *     sites_selections.tpl
 *     menu.js, ...etc
 */

// Load this once and only once.
broadcast = {};

broadcast.init = function() {
	if(typeof broadcast.isInit != 'undefined') {
		return;
	}
	broadcast.isInit = true;
	// Initialize history plugin.
	// The callback is called at once by present location.hash
	$.historyInit(broadcast.pageload);
}

/************************************************
 *
 *       Broadcast Main Methods:
 *
 ************************************************/
/**========== PageLoad function =================
* This function is called when:
* 1. after calling $.historyInit();
* 2. after calling $.historyLoad();  //look at broadcast.changeParameter();
* 3. after pushing "Go Back" button of a browser
*/
broadcast.pageload = function( hash ) {
	broadcast.init();
    // hash doesn't contain the first # character.
    if( hash ) {
	// restore ajax loaded state
        broadcast.loadAjaxContent(hash);
    } else {
	// start page
	$('#content').empty();
    }
};

/* ============================================
 * propagateAjax -- update hash values then make ajax calls.
 *    example :
 *       1) <a href="javascript:broadcast.propagateAjax('module=Referers&action=getKeywords')">View keywords report</a>
 *       2) Main menu li also goes through this function. check out onClickLi();
 *
 * Will propagate your new value into the current hash string and make ajax calls.
 *
 * NOTE: this method will only make ajax call and replacing main content.
 */

broadcast.propagateAjax = function (ajaxUrl)
{
	broadcast.init();
    // available in global scope
    var currentHashStr = window.location.hash;

    // Because $.history plugin doens't care about # or ? sign infront of the query string
    // We take it out if exist;
    currentHashStr = currentHashStr.replace(/^\?|^#/,'');
    ajaxUrl = ajaxUrl.replace(/^\?|&#/,'');

    var params_vals = ajaxUrl.split("&");
    for( var i=0; i<params_vals.length; i++ ) {
        currentHashStr   = broadcast.updateParamValue(params_vals[i],currentHashStr);
    }

    // Let history know about this new Hash and load it.
    $.historyLoad(currentHashStr);
};

/*
 * propagateNewPage() -- update url value and load new page,
 * Example:
 *         1) We want to update idSite to both search query and hash then reload the page,
 *         2) update period to both search query and hash then reload page.
 *
 * ** If you'd like to make ajax call with new values then use propagateAjax ** *
 *
 * Expecting:
 *         str = "param1=newVal1&param2=newVal2";
 *
 * Currently being use by:
 *
 *  handlePeriodClick,
 *  calendar.js,
 *  sites_seletion.tpl
 *
 * NOTE: This method will refresh the page with new values.
 */
broadcast.propagateNewPage = function (str)
{
	broadcast.init();
    var params_vals = str.split("&");

    // available in global scope
    var currentSearchStr = window.location.search;
    var currentHashStr = window.location.hash;

    for( var i=0; i<params_vals.length; i++ ) {
        // update both the current search query and hash string
        currentSearchStr = broadcast.updateParamValue(params_vals[i],currentSearchStr);

        if(currentHashStr.length != 0 ) {
        	currentHashStr   = broadcast.updateParamValue(params_vals[i],currentHashStr);
        }
    }

    // Now load the new page.
    window.location.href = currentSearchStr + currentHashStr;
};

/*************************************************
 *
 *      Broadcast Supporter Methods:
 *
 *************************************************/

/*
 * updateParamValue(newParamValue,urlStr) -- Helping propagate funtions to update value to url string.
 * eg. I want to update date value to search query or hash query
 *
 * Expecting:
 *        urlStr : A Hash or search query string. e.g: module=whatever&action=index=date=yesterday
 *        newParamValue : A param value pair: e.g: date=2009-05-02
 *
 * Return module=whatever&action=index&date=2009-05-02
 */
broadcast.updateParamValue = function(newParamValue,urlStr)
{
    var p_v = newParamValue.split("=");

    var paramName = p_v[0];
    var valFromUrl = broadcast.getParamValue(paramName,urlStr);

    if( valFromUrl != '') {
        // replacing current param=value to newParamValue;
        var regToBeReplace = new RegExp(paramName + '=' + valFromUrl, 'ig');
        urlStr = urlStr.replace( regToBeReplace, newParamValue );
    } else {
        urlStr += (urlStr == '') ? newParamValue : '&' + newParamValue;
    }

    return urlStr;
};

/*
 * broadcast.loadAjaxContent
 */
broadcast.loadAjaxContent = function(urlAjax)
{
    urlAjax = urlAjax.match(/^\?/) ? urlAjax : "?" + urlAjax;

    // showing loading...
    $('#loadingPiwik').show();
    $('#content').hide();

    $("object").remove();

    broadcast.lastUrlRequested = urlAjax;

    function sectionLoaded(content)
    {
		if(content.substring(0, 14) == '<!DOCTYPE html') {
			window.location.reload();
			return;
		}

        if(urlAjax == broadcast.lastUrlRequested) {
		    $('#content').html( content ).show();
		    $('#loadingPiwik').hide();
		    broadcast.lastUrlRequested = null;
		}
    }
	piwikMenu.activateMenu(
	    broadcast.getParamValue('module', urlAjax),
	    broadcast.getParamValue('action', urlAjax),
	    broadcast.getParamValue('idGoal', urlAjax)
    );
    ajaxRequest = {
        type: 'GET',
	    url: urlAjax,
	    dataType: 'html',
	    async: true,
	    error: broadcast.customAjaxHandleError,	// Callback when the request fails
	    success: sectionLoaded, // Callback when the request succeeds
	    data: new Object
    };
    $.ajax(ajaxRequest);
    return false;
};

broadcast.customAjaxHandleError = function ()
{
    broadcast.lastUrlRequested = null;
    ajaxHandleError();
};

/*
 * Return hash string if hash exists on address bar.
 * else return false;
 */
broadcast.isHashExists = function()
{
    var hashStr = broadcast.getHashFromUrl();

    if ( hashStr != "" ) {
        return hashStr;
    } else {
        return false;
    }
},

/*
 * Get Hash from given url or from current location.
 * return empty string if no hash present.
 */
broadcast.getHashFromUrl = function(url)
{
    var hashStr = "";
    // If url provided, give back the hash from url, else get hash from current address.
    if( url && url.match('#') ) {
        hashStr = url.substring(url.indexOf("#"),url.length);
    }
    else {
        hashStr = location.hash;
    }

    return hashStr;
};


/*
 * Get search query from given url or from current location.
 * return empty string if no search query present.
 */
broadcast.getSearchFromUrl = function(url)
{
    var searchStr = "";
    // If url provided, give back the hash from url, else get hash from current address.
    if( url && url.match(/\?/) ) {
        searchStr = url.substring(url.indexOf("?"),url.length);
    } else {
        searchStr = location.search;
    }

    return searchStr;
};

/*
 * help to get param value for any given url string with provided param name
 * if no url is provided, it will get param from current address.
 * return:
 *   Empty String if param is not found.
 */
broadcast.getValueFromUrl = function (param, url)
{
    var searchString = '';
    if( url ) {
        var urlParts = url.split('#');
        searchString = urlParts[0];
    } else {
        searchString = location.search;
    }
    return broadcast.getParamValue(param,searchString);
};

/*
 * help to get value from hash parameter for any given url string with provided param name
 * if no url is provided, it will get param from current address.
 * return:
 *   Empty String if param is not found.
 */
broadcast.getValueFromHash = function(param, url)
{
    var hashStr = this.getHashFromUrl(url);
    return broadcast.getParamValue(param,hashStr);
};


/*
 * return value for the requested param, will return the first match.
 * out side of this class should use getValueFromHash() or getValueFromUrl() instead.
 * return:
 *   Empty String if param is not found.
 */
broadcast.getParamValue = function (param, url)
{
    var startStr = url.indexOf(param);

    if( startStr  >= 0 ) {
        var endStr = url.indexOf("&", startStr);
        if( endStr == -1 ) {
            endStr = url.length;
        }
        return url.substring(startStr + param.length +1,endStr);
    } else {
        return '';
    }
};
