
function findSWFGraph(name) {
  if (navigator.appName.indexOf("Microsoft")!= -1) {
    return window[name];
  } else {
    return document[name];
  }
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
