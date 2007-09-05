
<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script type="text/javascript" src="themes/default/common.js"></script>
<script type="text/javascript" src="libs/jquery/jquery.dimensions.js"></script>
<script type="text/javascript" src="libs/jquery/tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="libs/jquery/truncate/jquery.truncate.js"></script>
<link rel="stylesheet" href="libs/jquery/tooltip/jquery.tooltip.css">

{literal}
<script>

var requestVariables = new Object;

$(document).ready( bindAllDataTableEvent );

function bindDataTableEvent( indexDiv )
{
	var DataTableAlreadyLoaded = new Array;
	
	// name of the DataTable we are working on
	var workingDivId = $(this).attr('id');
	
	function getRequestVariable( name )
	{
if(!requestVariables[workingDivId]) requestVariables[workingDivId] = new Object;
		if(requestVariables[workingDivId][name])
		{
			return requestVariables[workingDivId][name];
		}
		return false;
	}
	
	function addFilter( nameVariable, value )
	{
	//if(nameVariable=='action') alert('set action for '+workingDivId);
		requestVariables[workingDivId][nameVariable] = value;	
	}
				
	function reloadAjaxDataTable( displayLoading )
	{
		if (typeof displayLoading == "undefined") 
		{
	    	displayLoading = true;
	  	}
	  	
		if(displayLoading)
		{
			$('#'+workingDivId+' > #loadingDataTable', this).show();
		}
		var request = getAjaxRequest();
		$.ajax(request);
//		alert('request done');
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
		
		//$.each(requestVariables.UserSettingsResolution, function (key,value){ alert(key+' = '+value);  } );

		var requestVariableAjax = new Object;
		$.each(	requestVariables[workingDivId],
				function (name, value)
				{
//					alert(name+' = '+value);
					if( typeof(value) != 'boolean' 
						|| value != false )
					{
						requestVariableAjax[name] = value;
					}
					else
					{
//						alert(name +'='+value);
					}
				}
		);
//		toString(requestVariableAjax);
		ajaxRequest.data = requestVariableAjax;
		
		return ajaxRequest;
	}
	
	function setExcludeLowPopulationString()
	{
		var excludeLowPopulationEnabled =  getRequestVariable( 'filter_excludelowpop' );
		//alert(excludeLowPopulationEnabled);
		if(excludeLowPopulationEnabled != false)
		{
			string = 'Include all population';
		}
		else
		{
			string = 'Exclude low population';
		}
		$(this).html(string);
	}
	
	
	function submitOnEnter(e)
	{
		var key=e.keyCode || e.which;
		if (key==13)
		{
			return true;
		}
	}
	
	/* List of the filters to be applied
		// pattern search
		'filter_column'
		'filter_pattern'
		
		// remove rows for which a given column is less than a given value
		'filter_excludelowpop'
		'filter_excludelowpop_value'
		
		// order by some column 
		'filter_sort_column'
		'filter_sort_order'
		
		// offset, limit
		'filter_offset'
		'filter_limit'
	*/
	
	$('#loadingDataTable', this).hide();
	
	$('#dataTableExcludeLowPopulation', this)
		.each(  setExcludeLowPopulationString );
	
	$('#dataTableExcludeLowPopulation', this)
		.click(
			function()
			{
				var excludeLowPopulationEnabled =  getRequestVariable( 'filter_excludelowpop' );
		
				if(excludeLowPopulationEnabled)
				{
					addFilter('filter_excludelowpop', false);
					addFilter('filter_excludelowpop_value', false);
				}
				else
				{
					addFilter('filter_excludelowpop', 2); // add filter on the visits column
					addFilter('filter_excludelowpop_value', 30.0);			
				}
				addFilter('filter_offset', 0);

				reloadAjaxDataTable();
			}
		);
	
	
	$('#dataTablePages', this).each(
		function(){
			var offset = 1+Number(getRequestVariable('filter_offset'));
			var offsetEnd = Number(getRequestVariable('filter_offset')) 
								+ Number(getRequestVariable('filter_limit'));
			var totalRows = Number(getRequestVariable('totalRows'));
			offsetEndDisp = offsetEnd;
//		alert(totalRows);
			if(offsetEnd > totalRows) offsetEndDisp = totalRows;
			var str = offset + '-' + offsetEndDisp + ' of ' + totalRows;
			//alert(str);
			$(this).text(str);
		}
	);


		//	
			
	
	$('#dataTableNext', this)
		.each(function(){
			var offsetEnd = Number(getRequestVariable('filter_offset')) 
								+ Number(getRequestVariable('filter_limit'));
			var totalRows = Number(getRequestVariable('totalRows'));
			if(offsetEnd <= totalRows)
			{
				$(this).css('display','inline');
			}
		})
		.click(function(){
			addFilter('filter_offset', 
								Number(getRequestVariable('filter_offset')) 
								+ Number(getRequestVariable('filter_limit'))
				); 
			reloadAjaxDataTable();
		})
	;
	
	$('#dataTablePrevious', this)
		.each(function(){
				var offset = 1+Number(getRequestVariable('filter_offset'));
				if(offset != 1)
				{
					$(this).css('display','inline');
				}
			}
		)
		.click(
			function(){
				var offset = getRequestVariable('filter_offset') - getRequestVariable('filter_limit');
				if(offset < 0) { offset = 0; }
				addFilter('filter_offset', offset); 
				reloadAjaxDataTable();
			}
		)
	;
	
	
	$('.sortable', this).click( 
		function(){
			var newColumnToSort = $(this).attr('id');
			// we lookup if the column to sort was already this one, if it is the case then we switch from desc <-> asc 
			var currentSortedColumn =  getRequestVariable('filter_sort_column');
			var currentSortedOrder = getRequestVariable('filter_sort_order');
			if(currentSortedColumn == newColumnToSort) 
			{
				// toggle the sorted order
				if(currentSortedOrder == 'asc')
				{
					currentSortedOrder = 'desc';
				}
				else
				{
					currentSortedOrder = 'asc';
				}
			}
			addFilter('filter_offset', 0); 
			addFilter('filter_sort_column', newColumnToSort);
			addFilter('filter_sort_order', currentSortedOrder);
			reloadAjaxDataTable();
		}
	);
	
	// we change the style of the column currently used as sort column
	var currentSortedColumn = getRequestVariable('filter_sort_column');
	var currentSortedOrder = getRequestVariable('filter_sort_order');
	$(".sortable[@id='"+currentSortedColumn+"']", this)
			.addClass('columnSorted')
			.append('<img src="themes/default/images/sort'+ currentSortedOrder+'.png">');

	
	// we truncate the labels columns from the second row
	$("td:first-child", this).truncate(30);
    $('.truncated', this).Tooltip();
	                
	$("td:first-child:odd", this).addClass('label labelodd');
	$("td:first-child:even", this).addClass('label labeleven');
	$("tr:odd td", this).slice(1).addClass('columnodd');
	$("tr:even td", this).slice(1).addClass('columneven');
	$("th", this).hover( function() {  
	 	 $(this).css({ cursor: "pointer"}); 
	  	},
	  	function() {  
	 	 $(this).css({ cursor: "auto"}); 
	  	}
 	)
	
	// search for a pattern in the table
	$('#dataTableSearchPattern', this).each(
		function(){
		
			// when enter is pressed in the input field we submit the form
			$('#keyword', this)
				.keypress( 
					function(e)
					{ 
						if(submitOnEnter(e))
						{ 
							$(this).siblings(':submit').submit(); 
						} 
					} 
				)
				.val( function(){
						var currentPattern = getRequestVariable('filter_pattern');
						if(currentPattern.length > 0)
						{
							return currentPattern;
						}
						return '';
					}
				)
			;
			
			$(':submit', this).submit( 
				function()
				{
					var keyword = $(this).siblings('#keyword').val();
					
					addFilter('filter_offset', 0); 
					addFilter('filter_column', 'label');
					addFilter('filter_pattern', keyword);
					reloadAjaxDataTable();
				}
			);
			
			$(':submit', this)
				.click( function(){ $(this).submit(); })
			;
		}
	);
		

	
	// case subDataTable available
	$('tr.subDataTable', this)
		.click( 
		function()
		{
			// get the idSubTable
			var idSubTable = $(this).attr('id');
			var divIdToReplaceWithSubTable = 'subDataTable_'+idSubTable;
			
			if( !DataTableAlreadyLoaded[idSubTable] )
			{
				// if the subDataTable is not in the array of already loaded tables
				
				
				var numberOfColumns = $(this).children().length;
				
				// at the end of the query it will replace the ID matching the new HTML table #ID
				// we need to create this ID first
				$(this).after( '\
				<tr style="display:none">\
					<td colspan="'+numberOfColumns+'">\
						<div id="'+divIdToReplaceWithSubTable+'">\
							<span id="loadingDataTable"><img src="themes/default/images/loading-blue.gif"> Loading...</span>\
						</div>\
					</td>\
				</tr>\
				');
				
				var savedActionVariable = getRequestVariable('action');


				// reset all the filters from the Parent table
				filtersToRestore = resetAllFilters();				

				addFilter('idSubtable', idSubTable);
				addFilter('action', getRequestVariable('actionToLoadTheSubTable'));
				reloadAjaxDataTable( false );
				addFilter('action', savedActionVariable);
				addFilter('idSubtable', false);
				toString(filtersToRestore);
				restoreAllFilters(filtersToRestore);
								
				// add a new row after this one and set the HTML subTable in it
				DataTableAlreadyLoaded[idSubTable] = 1;
			}
			
			$(this).next().toggle();
		} 
	);
	
	
	
	function resetAllFilters()
	{
		var FiltersToRestore = new Array();
		filters = [ 
			'filter_column', 
			'filter_pattern', 
			'filter_excludelowpop',
			'filter_excludelowpop_value',
			'filter_offset',
			'filter_limit',
			'filter_sort_column',
			'filter_sort_order',
		];
		
		for(key in filters)
		{
			value = filters[key];
			FiltersToRestore[value] = getRequestVariable(value);
			//if(FiltersToRestore[value]!=false) alert('save '+value+'='+FiltersToRestore[value]);
			addFilter(value, false);
		}
		
		
		return FiltersToRestore;
	}
	
	function restoreAllFilters(FiltersToRestore)
	{
		for(key in FiltersToRestore)
		{ 
			value = FiltersToRestore[key];
			addFilter(key, value);
		}
	}
}

