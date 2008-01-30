function ajaxHandleError()
{
	alert('Transfer error, please reload the page or try again later.');
}

function ajaxShowError( string )
{
	$('#ajaxError').html(string).show();
}

function ajaxHideError()
{
	$('#ajaxError').hide();
}

function ajaxToggleLoading()
{
	$('#ajaxLoading').toggle();
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
	ajaxToggleLoading();
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
