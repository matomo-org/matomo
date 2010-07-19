//-----------------------------------------------------------------------------
//								DataTable
//-----------------------------------------------------------------------------
//A list of all our DataTables
//Test if the object have already been initialized (multiple includes)
if(typeof dataTables == "undefined")
	var dataTables = {};

//DataTable constructor
function dataTable()
{
	this.param = {};
}

//Prototype of the DataTable object
dataTable.prototype =
{
	//initialisation function
	init: function(workingDivId, domElem)
	{
		if(typeof domElem == "undefined")
		{
			domElem = $('#'+workingDivId);
		}
		
		this.workingDivId = workingDivId;
		this.loadedSubDataTable = {};
		this.bindEventsAndApplyStyle(domElem);
		this.initialized = true;
	},
	
	//function triggered when user click on column sort
	onClickSort: function(domElem)
	{
		var self = this;
		var newColumnToSort = $(domElem).attr('id');
		// we lookup if the column to sort was already this one, if it is the case then we switch from desc <-> asc 
		if(self.param.filter_sort_column == newColumnToSort)
		{
			// toggle the sorted order
			if(this.param.filter_sort_order == 'asc')
			{
				self.param.filter_sort_order = 'desc';
			}
			else
			{
				self.param.filter_sort_order = 'asc';
			}
		}
		self.param.filter_offset = 0; 
		self.param.filter_sort_column = newColumnToSort;
		self.reloadAjaxDataTable();
	},
	
	setGraphedColumn: function( columnName )
	{
		this.param.columns = columnName;
	},
	
	//Reset DataTable filters (used before a reload or view change)
	resetAllFilters: function()
	{
		var self = this;
		var FiltersToRestore = new Array();
		filters = [ 
			'filter_column', 
			'filter_pattern', 
			'filter_column_recursive', 
			'filter_pattern_recursive', 
			'enable_filter_excludelowpop',
			'filter_offset',
			'filter_limit',
			'filter_sort_column',
			'filter_sort_order'
		];
		
		for(var key in filters)
		{
			var value = filters[key];
			FiltersToRestore[value] = self.param[value];
			delete self.param[value];
		}
		
		return FiltersToRestore;
	},
	
	//Restores the filters to the values given in the array in parameters
	restoreAllFilters: function(FiltersToRestore)
	{
		var self = this;
		for(key in FiltersToRestore)
		{
			self.param[key] = FiltersToRestore[key];
		}
	},
	
	//Translate string parameters to javascript builtins
	//'true' -> true, 'false' -> false
	//it simplifies condition tests in the code
	cleanParams: function()
	{
		var self = this;
		for(var key in self.param)
		{
			if(self.param[key] == 'true') self.param[key]=true;
			if(self.param[key] == 'false') self.param[key]=false;
		}
	},
		
	// Returns the standard Ajax request object used by the Jquery .ajax method
	buildAjaxRequest: function(callbackSuccess)
	{
		var self = this;
		
		//prepare the ajax request
		var ajaxRequest = 
		{
			type: 'GET',
			url: 'index.php',
			dataType: 'html',
			async: true,
			error: piwikHelper.ajaxHandleError,		// Callback when the request fails
			success: callbackSuccess,	// Callback when the request succeeds
			data: new Object
		};
		
		//Extract the configuration from the datatable and pass it to the API
		for(var key in self.param)
		{
			if(typeof self.param[key] != "undefined")
				ajaxRequest.data[key] = self.param[key];
		}
		
		return ajaxRequest;
	},
	
	// Function called to trigger the AJAX request 
	// The ajax request contains the function callback to trigger if the request is successful or failed
	// displayLoading = false When we don't want to display the Loading... DIV .pk-loadingDataTable
	// for example when the script add a Loading... it self and doesn't want to display the generic Loading
	reloadAjaxDataTable: function(displayLoading, callbackSuccess)
	{
		var self = this;
		
		if (typeof displayLoading == "undefined") 
		{
			displayLoading = true;
		}
		if (typeof callbackSuccess == "undefined") 
		{
			callbackSuccess = self.dataTableLoaded;
		}
		
		if(displayLoading)
		{
			$('#'+self.workingDivId+' .pk-loadingDataTable').last().css('display','block');
		}
		
		$.ajax(self.buildAjaxRequest(callbackSuccess));
	},
			
	
	// Function called when the AJAX request is successful
	// it looks for the ID of the response and replace the very same ID 
	// in the current page with the AJAX response
	dataTableLoaded: function(response)
	{
		var content = $(response);
		var idToReplace = $(content).attr('id');
		var dataTableSel = $('#'+idToReplace);
		
		// if the current dataTable is located inside another datatable
		table = $(content).parents('table.dataTable');
		if(dataTableSel.parents('.dataTable').is('table'))
		{
			// we add class to the table so that we can give a different style to the subtable
			$(content).find('table.dataTable').addClass('subDataTable');
			$(content).find('.dataTableFeatures').addClass('subDataTable');
			
			//we force the initialisation of subdatatables
			dataTableSel.html( $(content) );
		}
		else
		{
			dataTableSel.find('object').remove();
			dataTableSel.html( $(content) );
			piwikHelper.lazyScrollTo(dataTableSel[0], 400);
		}
	},	
		
			
	/* This method is triggered when a new DIV is loaded, which happens
		- at the first loading of the page
		- after any AJAX loading of a DataTable
		
	This method basically add features to the DataTable, 
		- such as column sorting, searching in the rows, displaying Next / Previous links, etc.
		- add styles to the cells and rows (odd / even styles)
		- modify some rows to add images if a span img is found, or add a link if a span urlLink is found
			or truncate the labels when they are too big
		- bind new events onclick / hover / etc. to trigger AJAX requests, 
			nice hovertip boxes for truncated cells
	*/
	bindEventsAndApplyStyle: function(domElem)
	{
		var self = this;
		self.cleanParams();
		self.handleSort(domElem);
		self.handleSearchBox(domElem);
		self.handleLowPopulationLink(domElem);
		self.handleOffsetInformation(domElem);
		self.handleExportBox(domElem);
		self.handleLinkedRows(domElem);
		self.applyCosmetics(domElem);
		self.handleSubDataTable(domElem);
	},
	
	handleLinkedRows: function(domElem)
	{
		var self = this;
		
		var urlLinkFoundDom = $("tr td:first-child:has('.urlLink')", domElem);
		if(urlLinkFoundDom.length == 0)
		{
			self.truncate( $("table tr td:first-child", domElem) );
		}
		else
		{
			urlLinkFoundDom.each( function(){
				// we add a link based on the <span id="urlLink"> present in the column label (the first column)
				// if this span is there, we add the link around the HTML in the TD
				// but we add this link only for the rows that are not clickable already (subDataTable)
				var imgToPrepend = '';
				if( $(this).find('img').length == 0 )
				{
					var imageLinkWidth = 10;
					var imageLinkHeight = 9;
					imgToPrepend = '<img class="link" width="'+imageLinkWidth+'" height="'+imageLinkHeight+'" src="themes/default/images/link.gif" /> ';
				}
				var urlLinkDom = $('.urlLink',this);
				var urlToLink = $(urlLinkDom).html();
				$(urlLinkDom).remove();
				
				var truncationOffsetBecauseImageIsPrepend = -2; //website subtable needs -9. 

				self.truncate( $(this), truncationOffsetBecauseImageIsPrepend );
				if( urlToLink.match("javascript:") )
				{
					$(this).prepend(imgToPrepend).wrapInner('<a href="#" onclick="' + urlToLink.replace("javascript:","") + '"></a>');
				}
				else
				{
					$(this).prepend(imgToPrepend).wrapInner('<a target="_blank" href="' + urlToLink + '"></a>');
				} 	
			});
		}
	},
	
	// if sorting the columns is enabled, when clicking on a column, 
	// - if this column was already the one used for sorting, we revert the order desc<->asc
	// - we send the ajax request with the new sorting information
	handleSort: function(domElem)
	{
		var self = this;
		if( self.param.enable_sort )
		{
			$('.sortable', domElem).click( 
				function()
				{
					$(this).unbind('click');
					self.onClickSort(this);
				}
			);
		
			// are we in a subdatatable?
			var currentIsSubDataTable = $(domElem).parent().hasClass('cellSubDataTable');
			
			var prefixSortIcon = ''; 
			if(currentIsSubDataTable)
			{
				prefixSortIcon = '_subtable_';
			}
			var imageSortWidth = 16;
			var imageSortHeight = 16;
			// we change the style of the column currently used as sort column
			// adding an image and the class columnSorted to the TD
			$(".sortable#"+self.param.filter_sort_column+' #thDIV', domElem).parent()
				.addClass('columnSorted')
				.prepend('<div id="sortIconContainer"><img id="sortIcon" width="'+imageSortWidth+'" height="'+imageSortHeight+'" src="themes/default/images/sort'+prefixSortIcon+ self.param.filter_sort_order+'.png" /></div>');
			
	}
	},
	
	//behaviour for the DataTable 'search box'
	handleSearchBox: function(domElem, callbackSuccess)
	{
		var self = this;
	
		var currentPattern = self.param.filter_pattern;
		if(typeof self.param.filter_pattern != "undefined"
			&& self.param.filter_pattern.length > 0)
		{
			currentPattern = self.param.filter_pattern;
		}
		else if(typeof self.param.filter_pattern_recursive != "undefined"
			&& self.param.filter_pattern_recursive.length > 0)
		{
			currentPattern = self.param.filter_pattern_recursive;
		}
		else
		{
			currentPattern = '';
		}
		
		$('.dataTableSearchPattern', domElem)
			.show()
			.each(function(){
				// when enter is pressed in the input field we submit the form
				$('#keyword', this)
					.bind("keyup", 
						function(e)
						{
							if(isEnterKey(e))
							{ 
								$(this).siblings(':submit').submit(); 
							} 
						} 
					)
					.val(currentPattern)
				;
				
				$(':submit', this).submit( 
					function()
					{
						var keyword = $(this).siblings('#keyword').val();
						self.param.filter_offset = 0; 
						
						if(self.param.search_recursive)
						{
							self.param.filter_column_recursive = 'label';
							self.param.filter_pattern_recursive = keyword;
						}
						else
						{
							self.param.filter_column = 'label';
							self.param.filter_pattern = keyword;
						}
						self.reloadAjaxDataTable(true, callbackSuccess);
					}
				);
				
				$(':submit', this)
					.click( function(){ $(this).submit(); })
				;

				// in the case there is a searched keyword we display the RESET image
				if(currentPattern)
				{
					var target = this;
					var clearImg = $('<span style="position: relative;">\
							<img src="plugins/CoreHome/templates/images/reset_search.png" style="position: absolute; top: 4px; left: -15px; cursor: pointer; display: inline;" title="Clear" />\
							</span>')
						.click( function() {
							$('#keyword', target).val('');
							$(':submit', target).submit();
						});
					$('#keyword',this).after(clearImg);
					
				}
			}
		);
	},
	
	//behaviour for '< prev' 'next >' links and page count
	handleOffsetInformation: function(domElem)
	{
		var self = this;
		
		$('.dataTablePages', domElem).each(
			function(){
				var offset = 1+Number(self.param.filter_offset);
				var offsetEnd = Number(self.param.filter_offset) + Number(self.param.filter_limit);
				var totalRows = Number(self.param.totalRows);
				offsetEndDisp = offsetEnd;

				if(offsetEnd > totalRows) offsetEndDisp = totalRows;
				
				// only show this string if there is some rows in the datatable
				if(totalRows != 0)
				{
					var str = sprintf(_pk_translate('CoreHome_PageOf_js'),offset + '-' + offsetEndDisp,totalRows);
					$(this).text(str);
				}
			}
		);
		
		// Display the next link if the total Rows is greater than the current end row
		$('.dataTableNext', domElem)
			.each(function(){
				var offsetEnd = Number(self.param.filter_offset) 
									+ Number(self.param.filter_limit);
				var totalRows = Number(self.param.totalRows);
				if(offsetEnd < totalRows)
				{
					$(this).css('display','inline');
				}
			})
			// bind the click event to trigger the ajax request with the new offset
			.click(function(){
				$(this).unbind('click');
				self.param.filter_offset = Number(self.param.filter_offset) + Number(self.param.filter_limit); 
				self.reloadAjaxDataTable();
			})
		;
		
		// Display the previous link if the current offset is not zero
		$('.dataTablePrevious', domElem)
			.each(function(){
					var offset = 1+Number(self.param.filter_offset);
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
					$(this).unbind('click');
					var offset = Number(self.param.filter_offset) - Number(self.param.filter_limit);
					if(offset < 0) { offset = 0; }
					self.param.filter_offset = offset; 
					self.reloadAjaxDataTable();
				}
			);
	},

	
	// DataTable view box (simple table, all columns table, Goals table, pie graph, tag cloud, graph, ...)
	handleExportBox: function(domElem)
	{
		var self = this;
		if( self.param.idSubtable )
		{
			// no view box for subtables
			return;
		}
		
		// When the (+) image is hovered, the export buttons are displayed 
		$('.dataTableFooterIconsShow', domElem)
			.show()
			.hover( function() {
					$(this).fadeOut('slow');
					$('.exportToFormatIcons', $(this).parent()).show('slow');
				}, function(){}
		);
		
		//footer arrow position element name
		self.jsViewDataTable=$('.dataTableFooterWrap', domElem).attr('var');
		
		$('.tableAllColumnsSwitch a', domElem)
			.show()
			.click(
				function(){
					// we only reset the limit filter, in case switch to table view from cloud view where limit is custom set to 30
					// this value is stored in config file General->datatable_default_limit but this is more an edge case so ok to set it to 10
					
					self.setActiveIcon(this, domElem);
					
					var viewDataTable = $(this).attr('format');
					self.param.viewDataTable = viewDataTable;
					
					//self.resetAllFilters();
					
					
					// when switching to display simple table, do not exclude low pop by default
					delete self.param.enable_filter_excludelowpop; 
					delete self.param.filter_limit;
					self.reloadAjaxDataTable();
					self.notifyDashboardViewDataTableChange($(this), self.param.viewDataTable);
				}
			)
		
		//handle Graph View icons
		$('.tableGraphViews a', domElem)
			.click(function(){
				var viewDataTable = $(this).attr('format');
				self.setActiveIcon(this, domElem);
				self.resetAllFilters();
				self.param.viewDataTable = viewDataTable;
				self.reloadAjaxDataTable();
				self.notifyDashboardViewDataTableChange($(this), self.param.viewDataTable);
			});
		
		//Graph icon Collapsed functionality
		self.currentGraphViewIcon=0;
		self.graphViewEnabled=0;
		self.graphViewStartingThreads=0;
		self.graphViewStartingKeep=false; //show keep flag
		
		//define collapsed icons
		$('.tableGraphCollapsed a', domElem)
			.each(function(i){
				if(self.jsViewDataTable==$(this).attr('var')){
					self.currentGraphViewIcon=i;
					self.graphViewEnabled=true;
				}
			})
			.each(function(i){
				if(self.currentGraphViewIcon!=i) $(this).hide();
			});
		
		$('.tableGraphCollapsed', domElem).hover(
			function(){
				//Graph icon onmouseover
				if(self.graphViewStartingThreads>0) return self.graphViewStartingKeep=true; //exit if animation is not finished
				$(this).addClass('tableIconsGroupActive');
				$('a', this).each(function(i){
					if(self.currentGraphViewIcon!=i || self.graphViewEnabled){
						self.graphViewStartingThreads++;
					}
					if(self.currentGraphViewIcon!=i){
						//show other icons
						$(this).show('fast', function(){self.graphViewStartingThreads--});
					}
					else if (self.graphViewEnabled){
						//set footer arrow position
						$('.dataTableFooterActiveItem', domElem).animate({left:$(this).parent().position().left+i*(this.offsetWidth+1)}, "fast", function(){self.graphViewStartingThreads--});
					}
				});
				self.exportToFormatHide(domElem);
			},
			function(){
				//Graph icon onmouseout
				if(self.graphViewStartingKeep) return self.graphViewStartingKeep=false; //exit while icons animate
				$('a', this).each(function(i){
					if(self.currentGraphViewIcon!=i){
						//hide other icons
						$(this).hide('fast');
					}
					else if (self.graphViewEnabled){
						//set footer arrow position
						$('.dataTableFooterActiveItem', domElem).animate({left:$(this).parent().position().left}, "fast");
					}
				});
				$(this).removeClass('tableIconsGroupActive');
			}
		);
		
		//handle exportToFormat icons
		self.exportToFormat=null;
		$('.exportToFormatIcons a', domElem).click(function(){
			self.exportToFormat={};
			self.exportToFormat.lastActiveIcon=self.setActiveIcon(this, domElem);
			self.exportToFormat.target=$(this).parent().siblings('.exportToFormatItems').show('fast');
			self.exportToFormat.obj=$(this).hide();
		});
		
		//close exportToFormat onClickOutside
		$('body').bind('mouseup',function(e){
				if(self.exportToFormat){
					self.exportToFormatHide(domElem);
				}
		});
		
		
		$('.exportToFormatItems a', domElem).attr( 'href', function(){
				var format = $(this).attr('format');
				var method = $(this).attr('methodToCall');
				var filter_limit = $(this).attr('filter_limit');
				
				var param_date = self.param.date;
				var date = $(this).attr('date');
				if(typeof date != 'undefined') {
					param_date = date; 
				}
				var str = 'index.php?module=API'
						+'&method='+method
						+'&format='+format
						+'&idSite='+self.param.idSite
						+'&period='+self.param.period
						+'&date='+param_date
						+'&token_auth='+piwik.token_auth;
				if( filter_limit )
				{
					str += '&filter_limit=' + filter_limit;
				}
				return str;
			}
		);
		
		
		$('.dataTableFooterWrap a.tableIcon', domElem).each(function(){
			if(self.jsViewDataTable==$(this).attr('var')) self.setActiveIcon(this, domElem); 
		});
		
	},	
	
	exportToFormatHide: function(domElem)
	{
		var self=this;
		if(self.exportToFormat){
			self.setActiveIcon(self.exportToFormat.lastActiveIcon, domElem);
			self.exportToFormat.target.hide('fast');
			self.exportToFormat.obj.show('fast');
			self.exportToFormat=null;
		}
	},
	
	
	// Add behaviour to the low population link
	handleLowPopulationLink: function(domElem, callbackSuccess)
	{
		var self = this;
		
		// Set the string for the DIV, either "Exclude low pop" or "Include all"
		$('.dataTableExcludeLowPopulation', domElem)
			.each(
				function()
				{
					if(typeof self.param.enable_filter_excludelowpop == 'undefined')
					{
						self.param.enable_filter_excludelowpop = 0;
					}
					if(Number(self.param.enable_filter_excludelowpop) != 0)
					{
						string = _pk_translate('CoreHome_IncludeAllPopulation_js');
						self.param.enable_filter_excludelowpop = 1;
					}
					else
					{
						string = _pk_translate('CoreHome_ExcludeLowPopulation_js');
						self.param.enable_filter_excludelowpop = 0;
					}
					$(this).html(string);
				} 
			)
			// Bind a click event to the DIV that triggers the ajax request
			.click(
				function()
				{
					self.param.enable_filter_excludelowpop = 1 - self.param.enable_filter_excludelowpop;
					self.param.filter_offset = 0;
					self.reloadAjaxDataTable(true, callbackSuccess);
				}
			);
	},
	
	//footer arrow position handler
	setActiveIcon: function(obj, domElem)
	{	
		if(!obj) return false;
		
		var lastActiveIcon=this.lastActiveIcon;
		
		if(lastActiveIcon){
			$(lastActiveIcon).removeClass("activeIcon");
		}
		
		$(obj).addClass("activeIcon");
		this.lastActiveIcon=obj;
		
		var target=$('.dataTableFooterActiveItem', domElem);
		
		if(obj.offsetWidth){
			//set arrow position
			target.css({left:$(obj).position().left});
		}
		else{
			//set arrow position with delay (for ajax widget loading)
			setTimeout(function(){
				target.css({left:$(obj).position().left});
			},100);
		}
		
		return lastActiveIcon;
		
	},
	
	// Tell dashboard that the ViewDataTable of this table was updated,
	// Dashboard will records the new View type in the layout and restore it next reload
	notifyDashboardViewDataTableChange: function(domWidget, newViewDataTable)
	{
		if(piwik.dashboardObject)
		{
			widgetUniqueId = $(domWidget).parents('.widget').attr('id');
			piwik.dashboardObject.setDataTableViewChanged(widgetUniqueId, newViewDataTable);
		}
	},
	
	truncate: function(domElemToTruncate, truncationOffset)
	{
		var self = this;
		
		if(typeof truncationOffset == 'undefined') {
			truncationOffset = 0;
		}
		var truncationLimit = 30;
		// in a subtable
		if(typeof self.param.idSubtable != 'undefined')
		{
			truncationLimit = 25;
		}
		// when showing all columns
		if(typeof self.param.idSubtable == 'undefined'
			&& self.param.viewDataTable == 'tableAllColumns')
		{
			truncationLimit = 17;
		}
		// when showing all columns in a subtable, space is restricted
		else if(self.param.viewDataTable == 'tableAllColumns')
		{
			truncationLimit = 10;
		}
		
		truncationLimit += truncationOffset;

		$(domElemToTruncate).truncate(truncationLimit);
		$('.truncated', domElemToTruncate)
			.tooltip();
	},

	//Apply some miscelleaneous style to the DataTable
	applyCosmetics: function(domElem)
	{
		var self = this;

		// Add some styles on the cells even/odd
		// label (first column of a data row) or not
		$("th:first-child", domElem).addClass('label');
		$("td:first-child:odd", domElem).addClass('label labeleven');
		$("td:first-child:even", domElem).addClass('label labelodd');
		$("tr:odd td", domElem).slice(1).addClass('columnodd');
		$("tr:even td", domElem).slice(1).addClass('columneven');
		
	},
 	
 	//behaviour for 'nested DataTable' (DataTable loaded on a click on a row)
 	handleSubDataTable: function(domElem)
	{
		var self = this;
		// When the TR has a subDataTable class it means that this row has a link to a subDataTable
		$('tr.subDataTable', domElem)
			.click( 
			function()
			{
				// get the idSubTable
				var idSubTable = $(this).attr('id');
				var divIdToReplaceWithSubTable = 'subDataTable_'+idSubTable;
				
				// if the subDataTable is not already loaded
				if (typeof self.loadedSubDataTable[divIdToReplaceWithSubTable] == "undefined")
				{
					var numberOfColumns = $(this).children().length;
					
					// at the end of the query it will replace the ID matching the new HTML table #ID
					// we need to create this ID first
					$(this).after( 
					'<tr>'+
						'<td colspan="'+numberOfColumns+'" class="cellSubDataTable">'+
							'<div id="'+divIdToReplaceWithSubTable+'">'+
								'<span class="pk-loadingDataTable" style="display:inline"><img src="themes/default/images/loading-blue.gif" />'+ _pk_translate('CoreHome_Loading_js') +'</span>'+
							'</div>'+
						'</td>'+
					'</tr>'
					);
					
					var savedActionVariable = self.param.action;
					
					// reset all the filters from the Parent table
					var filtersToRestore = self.resetAllFilters();
					
					self.param.idSubtable = idSubTable;
					self.param.action = self.param.controllerActionCalledWhenRequestSubTable;
					self.reloadAjaxDataTable(false);
					
					self.param.action = savedActionVariable;
					delete self.param.idSubtable;
					self.restoreAllFilters(filtersToRestore);
					
					self.loadedSubDataTable[divIdToReplaceWithSubTable] = true;
					
					$(this).next().toggle();
				}
				
				$(this).next().toggle();
			} 
		);
	}
};