function bindAllDataTableEvent()
{
	// foreach parentDiv which means for each DataTable
	$('.parentDiv').each( bindDataTableEvent );
}

function dataTableLoaded( response )
{
	var content = $(response);
	var idToReplace = $(content).attr('id');

	// if the current dataTable is situated inside another datatable
	table = $(content).parents('table.dataTable');
	
	if($('#'+idToReplace).parents('.dataTable').is('table'))
	{
		$(content).children('table.dataTable').addClass('subDataTable');
		$(content).children('#dataTableFeatures').addClass('subDataTable');
	}
	
	$('#'+idToReplace)
			.html(content);
			
	$('#'+idToReplace).each(bindDataTableEvent);
	$('#loadingDataTable', this).hide();
	
}

function ajaxHandleError()
{
	alert('Error ajax loading!');
}


function toString( object )
{
	$.each(object, function (key,value){ alert(key+' = '+value);  } );
}


</script>

<style>
* {
	font-family: "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
}

/* main data table */

table.dataTable th.columnSorted {
	font-weight:bold;
}
table.dataTable {
	width: 500px;
	padding: 0;
	border-spacing:0;
	margin: 0;
	font-family: "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
	font-size:0.9em;
}

table.dataTable tr.subDataTable{
	cursor:pointer;
}

