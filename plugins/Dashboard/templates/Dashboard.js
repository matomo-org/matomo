

//label and string used in the javascript
//overide this object for dataTable_translation
if(typeof dashboard_translation == "undefined")
{
	var dashboard_translation = {
		titleWidgetInDashboard: 	'Widget already in dashboard',
		titleClickToAdd: 			'Click to add to dashboard',
		loadingPreview: 			'Loading preview, please wait...',
		loadingWidget: 				'Loading widget, please wait...',
		widgetNotFound: 			'Widget not found'
	};
}

//there is a problem with loop for-in when we extends javascript Array
//so I prefer using a separate function to do this
function contains(array, searchElem) {
	for(var i=0; i<array.length; i++) {
		if (array[i]==searchElem) {
			return true;
		}
	}
	return false;
}


function blockUIConfig()
{
	//set default value for blockUI
	$.extend($.blockUI.defaults.overlayCSS, { backgroundColor: '#000000', opacity: '0.4'});
	$.extend($.blockUI.defaults,{ fadeIn: 0, fadeOut: 0 });

	//unblock UI on 'escape' key pressed...
	$(window).keydown(
		function(e)
		{
			var key = e.keyCode || e.which;
			if(key == 27) //escape key ascii code
				$.unblockUI();
		}
	);
}
		
		
//widgetMenu constructor
function widgetMenu(dash)
{
	this.menu = new Object;
	this.dashboard = dash;
}
	