//-----------------------------------------------------------------------------
//								Action Data Table
//-----------------------------------------------------------------------------

//inheritance declaration
//actionDataTable is a child of dataTable
actionDataTable.prototype = new dataTable;
actionDataTable.prototype.constructor = actionDataTable;

//actionDataTable constructor
function actionDataTable()
{
	dataTable.call(this);
	this.parentAttributeParent = '';
	this.parentId = '';
	this.disabledRowDom = {};	//to handle double click on '+' row
}

//Prototype of the actionDataTable object
actionDataTable.prototype =
{	
	//method inheritance
	cleanParams: dataTable.prototype.cleanParams,
	reloadAjaxDataTable: dataTable.prototype.reloadAjaxDataTable,
	buildAjaxRequest: dataTable.prototype.buildAjaxRequest,
	handleLowPopulationLink: dataTable.prototype.handleLowPopulationLink,
	handleSearchBox: dataTable.prototype.handleSearchBox,
	handleExportBox: dataTable.prototype.handleExportBox,
	handleSort: dataTable.prototype.handleSort,
	onClickSort: dataTable.prototype.onClickSort,
	handleLinkedRows: dataTable.prototype.handleLinkedRows,
	truncate: dataTable.prototype.truncate,
	handleOffsetInformation: dataTable.prototype.handleOffsetInformation,
	setActiveIcon: dataTable.prototype.setActiveIcon,
	
	//initialisation of the actionDataTable
	init: function(workingDivId, domElem)
	{
		if(typeof domElem == "undefined")
		{
			domElem = $('#'+workingDivId);
		}
		this.workingDivId = workingDivId;
		this.bindEventsAndApplyStyle(domElem);
		this.initialized = true;
	},

	//see dataTable::bindEventsAndApplyStyle
	bindEventsAndApplyStyle: function(domElem)
	{
		var self = this;
		
		self.cleanParams();
		
		// we dont display the link on the row with subDataTable when we are already
		// printing all the subTables (case of recursive search when the content is
		// including recursively all the subtables
		if(!self.param.filter_pattern_recursive)
		{
			$('tr.subActionsDataTable.rowToProcess')
				.click( function()
				{
					self.onClickActionSubDataTable(this)
				});
		}
		
		self.applyCosmetics(domElem);
		
		self.handleExportBox(domElem);
		self.handleSort(domElem);
		self.handleLinkedRows(domElem);
		self.handleOffsetInformation(domElem);
		if( self.workingDivId != undefined)
		{
			self.handleSearchBox(domElem, self.dataTableLoaded );
			self.handleLowPopulationLink(domElem, self.dataTableLoaded );
		}
	},
	
	//see dataTable::applyCosmetics
	applyCosmetics: function(domElem)
	{
		var self = this;
		
		$('tr.subActionsDataTable.rowToProcess')
		.css('font-weight','bold');			
			
		$("th:first-child", domElem).addClass('label');
		
		var imagePlusMinusWidth = 12;
		var imagePlusMinusHeight = 12;
		$('tr.subActionsDataTable.rowToProcess td:first-child')
				.each( function(){
						$(this).prepend('<img width="'+imagePlusMinusWidth+'" height="'+imagePlusMinusHeight+'" class="plusMinus" src="" />');
						if(self.param.filter_pattern_recursive)
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
					return self.parentAttributeParent + ' ' + self.parentId;
					}
				);
			
				// Add some styles on the cells even/odd
				// label (first column of a data row) or not
				$("td:first-child:odd", this).addClass('label labeleven');
				$("td:first-child:even", this).addClass('label labelodd');
				// we truncate the labels columns from the second row
//				$("td:first-child", this).truncate(30);
//			    $('.truncated', this).tooltip();
			})
			.removeClass('rowToProcess');
	},
	
	// Called when the user click on an actionDataTable row
	onClickActionSubDataTable: function(domElem)
	{
		var self = this;
				
		// get the idSubTable
		var idSubTable = $(domElem).attr('id');
	
		var divIdToReplaceWithSubTable = 'subDataTable_'+idSubTable;
		
		var NextStyle = $(domElem).next().attr('class');
		var CurrentStyle = $(domElem).attr('class');
		
		var currentRowLevel = getLevelFromClass(CurrentStyle);
		var nextRowLevel = getLevelFromClass(NextStyle);

		// if the row has not been clicked
		// which is the same as saying that the next row level is equal or less than the current row
		// because when we click a row the level of the next rows is higher (level2 row gives level3 rows)
		if(currentRowLevel >= nextRowLevel)
		{
			//unbind click to avoid double click problem
			$(domElem).unbind('click');
			self.disabledRowDom = $(domElem);
			
			var numberOfColumns = $(domElem).children().length;
			$(domElem).after( '\
			<tr id="'+divIdToReplaceWithSubTable+'" class="cellSubDataTable">\
				<td colspan="'+numberOfColumns+'">\
						<span class="pk-loadingDataTable" style="display:inline"><img src="themes/default/images/loading-blue.gif" /> Loading...</span>\
				</td>\
			</tr>\
			');
			var savedActionVariable = self.param.action;
		
			// reset search for subcategories
			delete self.param.filter_column;
			delete self.param.filter_pattern;
			
			self.param.idSubtable = idSubTable;
			self.param.action = self.param.controllerActionCalledWhenRequestSubTable;
			
			self.reloadAjaxDataTable(false, function(resp){self.actionsSubDataTableLoaded(resp)});
			self.param.action = savedActionVariable;
			delete self.param.idSubtable;		
		}
		// else we toggle all these rows
		else
		{
			var plusDetected = $('td img', domElem).attr('src').indexOf('plus') >= 0;
			
			$(domElem).siblings().each( function(){
				var parents = $(this).attr('parent');
				if(parents)
				{
					if(parents.indexOf(idSubTable) >= 0 
						|| parents.indexOf('subDataTable_'+idSubTable) >= 0)
					{
						if(plusDetected)
						{
							$(this).css('display','');
								
							//unroll everything and display '-' sign
							//if the row is already opened	
							var NextStyle = $(this).next().attr('class');
							var CurrentStyle = $(this).attr('class');
		
							var currentRowLevel = getLevelFromClass(CurrentStyle);
							var nextRowLevel = getLevelFromClass(NextStyle);

							if(currentRowLevel < nextRowLevel)
								setImageMinus(this);
						}
						else
						{
							$(this).css('display','none');
						}
					}
				}
			});
		}
		
		// toggle the +/- image
		var plusDetected = $('td img', domElem).attr('src').indexOf('plus') >= 0;
		if(plusDetected)
		{
			setImageMinus(domElem);
		}
		else
		{
			setImagePlus(domElem);
		}
	},
	
	//called when the full table actions is loaded
	dataTableLoaded: function(response)
	{
		var content = $(response);
		var idToReplace = $(content).attr('id');		
		
		//reset parents id
		self.parentAttributeParent = '';
		self.parentId = '';
	
		var dataTableSel = $('#'+idToReplace);
		dataTableSel.html( $(content) );
		piwikHelper.lazyScrollTo(dataTableSel[0], 400);
	},
	
	// Called when a set of rows for a category of actions is loaded
	actionsSubDataTableLoaded: function(response)
	{	
		var self = this;
		var idToReplace = $(response).attr('id');
		
		// remove the first row of results which is only used to get the Id
		var response = $(response).filter('tr').slice(1).addClass('rowToProcess');
		self.parentAttributeParent = $('tr#'+idToReplace).prev().attr('parent');
		self.parentId = idToReplace;
		
		$('tr#'+idToReplace).after( response ).remove();
			
		var re = /subDataTable_(\d+)/;
		ok = re.exec(self.parentId);
		if(ok)
		{
			self.parentId = ok[1];
		}
		
		// we execute the bindDataTableEvent function for the new DIV
		self.init(self.workingDivId, $('#'+self.workingDivId));
//		self.init(self.workingDivId, $('#'+idToReplace));
		
		//bind back the click event (disabled to avoid double-click problem)
		self.disabledRowDom.click(
			function()
			{
				self.onClickActionSubDataTable(this)
			});
	}
};

//helper function for actionDataTable
function getLevelFromClass( style) 
{
	if (!style || typeof style == "undefined") return 0;
	
	var currentLevelIndex = style.indexOf('level');
	var currentLevel = 0;
	if( currentLevelIndex >= 0)
	{
		currentLevel = Number(style.substr(currentLevelIndex+5,1));
	}
	return currentLevel;
}

//helper function for actionDataTable
function getNextLevelFromClass( style )
{
	if (!style || typeof style == "undefined") return 0;
	currentLevel = getLevelFromClass(style);
	newLevel = currentLevel;
	// if this is not a row to process so 
	if(  style.indexOf('rowToProcess') < 0 )
	{
		newLevel = currentLevel + 1;
	}
	return newLevel;
}

//helper function for actionDataTable
function setImageMinus( domElem )
{
	$('img',domElem).attr('src', 'themes/default/images/minus.png');
}

//helper function for actionDataTable
function setImagePlus( domElem )
{
	$('img',domElem).attr('src', 'themes/default/images/plus.png');
}