table.dataTable th {
	margin:0;
	color: #6D929B;
	border-right: 1px solid #C1DAD7;
	border-bottom: 1px solid #C1DAD7;
	border-top: 1px solid #C1DAD7;
	letter-spacing: 2px;
	text-transform: uppercase;
	text-align: left;
	padding: 6px 6px 6px 12px;
	background: #CAE8EA url(themes/default/images/bg_header.jpg) no-repeat;
}

table.dataTable td.label {
	width:80%;
}

table.dataTable td {
	margin:0;
	border-right: 1px solid #C1DAD7;
	border-bottom: 1px solid #C1DAD7;
	background: #fff;
	padding: 6px 6px 6px 12px;
	color: #4f6b72;
}

table.dataTable td.columneven {
	background: #F5FAFA;
	color: #797268;
}

table.dataTable td.labeleven {
	border-top: 0;
	border-left: 1px solid #C1DAD7;
	background: #fff url(themes/default/images/bullet1.gif) no-repeat;
}

table.dataTable td.labelodd {
	border-top: 0;
	border-left: 1px solid #C1DAD7;
	background: #f5fafa url(themes/default/images/bullet2.gif) no-repeat;
	color: #797268;
}


/* a datatable inside another datatable */

table.subDataTable img {
	border:0;
	margin-top:.5em;
}	

