
<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script type="text/javascript" src="themes/default/common.js"></script>
{literal}
<script>

$(document).ready( bindDataTableEvent );

function bindDataTableEvent()
{ 
	$('#dataTableExcludeLowPopulation').click(
		function(){
			addFilter('filter_excludelowpop', 2); // add filter on the visits column
			addFilter('filter_excludelowpop_value', 400.0);
			reloadAjaxDataTable();
		}
	);
		
//			'filter_column' 			=> array('string'), 
//			'filter_pattern' 			=> array('string'),

//			'filter_sort_column' 		=> array('string', Piwik_Archive::INDEX_NB_VISITS),
//			'filter_sort_order' 		=> array('string', 'desc'),

//			'filter_offset' 			=> array('integer'),
//			'filter_limit' 				=> array('integer'),

		
	$('#dataTableNext').click(
		function(){
			addFilter('filter_offset', requestVariables.filter_offset + requestVariables.filter_limit); 
			reloadAjaxDataTable();
		}
	);
	$('#dataTablePrevious').click(
		function(){
			var offset = requestVariables.filter_offset - requestVariables.filter_limit;
			if(offset < 0) { offset = 0; }
			addFilter('filter_offset', offset); 
			reloadAjaxDataTable();
		}
	);
}
function addFilter( nameVariable, value )
{
	requestVariables[nameVariable] = value;	
}

function dataTableLoaded( response )
{
	var idToReplace = $(response).attr('id');
	$('#'+idToReplace).html(response);
	
	bindDataTableEvent();
}
function ajaxHandleError()
{
	alert('error!');
}

function getAjaxRequest()
{
	var ajaxRequest = new Object;

	//prepare the ajax request
	ajaxRequest.type = 'GET';
	ajaxRequest.url = 'index.php';
	ajaxRequest.dataType = 'html';
	ajaxRequest.error = ajaxHandleError;
	ajaxRequest.success = dataTableLoaded;

//	$.each(requestVariables, function (key,value){ alert(key+' = '+value);  } );
	ajaxRequest.data = requestVariables;
	
	return ajaxRequest;
}
function reloadAjaxDataTable()
{
	var request = getAjaxRequest();
	$.ajax(request);
}

function toString( object )
{
	var str='';
	$.each(object, function (key,value){ alert(key+' = '+value);  } );
	return str; 
}
</script>
{/literal}
<h1>User Settings<h1>

<h2>Resolutions</h2>
{$dataTableResolution}
<h2>Browsers</h2>
{* ataTableBrowsers} *}