//Prototype of the widgetMenu object
widgetMenu.prototype =
{
	init: function()
	{
		var self = this;
		self.buildMenu();
		$('.button#addWidget').click(function(){self.show();});
	},
	
	initBuilt: function(menuDom)
	{
		var self = this;
		self.menu = menuDom;
		self.bindEvents();
	},
	
	buildMenu: function()
	{
		var self = this;
		
		//load menu widgets list
		self.menu = $('.menu#widgetChooser');		
		var subMenu1 = $('.subMenu#sub1', self.menu);
		var subMenu2 = $('.subMenu#sub2', self.menu);
		
		subMenu1.append('<ol id="menuList"></ol>');
		subMenu2.append('<ul id="widgetList"></ul>');
		var lineHeight = $('ol', subMenu1).css('line-height');
		lineHeight = Number(lineHeight.substring(0, lineHeight.length-2));
	
		var count=0;
		for(var plugin in piwik.availableWidgets)
		{
			var widgets = piwik.availableWidgets[plugin];
	
			for(var i in widgets)
			{
				var exist = $('.subMenuItem#'+plugin, subMenu1);
				if(exist.size()==0)
				{
					$('ol', subMenu1).append('<li class="subMenuItem" id="'+plugin+'"><span>'+plugin+'</span></li>');
					$('ul', subMenu2).append('<li class="subMenuItem" id="'+plugin+'"></li>');
				}
				
				var sm2Div = $('.subMenuItem#'+plugin, subMenu2);
				sm2Div.append('<div class="button menuItem" pluginToLoad="'+plugin+'" actionToLoad="'+widgets[i][1]+'">'+widgets[i][0] + '</div>');
				sm2Div.css('padding-top', count*lineHeight+'px');
			}
			count++;
		}
		
		$('.subMenuItem', subMenu2).hide();
	},
	
	bindEvents: function()
	{
		var self = this;
		
		//menu buttons
		$('.button#hideMenu', self.menu).click(function(){self.hide();});
		$('#closeMenuIcon', self.menu).click(function(){self.hide();});
		$('.subMenu#sub3 .widget .handle', self.menu).css('cursor', 'pointer')
				.click(function(){self.movePreviewToDashboard();});
		
		$('.subMenu#sub1 .subMenuItem', self.menu).each(function(){
			var plugin = $(this).attr('id');
			var item = $('.subMenu#sub2 .subMenuItem#'+plugin, self.menu);
			
			$(this).hover(
				function()
				{
					$('.widgetDiv.previewDiv', self.menu).empty()
													.attr('plugin', '')
													.attr('id', '');
					$('.menuItem', self.menu).removeClass('menuSelected');
					$('.subMenu#sub1 .subMenuItem', self.menu).removeClass('menuSelected');
					$('.subMenu#sub2 .subMenuItem', self.menu).hide();
					$(this).addClass('menuSelected');
					item.show();
				},function(){});
		});
	
		$('.menuItem', self.menu).hover(
		function()
		{
			if(!$(this).hasClass('menuDisabled'))
			{
				var plugin = $(this).attr('pluginToLoad');
				var action = $(this).attr('actionToLoad');
				
				$('.menuSelected', self.menu).removeClass('menuSelected');
				$(this).addClass('menuSelected');
				
				$('.widgetDiv.previewDiv', self.menu).each(function(){
					//only reload preview if necessary
					if($(this).attr('plugin')!=plugin || $(this).attr('id')!=action)
					{
						//format the div for upcomming ajax loading and set a temporary content
						$(this)	.attr('plugin', plugin)
								.attr('id', action)
								.html('<div id="previewLoading"><img src="themes/default/loading.gif" />'+ dashboard_translation.loadingPreview +'</div>').show();
						self.dashboard.ajaxLoading(plugin, action);
					}
				});
			}
		},function(){})
		.click(function(){	self.movePreviewToDashboard(); });
	},
	
	hide: function()
	{
		$.unblockUI();
	},
	
	show: function()
	{
		var self = this;
		self.filterOutAlreadyLoadedWidget();
		var dispMenu = $('.menu#widgetChooser').clone(true);
		$.blockUI(dispMenu, {width:'', top: '5%',left:'10%', right:'10%', margin:"0px", textAlign:'', cursor:'', border:'0px'});
		
		var dispMenuObject = new widgetMenu(self.dashboard);
		dispMenuObject.initBuilt($('.blockMsg .menu#widgetChooser')); 
	},

	//disable widgets that are already in the dashboard
	filterOutAlreadyLoadedWidget: function()
	{
		var self = this;
		
		//list loaded widget:
		var widgets = new Array;	
		self.dashboard.getColumns().each(
			function()
			{
				widgets = widgets.concat(getWidgetInDom(this));
			}
		);
		
		$('.menuItem', self.menu).each(function(){
			var plugin = $(this).attr('pluginToLoad');
			var action = $(this).attr('actionToLoad');
			if(contains(widgets, plugin+'.'+action))
			{
				$(this).addClass('menuDisabled');
				$(this).attr('title', dashboard_translation.titleWidgetInDashboard);
			}
			else
			{
				$(this).removeClass('menuDisabled');
				$(this).attr('title', dashboard_translation.titleClickToAdd);
			}
		});
	},
	
	movePreviewToDashboard: function()
	{
		var self = this;
		
		$('.widgetDiv.previewDiv', self.menu).each(function(){
			var plugin = $(this).attr('plugin');
			var action = $(this).attr('id');
			
			self.dashboard.addEmptyWidget(1, plugin, action, true);
			
			var parDiv = $('.widgetDiv[plugin='+plugin+']'+'#'+action, self.dashboard.getColumns()[0]);
			parDiv.show();
			parDiv.siblings('.widgetLoading').hide();
			
			$(this).children().clone(true).appendTo(parDiv);
		});
		
		self.hide();
		self.clearPreviewDiv();
		self.dashboard.saveLayout();
	},
	
	clearPreviewDiv: function()
	{
		var self = this;
		$('.subMenu .widgetDiv.previewDiv', self.menu).empty()
			.attr('id', '')
			.attr('plugin', '');
	}
};


//dashboard constructor
function dashboard()
{
	this.test = new Object;
	this.dashArea = new Object;
	this.dashColumns = new Object;
	this.layout = '';
}
	