table.subDataTable {
	background:#FFFFFF;
	color: #678197;
	width:80%;
	border-top:1px solid #e5eff8;
	border-right:1px solid #e5eff8;
	margin:1em auto;
	border-collapse:collapse;
}

table.subDataTable tr.columnodd td	{
	background:#f7fbff
}


table.subDataTable td {
	color:#678197;
	border-bottom:1px solid #e5eff8;
	border-left:1px solid #e5eff8;
	padding:.3em 1em;
	text-align:left;
}

table.subDataTable td.label	{
	background:#ffffff;
	width:80%;
}

table.subDataTable td.labelodd	{
	background:#f4f9fe;
}
				
table.subDataTable th {
	font-weight:normal;
	color: #678197;
	text-align:left;
	border-bottom: 1px solid #e5eff8;
	border-left:1px solid #e5eff8;
	padding:.3em 1em;
}
					
table.subDataTable thead th {
	background:#f4f9fe;
	text-align:center;
	font: 0.8em "Century Gothic","Trebuchet MS",Arial,Helvetica,sans-serif;
	color:#66a3d3
}

/* misc SPAN and DIV */

#dataTablePages {
	color:grey;
	font-weight:bold;
	margin:10px;
	font-size:0.9em;
}

#dataTableSearchPattern input {
	font-size: 0.7em;
	padding:2px;
	background:#FBFBFF none repeat scroll 0%;
	border:1px solid #B3B3B3;
	color:#0C183A;
}
#dataTableSearchPattern input:hover{
	background:#F7F7FF none repeat scroll 0%;
}

#dataTableExcludeLowPopulation, #dataTableNext, #dataTablePrevious {
	font-size: 1em;
	color: #184A83;
	text-decoration:underline;
	cursor:pointer;
}

#dataTableFeatures {
	padding-top:10px;
	padding-bottom:10px;
	width:400px;
	text-align:center;
}

#dataTableExcludeLowPopulation{
	position:absolute;
	float:left;
	margin-top:2em;
	margin-left:350px;
	font-size:0.8em;
	color:#C3C6D8;
}
div.subDataTable {
	font-size:0.8em;
}
#dataTableNext, #dataTablePrevious {
	display:none;

}

#loadingDataTable {
	font-size: 1em;
	font-decoration:bold;
	color:#193B6C;
	padding:0.5em;
}

</style>
{/literal}

<h1>User Settings<h1>

<h2>Search engines</h2>
{$dataTableSearchEngines}
<h2>Keywords</h2>
{$dataTableKeywords}
<h2>Browsers</h2>
{$dataTableBrowser}
<h2>Resolutions</h2>
{$dataTableResolution}

