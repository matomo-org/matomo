
var requestVariables = new Object;

$(document).ready( bindAllDataTableEvent );

function setDivVariable(id,name,value)
{
	if(!requestVariables[id]) requestVariables[id] = new Object;
		
	requestVariables[id][name] = value;	
}

/* This function is triggered when a new DIV is loaded, which happens
   - at the first loading of the page
   - after any AJAX loading of a DataTable
   
   This function basically add features to the DataTable, 
   - such as column sorting, searching in the rows, displaying Next / Previous links, etc.
   - add styles to the cells and rows (odd / even styles)
   - modify some rows to add images if a span img is found, or add a link if a span urlLink is found
     or truncate the labels when they are too big
   - bind new events onclick / hover / etc. to trigger AJAX requests, 
     nice hovertip boxes for truncated cells
   */
function bindDataTableEvent( indexDiv )
{
	// Array containing the subDataTables ID already loaded. So that when collapsing expanding the same sub Table
	// There is only the first AJAX query and the next times it is read from an array
	var DataTableAlreadyLoaded = new Array;
	
	// ID of the DIV containing the DataTable we are currently working on
	var workingDivId = $(this).attr('id');
	
	// Returns a given Javascript variable associated to the current DIV
	function getRequestVariable( name )
	{
		// IE fix
		if(!requestVariables[workingDivId]) requestVariables[workingDivId] = new Object;
		
		if(requestVariables[workingDivId][name])
		{
			return requestVariables[workingDivId][name];
		}
		return false;
	}
	
	// Set a given JS variable for this DIV
	function setVariable( nameVariable, value )
	{
		requestVariables[workingDivId][nameVariable] = value;	
	}
				
	// Function called to trigger the AJAX request 
	// The ajax request contains the function callback to trigger if the request is successful or failed
	// displayLoading = false When we don't want to display the Loading... DIV #loadingDataTable
	// for example when the script add a Loading... it self and doesn't want to display the generic Loading
	function reloadAjaxDataTable( displayLoading )
	{
//		alert('request ajax');
		if (typeof displayLoading == "undefined") 
		{
	    	displayLoading = true;
	  	}
	  	
		if(displayLoading)
		{
			$('#'+workingDivId+' #loadingDataTable').css('display','block');
		}
		var request = getAjaxRequest();
		$.ajax(request);
//		alert('request done');
	}
	
	// Function called when the AJAX request is successful
	// it looks for the ID of the response and replace the very same ID 
	// in the current page with the AJAX response
	function dataTableLoaded( response )
	{
		var content = $(response);
		var idToReplace = $(content).attr('id');
	
		// if the current dataTable is situated inside another datatable
		table = $(content).parents('table.dataTable');
		if($('#'+idToReplace).parents('.dataTable').is('table'))
		{
			// we add class to the table so that we can give a different style to the subtable
			$(content).children('table.dataTable').addClass('subDataTable');
			$(content).children('#dataTableFeatures').addClass('subDataTable');
		}
		
		
		$('#'+idToReplace).html(content);
		
		// we execute the bindDataTableEvent function for the new DIV
		$('#'+idToReplace).each(bindDataTableEvent);
		
		// and we hide the loading DIV
		$('#loadingDataTable', this).hide();
	}
	
	// Returns the standard Ajax request object used by the Jquery .ajax method
	function getAjaxRequest()
	{
		var ajaxRequest = new Object;
	
		//prepare the ajax request
		ajaxRequest.type = 'GET';
		ajaxRequest.url = 'index.php';
		ajaxRequest.dataType = 'html';
		
		// Callback when the request fails
		ajaxRequest.error = ajaxHandleError;
		
		// Callback when the request succeeds
		ajaxRequest.success = dataTableLoaded;
		
		// Here we prepare the GET string to pass to the AJAX request
		// we build it from the values of the requestVariables for this DIV
		// for example if you want to add a parameter in the AJAX request you can do
		// setVariable('myVariable', 42) and it will be given to the PHP script
		var requestVariableAjax = new Object;
		$.each(	requestVariables[workingDivId],
				function (name, value)
				{
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
		ajaxRequest.data = requestVariableAjax;
		
		return ajaxRequest;
	}
	
	// Returns true if the event keypress passed in parameter is the ENTER key
	function submitOnEnter(e)
	{
		var key=e.keyCode || e.which;
		if (key==13)
		{
			return true;
		}
	}
	
	// reset All filters set to false all the datatable filters JS variables
	// returns the values before reseting the filters
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
			setVariable(value, false);
		}
		
		
		return FiltersToRestore;
	}
	
	// Restores the filters to the values given in the array in parameters
	function restoreAllFilters(FiltersToRestore)
	{
		for(key in FiltersToRestore)
		{ 
			value = FiltersToRestore[key];
			setVariable(key, value);
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
	
	// Showing the search box for this DIV and binding the event
	// - on the keyword DIV anywhere, if the ENTER key is pressed
	// - if
	if(getRequestVariable( 'show_search' ) == true)
	{
		$('#dataTableSearchPattern', this)
			.css('display','block')
			.each(function(){			
				// when enter is pressed in the input field we submit the form
				$('#keyword', this).not(':submit')
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
						
						setVariable('filter_offset', 0); 
						setVariable('filter_column', 'label');
						setVariable('filter_pattern', keyword);
						reloadAjaxDataTable();
					}
				);
				
				$(':submit', this)
					.click( function(){ $(this).submit(); })
				;
			}
		);
			
	}
	
	// Showing the offset information (1 - 10 of 42) for this DIV
	if( getRequestVariable( 'show_offset_information' ) == true )
	{
		$('#dataTablePages', this).each(
			function(){
				var offset = 1+Number(getRequestVariable('filter_offset'));
				var offsetEnd = Number(getRequestVariable('filter_offset')) 
									+ Number(getRequestVariable('filter_limit'));
				var totalRows = Number(getRequestVariable('totalRows'));
				offsetEndDisp = offsetEnd;

				if(offsetEnd > totalRows) offsetEndDisp = totalRows;
				var str = offset + '-' + offsetEndDisp + ' of ' + totalRows;
				$(this).text(str);
			}
		);
	}
	
	// Showing the link "Exclude low population" for this DIV
	if( getRequestVariable( 'show_exclude_low_population' ) == true)
	{
		// Set the string for the DIV, either "Exclude low pop" or "Include all"
		$('#dataTableExcludeLowPopulation', this)
			.each(  function() {
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
		)
			// Bind a click event to the DIV that triggers the ajax request
			.click(
				function()
				{
					var excludeLowPopulationEnabled = getRequestVariable( 'filter_excludelowpop' );
			
					if(excludeLowPopulationEnabled)
					{
						setVariable('filter_excludelowpop', false);
						setVariable('filter_excludelowpop_value', false);
					}
					else
					{
						setVariable('filter_excludelowpop', 2); // add filter on the visits column
						setVariable('filter_excludelowpop_value', 30.0);			
					}
					setVariable('filter_offset', 0);
	
					reloadAjaxDataTable();
				}
			);
	}
	
	// if sorting the columns is enabled, when clicking on a column, 
	// - if this column was already the one used for sorting, we revert the order desc<->asc
	// - we send the ajax request with the new sorting information
	if( getRequestVariable( 'enable_sort' ) == true)
	{
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
				setVariable('filter_offset', 0); 
				setVariable('filter_sort_column', newColumnToSort);
				setVariable('filter_sort_order', currentSortedOrder);
				reloadAjaxDataTable();
			}
		);
	
		// we change the style of the column currently used as sort column
		// adding an image and the class columnSorted to the TD
		var currentSortedColumn = getRequestVariable('filter_sort_column');
		var currentSortedOrder = getRequestVariable('filter_sort_order');
		$(".sortable[@id='"+currentSortedColumn+"']", this)
			.addClass('columnSorted')
			.append('<img src="themes/default/images/sort'+ currentSortedOrder+'.png">');
	}
	
	
	// Display the next link if the total Rows is greater than the current end row
	$('#dataTableNext', this)
		.each(function(){
			var offsetEnd = Number(getRequestVariable('filter_offset')) 
								+ Number(getRequestVariable('filter_limit'));
			var totalRows = Number(getRequestVariable('totalRows'));
			if(offsetEnd < totalRows)
			{
				$(this).css('display','inline');
			}
		})
		// bind the click event to trigger the ajax request with the new offset
		.click(function(){
			setVariable('filter_offset', 
								Number(getRequestVariable('filter_offset')) 
								+ Number(getRequestVariable('filter_limit'))
				); 
			reloadAjaxDataTable();
		})
	;
	
	// Display the previous link if the current offset is not zero
	$('#dataTablePrevious', this)
		.each(function(){
				var offset = 1+Number(getRequestVariable('filter_offset'));
				if(offset != 1)
				{
					$(this).css('display','inline');
				}
			}
		)
		// bind the click event to trigger the ajax request with the new offset
		// take care of the negative offset, we setup 0 
		.click(
			function(){
				var offset = getRequestVariable('filter_offset') - getRequestVariable('filter_limit');
				if(offset < 0) { offset = 0; }
				setVariable('filter_offset', offset); 
				reloadAjaxDataTable();
			}
		)
	;	
	
	// we truncate the labels columns from the second row
	$("table tr td:first-child", this).truncate(30);
    $('.truncated', this).Tooltip();
	
	// we add a link based on the <span id="urlLink"> present in the column label (the first column)
	// if this span is there, we add the link around the HTML in the TD
	// but we add this link only for the rows that are not clickable already (subDataTable)
	$("tr:not('.subDataTable') td:first-child:has('#urlLink')", this).each( function(){
		
		var imgToPrepend = '';
		if( $(this).find('img').length == 0 )
		{
			imgToPrepend = '<img src="themes/default/images/link.gif" /> ';
		}
		var urlToLink = $('#urlLink',this).text();		
		
		$(this).html( 
			'<a target="_blank" href="' + urlToLink + '">' + imgToPrepend + $(this).html() + '</a>'
		);
	});

	// Add some styles on the cells even/odd
	// label (first column of a data row) or not
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
 	);

	// When the TR has a subDataTable class it means that this row has a link to a subDataTable
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
				<tr>\
					<td colspan="'+numberOfColumns+'">\
						<div id="'+divIdToReplaceWithSubTable+'">\
							<span id="loadingDataTable" style="display:inline"><img src="themes/default/images/loading-blue.gif"> Loading...</span>\
						</div>\
					</td>\
				</tr>\
				');
				
				var savedActionVariable = getRequestVariable('action');


				// reset all the filters from the Parent table
				filtersToRestore = resetAllFilters();				

				setVariable('idSubtable', idSubTable);
				setVariable('action', getRequestVariable('actionToLoadTheSubTable'));
				reloadAjaxDataTable( false );
				setVariable('action', savedActionVariable);
				setVariable('idSubtable', false);
				toString(filtersToRestore);
				restoreAllFilters(filtersToRestore);
								
				// add a new row after this one and set the HTML subTable in it
				DataTableAlreadyLoaded[idSubTable] = 1;
				
				$(this).next().toggle();
			}
			
			$(this).next().toggle();
		} 
	);	
}

function bindAllDataTableEvent()
{
	// foreach parentDiv which means for each DataTable
	$('.parentDiv').each( bindDataTableEvent );
}

function ajaxHandleError()
{
	alert('Error ajax loading!');
}


function toString( object )
{
	$.each(object, function (key,value){ alert(key+' = '+value);  } );
}

