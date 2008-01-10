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
	var workingDivId;
	// Array containing the subDataTables ID already loaded. So that when collapsing expanding the same sub Table
	// There is only the first AJAX query and the next times it is read from an array
	var DataTableAlreadyLoaded = new Array;
	
	// ID of the DIV containing the DataTable we are currently working on
	workingDivId = $(this).attr('id');	
	
	// reset All filters set to false all the datatable filters JS variables
	// returns the values before reseting the filters
	function resetAllFilters()
	{
		var FiltersToRestore = new Array();
		filters = [ 
			'filter_column', 
			'filter_pattern', 
			'filter_column_recursive', 
			'filter_pattern_recursive', 
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
			FiltersToRestore[value] = getRequestVariable(workingDivId,value);
			//if(FiltersToRestore[value]!=false) alert('save '+value+'='+FiltersToRestore[value]);
			setVariable(workingDivId, value, false);
		}
		
		
		return FiltersToRestore;
	}
	
	// Restores the filters to the values given in the array in parameters
	function restoreAllFilters(FiltersToRestore)
	{
		for(key in FiltersToRestore)
		{ 
			value = FiltersToRestore[key];
			setVariable(workingDivId, key, value);
		}
	}
	
	/* List of the filters to be applied
		// pattern search
		'filter_column'
		'filter_pattern'
		
		// recursive pattern search
		'filter_column_recursive'
		'filter_pattern_recursive'
		
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
	
	
	// if sorting the columns is enabled, when clicking on a column, 
	// - if this column was already the one used for sorting, we revert the order desc<->asc
	// - we send the ajax request with the new sorting information
	if( getRequestVariable(workingDivId, 'enable_sort' ) == true)
	{
		$('.sortable', this).click( 
			function(){
				var newColumnToSort = $(this).attr('id');
				// we lookup if the column to sort was already this one, if it is the case then we switch from desc <-> asc 
				var currentSortedColumn =  getRequestVariable(workingDivId,'filter_sort_column');
				var currentSortedOrder = getRequestVariable(workingDivId,'filter_sort_order');
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
				setVariable(workingDivId, 'filter_offset', 0); 
				setVariable(workingDivId, 'filter_sort_column', newColumnToSort);
				setVariable(workingDivId, 'filter_sort_order', currentSortedOrder);
				reloadAjaxDataTable(workingDivId);
			}
		);
	
		var imageSortWidth = 16;
		var imageSortHeight = 16;
		// we change the style of the column currently used as sort column
		// adding an image and the class columnSorted to the TD
		var currentSortedColumn = getRequestVariable(workingDivId,'filter_sort_column');
		var currentSortedOrder = getRequestVariable(workingDivId,'filter_sort_order');
		$(".sortable[@id='"+currentSortedColumn+"']", this)
			.addClass('columnSorted')
			.append('<img width="'+imageSortWidth+'" height="'+imageSortHeight+'" src="themes/default/images/sort'+ currentSortedOrder+'.png">');
	}
	
	
	handleSearchBox( workingDivId, this );
	handleLowPopulationLink( workingDivId, this );
	
	
	// Showing the offset information (1 - 10 of 42) for this DIV
	if( getRequestVariable(workingDivId, 'show_offset_information' ) == true
		// fix konqueror that doesnt recognize the show_offset_information false for the tag cloud
		// and we really dont want to print Next/Previous for tag clouds
		&& getRequestVariable(workingDivId, 'viewDataTable') != 'cloud' )
	{
		$('#dataTablePages', this).each(
			function(){
				var offset = 1+Number(getRequestVariable(workingDivId,'filter_offset'));
				var offsetEnd = Number(getRequestVariable(workingDivId,'filter_offset')) 
									+ Number(getRequestVariable(workingDivId,'filter_limit'));
				var totalRows = Number(getRequestVariable(workingDivId,'totalRows'));
				offsetEndDisp = offsetEnd;

				if(offsetEnd > totalRows) offsetEndDisp = totalRows;
				var str = offset + '-' + offsetEndDisp + ' of ' + totalRows;
				$(this).text(str);
			}
		);
		
		// Display the next link if the total Rows is greater than the current end row
		$('#dataTableNext', this)
			.each(function(){
				var offsetEnd = Number(getRequestVariable(workingDivId,'filter_offset')) 
									+ Number(getRequestVariable(workingDivId,'filter_limit'));
				var totalRows = Number(getRequestVariable(workingDivId,'totalRows'));
				if(offsetEnd < totalRows)
				{
					$(this).css('display','inline');
				}
			})
			// bind the click event to trigger the ajax request with the new offset
			.click(function(){
				setVariable(workingDivId, 'filter_offset', 
									Number(getRequestVariable(workingDivId,'filter_offset')) 
									+ Number(getRequestVariable(workingDivId,'filter_limit'))
					); 
				reloadAjaxDataTable(workingDivId);
			})
		;
		
		// Display the previous link if the current offset is not zero
		$('#dataTablePrevious', this)
			.each(function(){
					var offset = 1+Number(getRequestVariable(workingDivId,'filter_offset'));
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
					var offset = getRequestVariable(workingDivId,'filter_offset') - getRequestVariable(workingDivId,'filter_limit');
					if(offset < 0) { offset = 0; }
					setVariable(workingDivId, 'filter_offset', offset); 
					reloadAjaxDataTable(workingDivId);
				}
			)
		;	
		
	}
	
	if( getRequestVariable(workingDivId, 'idSubtable' ) == false)
	{
		$('#exportDataTable', this)
			.show()
			.hover( function() {  
			 	 $(this).css({ cursor: "pointer"}); 
			  	},
			  	function() {  
			 	 $(this).css({ cursor: "auto"}); 
			  	}
	 	);
	 	
		$('.viewDataTable', this).click(
			function(){
					var viewDataTable = $(this).attr('format');
					resetAllFilters();
					setVariable(workingDivId, 'viewDataTable', viewDataTable);
					
					reloadAjaxDataTable(workingDivId);
				}
		);
		
	 	$('#exportToFormat img', this).click(function(){
	 		$(this).siblings('#linksExportToFormat').toggle();
	 	});
	 	
	 	$('.exportToFormat', this).attr( 'href', function(){
	 			var format = $(this).attr('format');
	 			var method = $(this).attr('methodToCall');
	 			var filter_limit = $(this).attr('filter_limit');
	 			
	 			var str = '?module=API'
						+'&method='+method
	 					+'&format='+format
	 					+'&idSite='+getRequestVariable(workingDivId,'idSite')
	 					+'&period='+getRequestVariable(workingDivId,'period')
	 					+'&date='+getRequestVariable(workingDivId,'date');
	 			if( filter_limit )
	 			{
	 				str += '&filter_limit=' + filter_limit;
	 			}
	 			return str;
	 		}
	 	);
	}

	// we truncate the labels columns from the second row
	$("table tr td:first-child", this).truncate(30);
    $('.truncated', this).Tooltip();
	
	var imageLinkWidth = 10;
	var imageLinkHeight = 9;
	
	// we add a link based on the <span id="urlLink"> present in the column label (the first column)
	// if this span is there, we add the link around the HTML in the TD
	// but we add this link only for the rows that are not clickable already (subDataTable)
	$("tr:not('.subDataTable') td:first-child:has('#urlLink')", this).each( function(){
		
		var imgToPrepend = '';
		if( $(this).find('img').length == 0 )
		{
			imgToPrepend = '<img width="'+imageLinkWidth+'" height="'+imageLinkHeight+'" src="themes/default/images/link.gif" /> ';
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
				
				var savedActionVariable = getRequestVariable(workingDivId,'action');


				// reset all the filters from the Parent table
				filtersToRestore = resetAllFilters();

				setVariable(workingDivId, 'idSubtable', idSubTable);
				setVariable(workingDivId, 'action', getRequestVariable(workingDivId,'actionToLoadTheSubTable'));
				reloadAjaxDataTable(workingDivId, false );
				setVariable(workingDivId, 'action', savedActionVariable);
				setVariable(workingDivId, 'idSubtable', false);
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

// Returns true if the event keypress passed in parameter is the ENTER key
function submitOnEnter(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		return true;
	}
}


function handleLowPopulationLink(workingDivId, currentThis, callbackSuccess )
{

	// Showing the link "Exclude low population" for this DIV
	if( getRequestVariable(workingDivId, 'show_exclude_low_population' ) == true)
	{
		// Set the string for the DIV, either "Exclude low pop" or "Include all"
		$('#dataTableExcludeLowPopulation', currentThis)
			.each(  function() {
				var excludeLowPopulationEnabled = 
					getRequestVariable(workingDivId, 'filter_excludelowpop' );
				//alert(workingDivId + ' ' +excludeLowPopulationEnabled);
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
					var excludeLowPopulationEnabled =
						getRequestVariable(workingDivId, 'filter_excludelowpop' );
			
					if(excludeLowPopulationEnabled != false)
					{
					//	alert('we include all');
						setVariable(workingDivId, 'filter_excludelowpop', 0);
						setVariable(workingDivId, 'filter_excludelowpop_value', 0);
					}
					else
					{
					//	alert('we exclude low');
						setVariable(	workingDivId, 
										'filter_excludelowpop', 
										getRequestVariable(workingDivId, 'filter_excludelowpop_default' )
							);
						setVariable(	workingDivId, 
										'filter_excludelowpop_value',
										getRequestVariable(workingDivId, 'filter_excludelowpop_value_default' )
							);			
					}
					setVariable(workingDivId, 'filter_offset', 0);
	
					reloadAjaxDataTable(workingDivId, true, callbackSuccess);
					
				}
			);
	}
	
}
function handleSearchBox( workingDivId, currentThis, callbackSuccess )
{
	// Showing the search box for currentThis DIV and binding the event
	// - on the keyword DIV anywhere, if the ENTER key is pressed
	// - if
	if(getRequestVariable(workingDivId, 'show_search' ) == true)
	{
		$('#dataTableSearchPattern', currentThis)
			.css('display','block')
			.each(function(){			
				// when enter is pressed in the input field we submit the form
				$('#keyword', currentThis).not(':submit')
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
							var currentPattern = getRequestVariable(workingDivId,'filter_pattern');
							if(currentPattern.length > 0)
							{
								return currentPattern;
							}
							var currentPattern = getRequestVariable(workingDivId,'filter_pattern_recursive');
							if(currentPattern.length > 0)
							{
								return currentPattern;
							}
							return '';
						}
					)
				;
				
				$(':submit', currentThis).submit( 
					function()
					{
						var keyword = $(this).siblings('#keyword').val();
						setVariable(workingDivId, 'filter_offset', 0); 
						
						if(getRequestVariable(workingDivId, 'search_recursive' ) == true)
						{
							setVariable(workingDivId, 'filter_column_recursive', 'label');
							setVariable(workingDivId, 'filter_pattern_recursive', keyword);
						}
						else
						{
							setVariable(workingDivId, 'filter_column', 'label');
							setVariable(workingDivId, 'filter_pattern', keyword);
						}
						reloadAjaxDataTable(workingDivId, true, callbackSuccess);
					}
				);
				
				$(':submit', currentThis)
					.click( function(){ $(this).submit(); })
				;
			}
		);
			
	}
}
// Returns a given Javascript variable associated to the current DIV
function getRequestVariable(workingDivId, name )
{
	// IE fix
	if(!requestVariables[workingDivId]) requestVariables[workingDivId] = new Object;
	
	if(requestVariables[workingDivId][name])
	{
		return requestVariables[workingDivId][name];
	}
	return false;
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
	
	
	$('#'+idToReplace).html( $(content).html());
	
	// we execute the bindDataTableEvent function for the new DIV
	$('#'+idToReplace).each(bindDataTableEvent);
	
	// and we hide the loading DIV
	$('#loadingDataTable', this).hide();
}	

function setImageMinus( currentThis )
{
	$('img',currentThis).attr('src', 'themes/default/images/minus.png');
}
function setImagePlus( currentThis )
{
	$('img',currentThis).attr('src', 'themes/default/images/plus.png');
}


var parentId = '';
var parentAttributeParent = '';

//called when the full table actions is loaded
function actionsDataTableLoaded( response )
{
	var content = $(response);
	var idToReplace = $(content).attr('id');

	$('#'+idToReplace).html(content);
		
	// reset these values when clicking low population include for example
	parentId = '';
	parentAttributeParent = '';
	
	$('#'+idToReplace).each( bindActionDataTableEvent );
}

// Called when a set of rows for a category of actions is loaded
function actionsSubDataTableLoaded( response )
{	
	var idToReplace = $(response).attr('id');
	
	// remove the first row of results which is only used to get the Id
	var response = $(response).filter('tr').slice(1).addClass('rowToProcess');
	
	parentAttributeParent = $('tr#'+idToReplace).prev().attr('parent');
	//alert('parent attr = '+parentAttributeParent);
	$('tr#'+idToReplace).after( response ).remove();
	
	parentId = idToReplace;
	
	re = /subDataTable_(\d+)/;
	ok = re.exec(parentId);
	if(ok)
	{
//		alert('ok = '+ok[1]);
		parentId = ok[1];
	}
	//alert('parent id = '+parentId);
	//alert('id='+idToReplace);
	// we execute the bindDataTableEvent function for the new DIV
	bindActionDataTableEvent();
	
}

function getLevelFromClass( style) 
{
	if (typeof style == "undefined") return 0;
	
	var currentLevelIndex = style.indexOf('level');
	var currentLevel = 0;
	if( currentLevelIndex >= 0)
	{
		currentLevel = Number(style.substr(currentLevelIndex+5,1));
	}
	return currentLevel;
}

function getNextLevelFromClass( style )
{
	if (typeof style == "undefined") return 0;
	currentLevel = getLevelFromClass(style);
	newLevel = currentLevel;
	// if this is not a row to process so 
	if(  style.indexOf('rowToProcess') < 0 )
	{
		newLevel = currentLevel + 1;
	}
	return newLevel;
}

function getWorkingIdActions(currentThis)
{
	var id;
	id = $(currentThis).parents('.parentDivActions').attr('id');
	
	if(id == undefined)
	{
		id = $(currentThis).attr('id');
	}
	
	if(id == undefined)
	{
		//alert(id+' for '+$(currentThis).html() );
	}
	
	return id;
}

var ActionsLoading = new Array;
function onClickActionSubDataTable()
{			
		workingDivId = getWorkingIdActions(this);
		
		// get the idSubTable
		var idSubTable = $(this).attr('id');
	
		var divIdToReplaceWithSubTable = 'subDataTable_'+idSubTable;
		
		var NextStyle = $(this).next().attr('class');
		var CurrentStyle = $(this).attr('class');
		
		var currentRowLevel = getLevelFromClass(CurrentStyle);
		var nextRowLevel = getLevelFromClass(NextStyle);

		// if the row has not been clicked
		// which is the same as saying that the next row level is equal or less than the current row
		// because when we click a row the level of the next rows is higher (level2 row gives level3 rows)
		if(currentRowLevel >= nextRowLevel)
		{
			if( ActionsLoading[idSubTable] )
			{
				return ;
			}
			ActionsLoading[idSubTable] = true;
			var numberOfColumns = $(this).children().length;
			$(this).after( '\
			<tr id="'+divIdToReplaceWithSubTable+'">\
				<td colspan="'+numberOfColumns+'">\
						<span id="loadingDataTable" style="display:inline"><img src="themes/default/images/loading-blue.gif"> Loading...</span>\
				</td>\
			</tr>\
			');
			var savedActionVariable = getRequestVariable(workingDivId,'action');
		
			// reset search for subcategories
			setVariable(workingDivId, 'filter_column', false);
			setVariable(workingDivId, 'filter_pattern', false);
			
			setVariable(workingDivId, 'idSubtable', idSubTable);
			setVariable(workingDivId, 'action', getRequestVariable(workingDivId,'actionToLoadTheSubTable'));
			
			reloadAjaxDataTable(workingDivId, false, actionsSubDataTableLoaded );
			setVariable(workingDivId, 'action', savedActionVariable);
			setVariable(workingDivId, 'idSubtable', false);		
		}
		// else we toggle all these rows
		else
		{
			var plusDetected = $('td img', this).attr('src').indexOf('plus') >= 0;
			
			//alert('look for '+idSubTable);
			$(this).siblings().each( function(){
				if( parents = $(this).attr('parent') )
				{
					//alert('parent = '+ parents);
					if(parents.indexOf(idSubTable) >= 0
						|| parents.indexOf('subDataTable_'+idSubTable) >= 0
					)
					{
						//alert('found');
						if(plusDetected)
							$(this).css('display','');
						else
							$(this).css('display','none');
							
					}
				}
			});
		}
		
		// toggle the image
		var plusDetected = $('td img', this).attr('src').indexOf('plus') >= 0;
		if(plusDetected)
		{
//				$(this).css('font-weight','bold');
			setImageMinus( this );
		}
		else
		{
//				$(this).css('font-weight','normal');
			setImagePlus( this );
		}
}
function bindActionDataTableEvent()
{
	
	ActionsLoading = new Array;
	subTableId = $(this).attr('id');
	
	// define the this to give to the handle search box
	// if the function is called after the page is loaded we use the parents 
	workingDivId = getWorkingIdActions( this );
	
	$('tr.subActionsDataTable.rowToProcess')
		.css('font-weight','bold');

	// we dont display the link on the row with subDataTable when we are already
	// printing all the subTables (case of recursive search when the content is
	// including recursively all the subtables
	if(getRequestVariable(workingDivId, 'filter_pattern_recursive' ) == false)
	{
		$('tr.subActionsDataTable.rowToProcess')
			.click( onClickActionSubDataTable )
			.hover( function() {  
			 	 $(this).css({ cursor: "pointer"}); 
			  	},
			  	function() {  
			 	 $(this).css({ cursor: "auto"}); 
			  	}
	 		)
			;
		
	}
	
	var imagePlusMinusWidth = 12;
	var imagePlusMinusHeight = 12;
	$('tr.subActionsDataTable.rowToProcess td:first-child')
			.each( function(){
					$(this).prepend('<img width="'+imagePlusMinusWidth+'" height="'+imagePlusMinusHeight+'" class="plusMinus" src="" />');
					if(getRequestVariable(workingDivId, 'filter_pattern_recursive' ) != false)
					{					
						setImageMinus(this);	
					}
					else
					{
						setImagePlus(this);
					}
				});
	
	$('tr.rowToProcess')
		.each( function() {
			
			// we add the CSS style depending on the level of the current loading category
			// we look at the style of the parent row 
			var style = $(this).prev().attr('class');
			var currentStyle = $(this).attr('class');
			
			if( (typeof currentStyle != 'undefined')
				&& currentStyle.indexOf('level') >= 0 )
			{
			}
			else
			{
				var level = getNextLevelFromClass( style );
				$(this).addClass('level'+ level);
			}	
			
			// we add an attribute parent that contains the ID of all the parent categories
			// this ID is used when collapsing a parent row, it searches for all children rows
			// which 'parent' attribute's value contains the collapsed row ID 
			$(this).attr('parent', function(){ 
				return parentAttributeParent + ' ' + parentId;
				}
			);
		
			// Add some styles on the cells even/odd
			// label (first column of a data row) or not
			$("td:first-child:odd", this).addClass('label labelodd');
			$("td:first-child:even", this).addClass('label labeleven');
			// we truncate the labels columns from the second row
			$("td:first-child", this).truncate(30);
		    $('.truncated', this).Tooltip();
		})
		.removeClass('rowToProcess')
	;
	
	if( workingDivId != undefined)
	{
		handleSearchBox( workingDivId, this, actionsDataTableLoaded );
		handleLowPopulationLink( workingDivId, this, actionsDataTableLoaded );
	}
}
	
// Set a given JS variable for this DIV
function setVariable( workingDivId, nameVariable, value )
{
	requestVariables[workingDivId][nameVariable] = value;	
}


// Function called to trigger the AJAX request 
// The ajax request contains the function callback to trigger if the request is successful or failed
// displayLoading = false When we don't want to display the Loading... DIV #loadingDataTable
// for example when the script add a Loading... it self and doesn't want to display the generic Loading
function reloadAjaxDataTable( workingDivId, displayLoading, callbackSuccess )
{
//		alert('request ajax');
	if (typeof displayLoading == "undefined") 
	{
    	displayLoading = true;
  	}
  	if (typeof callbackSuccess == "undefined") 
  	{
  		callbackSuccess = dataTableLoaded;
  	}
  	
	if(displayLoading)
	{
		$('#'+workingDivId+' #loadingDataTable').css('display','block');
	}
	var request = getAjaxRequest(workingDivId, callbackSuccess);
	$.ajax(request);
//		alert('request done');
}

// Returns the standard Ajax request object used by the Jquery .ajax method
function getAjaxRequest(workingDivId, callbackSuccess)
{
	
	var ajaxRequest = new Object;

	//prepare the ajax request
	ajaxRequest.type = 'GET';
	ajaxRequest.url = 'index.php';
	ajaxRequest.dataType = 'html';
	ajaxRequest.async = true;
	
	// Callback when the request fails
	ajaxRequest.error = ajaxHandleError;
	
	// Callback when the request succeeds
	ajaxRequest.success = callbackSuccess;
	
	// Here we prepare the GET string to pass to the AJAX request
	// we build it from the values of the requestVariables for this DIV
	// for example if you want to add a parameter in the AJAX request you can do
	// setVariable('myVariable', 42) and it will be given to the PHP script
	var requestVariableAjax = new Object;
	
	$.each(	requestVariables[workingDivId],
			function (name, value)
			{
				//alert(name +'='+value);
				if( typeof(value) != 'boolean' 
					|| value != false )
				{
					requestVariableAjax[name] = value;
				}
				else
				{
//					alert(name +'='+value);
				}
			}
	);
	ajaxRequest.data = requestVariableAjax;
	
	return ajaxRequest;
}

function bindAllDataTableEvent()
{
	// foreach parentDiv which means for each DataTable
	$('.parentDiv').each( bindDataTableEvent );
	$('.parentDivActions').each( bindActionDataTableEvent );
}

function ajaxHandleError()
{
	alert('Error ajax loading!');
}


function toString( object )
{
	$.each(object, function (key,value){ alert(key+' = '+value);  } );
}