//Prototype of the dashboard object
dashboard.prototype =
{
	init: function(layout)
	{
		var self = this;
		
		self.dashArea = $('#dashboardWidgetsArea');
		self.dashColumns = $('.col', self.dashDom);
		self.layout = layout;
		
		//generate dashboard layout and load every displayed widgets
		self.generateLayout();

		//setup widget dynamic behaviour
		self.setupWidgetSortable();
	},
	
	getColumns: function()
	{
		return this.dashColumns;
	},
	
	setupWidgetSortable: function()
	{
		var self = this;
		
		//add a dummy item on each columns
		self.dashColumns.each(
			function()
			{
				$(this).append('<div class="items dummyItem"><div class="handle dummyHandle"></div></div>');
			});
		 
		self.hideUnnecessaryDummies();
		 
		self.makeSortable();
	},
	
	generateLayout: function()
	{
		var self = this;
		
		//dashboardLayout looks like :
		//'Actions.getActions~Actions.getDownloads|UserCountry.getCountry|Referers.getSearchEngines';
		//'|' separate columns
		//'~' separate widgets
		//'.' separate plugin name from action name
		var col = self.layout.split('|');
		for(var i=0; i<col.length; i++)
		{
			if(col[i] != '')
			{
				var widgets = col[i].split('~');
				for(var j=0; j<widgets.length; j++)
				{
					var wid = widgets[j].split('.');
					self.addWidgetAndLoad(i+1, wid[0], wid[1]);
	  			}
	  		}
		}
	},
	
	addEmptyWidget: function(colNumber, plugin, action, onTop)
	{
		var self = this;
		
		if(typeof onTop == "undefined")
			onTop = false;
		
		var item = '<div class="items"><div class="widget"><div class="widgetLoading">'+dashboard_translation.loadingWidget+'</div><div plugin="'+plugin+'"'+' id="'+action+'" class="widgetDiv"></div></div></div>';
	    
	    if(onTop)
	    {
	   		$(self.dashColumns[colNumber-1]).prepend(item);
	    }
	    else
	    {
	   		$(self.dashColumns[colNumber-1]).append(item);
	   	}
	   	
	   	//find the title of the widget
		var title = self.getWidgetTitle(plugin, action);
		
		//add an handle to each items
		var widget = $('.widgetDiv#'+action+'[plugin='+plugin+']', self.dashColumns[colNumber-1]).parents('.widget');
		self.addHandleToWidget(widget, title);
		
	    var button = $('.button#close', widget);
		
		//Only show handle buttons on mouse hover
		$(widget).hover(
			function()
			{
				$(this).addClass('widgetHover');
				$('.handle',this).addClass('handleHover');
				button.show();
			},
			function()
			{
				$(this).removeClass('widgetHover');
				$('.handle',this).removeClass('handleHover');
				button.hide();
			}
		);
		
		//Bind click event on close button
		button.click(function(ev){self.onDeleteItem(this, ev);});
		
		self.makeSortable();
	},
	
	getWidgetTitle: function(plugin, action)
	{
		var self = this;
		
		var title = dashboard_translation.widgetNotFound;
		var widgets = piwik.availableWidgets[plugin];
		for(var i in widgets)
		{
			if(action == widgets[i][1])
				title = widgets[i][0];
		}
		return title;
	},
	
	addWidgetAndLoad: function(colNumber, plugin, action, onTop)
	{
		var self = this;
		
		self.addEmptyWidget(colNumber, plugin, action, onTop);
	    self.loadItem($('.items [plugin='+plugin+']#'+action, self.dashArea).parents('.items'));
	},
	
	addHandleToWidget: function(widget, title)
	{
		widget.prepend('<div class="handle">\
							<div class="button" id="close">\
								<img src="themes/default/images/close.png" />\
							</div>\
							<div class="widgetTitle">'+title+'</div>\
						</div>');
	},

	loadItem: function(domElem)
	{	
		var self = this;
		
		//load every widgetDiv with asynchronous ajax
		$('.widgetDiv', domElem).each(
			function()
			{				
				// get the ID of the div and load with ajax						
				self.ajaxLoading($(this).attr('plugin'), $(this).attr('id'));
			});
	},
	
	makeSortable: function()
	{
		var self = this;
		
		function getHelper()
		{
			return $(this).clone().addClass('helper');
		}
		
		function onStart()
		{
			self.showDummies();
		}
		
		function onStop()
		{
			self.hideUnnecessaryDummies();
			self.saveLayout();
			
			$('.widgetHover', this).removeClass('widgetHover');
			$('.handleHover', this).removeClass('handleHover');
			$('.button#close', this).hide();
		}
	
		//launch 'sortable' property on every dashboard widgets
		self.dashArea.sortableDestroy()
					 .sortable({
					 	items:'.items',
					 	hoverClass: 'hover',
					 	handle: '.handle',
					 	helper: getHelper,
					 	start: onStart,
					 	stop: onStop
					 	});
	},

	onDeleteItem: function(target, ev)
	{
		var self = this;
		   
		//ask confirmation and delete item
		var question = $('.dialog#confirm').clone();
		$('#yes', question).click(function()
		{
			var item = $(target).parents('.items');
			var plugin = $('.widgetDiv', item).attr('plugin');
			var action = $('.widgetDiv', item).attr('id');
			
			//hide confirmation dialog
			$.unblockUI();
			
			//the item disapear slowly and is removed from the DOM
			item.fadeOut(200, function()
				{
					$(this).remove();
					self.showDummies();
					self.saveLayout();
					self.makeSortable();
				});
			
			//show menu item
			$('.menu#widgetChooser .menuItem[pluginToLoad='+plugin+'][actionToLoad='+action+']').show();
		});
		$('#no', question).click($.unblockUI);
		$.blockUI(question, { width: '300px', border:'1px solid black' }); 
	},
	
	showDummies: function()
	{
		var self = this;
		$('.dummyItem').css('display', 'block');
	},
	
	hideUnnecessaryDummies: function()
	{
		var self = this;
		$('.dummyItem').each(function(){
			$(this).appendTo($(this).parent());
			if($(this).siblings().size() > 0)
				$(this).css('display', 'none');
		});
	},
	
	saveLayout: function()
	{
		var self = this;
		var column = new Array;
		//parse the dom to see how our div are sorted
		self.dashColumns.each(function() {
			column.push(getWidgetInDom(this));
		});
		
		var ajaxRequest =
		{
			type: 'GET',
			url: 'index.php',
			dataType: 'html',
			async: true,
			error: ajaxHandleError,		// Callback when the request fails
			data: {	module: 'Dashboard',
					action: 'saveLayout' }
		};
		var layout = '';
		for(var i=0; i<column.length; i++)
		{
			layout += column[i].join('~');
			layout += '|';
		}
		if(layout != self.layout)
		{
			self.layout = layout;
			ajaxRequest.data['layout'] = layout;
			$.ajax(ajaxRequest);
		}
	},
	
	ajaxLoading: function(pluginId, actionId, callbackAfterLoaded)
	{
		var self = this;
		// When ajax replied, we replace the right div with the response
		function onLoaded(response)
		{
			var parDiv = $('.widgetDiv#'+actionId+'[plugin='+pluginId+']');
			parDiv.siblings('.widgetLoading').hide();
			parDiv.html($(response)).show();
			if(typeof callbackAfterLoaded != 'undefined')
			{
				callbackAfterLoaded(parDiv);
			}
		}
		//prepare and launch the ajax request
		var ajaxRequest = 
		{
			type: 'GET',
			url: 'index.php',
			dataType: 'html',
			async: true,
			error: ajaxHandleError,		// on request fails
			success: onLoaded,			// on request succeeds
			data: {	module: pluginId,
					action: actionId,
					idSite: piwik.idSite,
					period: piwik.period,
					date: piwik.currentDateStr					
	 			}
		};
		$.ajax(ajaxRequest);
	}
};


function getWidgetInDom(domElem)
{
	var items = $('.items:not(.dummyItem) .widgetDiv', domElem);
	var widgets = new Array;
	for(var i=0; i<items.size(); i++)
	{
		widgets.push($(items[i]).attr('plugin')+'.'+$(items[i]).attr('id'));
	}
	return widgets;
}

//fire-up everything on DOM ready event
$(document).ready(
	function()
	{		
		var dash = new dashboard();
		var menu = new widgetMenu(dash);
		
		blockUIConfig();
	
		//build the dashboard...
		dash.init(piwik.dashboardLayout);
		//...and the menu
		menu.init();		
	}
);
