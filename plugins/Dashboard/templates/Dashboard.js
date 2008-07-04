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

//Default configuration of the blockUI jquery plugin
function blockUIConfig()
{
	//set default style value for blockUI
	$.extend($.blockUI.defaults.overlayCSS, { backgroundColor: '#000000', opacity: '0.4'});
	//disable animation effect
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
	//function called when the menu is built for the first time
	init: function()
	{
		var self = this;
		self.buildMenu();
		$('.button#addWidget').click(function(){self.show();});
	},
	
	//function called when a clone of an existing menu is built
	initBuilt: function(menuDom)
	{
		var self = this;
		self.menu = menuDom;
		self.bindEvents();
	},
	
	//create DOM elements of the menu
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
	
	//bind events (click/hover/...) of the menu to appropriate callback
	bindEvents: function()
	{
		var self = this;
		
		//menu buttons
		$('.button#hideMenu', self.menu).click(function(){self.hide();});
		$('#closeMenuIcon', self.menu).click(function(){self.hide();});
		$('.subMenu#sub3 .widget .handle', self.menu).css('cursor', 'pointer')
				.click(function(){self.movePreviewToDashboard();});
		
		//update widget list on submenu#1 mouse over
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
	
		//update widget preview on submenu#2 mouse over
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
								.html('<div id="previewLoading"><img src="themes/default/loading.gif" />'+ _pk_translate('Dashboard_LoadingPreview') +'</div>').show();
						self.dashboard.ajaxLoading(plugin, action);
					}
				});
			}
		},function(){})
		.click(function(){	self.movePreviewToDashboard(); });
	},
	
	//hide the menu
	hide: function()
	{
		//simply disable modal dialog box
		$.unblockUI();
	},
	
	//show the menu
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
		
		//build a list of loaded widget, parse the dashboard column
		var widgets = new Array;	
		self.dashboard.getColumns().each(
			function()
			{
				widgets = widgets.concat(getWidgetInDom(this));
			}
		);
		
		//find widget from the loaded list in the menu, and apply
		//appropriate style and behaviour
		$('.menuItem', self.menu).each(function(){
			var plugin = $(this).attr('pluginToLoad');
			var action = $(this).attr('actionToLoad');
			if(contains(widgets, plugin+'.'+action))
			{
				$(this).addClass('menuDisabled');
				$(this).attr('title', _pk_translate('Dashboard_TitleWidgetInDashboard'));
			}
			else
			{
				$(this).removeClass('menuDisabled');
				$(this).attr('title', _pk_translate('Dashboard_TitleClickToAdd'));
			}
		});
	},
	
	//move the widget in the preview box to the dashboard, without reloading it
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
	
	//clear the widget preview box
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
	//function called on dashboard initialisation
	init: function(layout)
	{
		var self = this;
		
		//save some often used DOM objects
		self.dashArea = $('#dashboardWidgetsArea');
		self.dashColumns = $('.col', self.dashDom);
		
		//dashboard layout
		self.layout = layout;
		
		//generate dashboard layout and load every displayed widgets
		self.generateLayout();

		//setup widget dynamic behaviour
		self.setupWidgetSortable();
	},
	
	//return the DOM corresponding to the dashboard columns
	getColumns: function()
	{
		return this.dashColumns;
	},
	
	//'widgetize' every created widgets:
	//add an handle bar and apply 'sortable' drag&drop effect
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
	
	//generate dashboard DOM corresponding to the initial layout
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
	
	//add a new widget to the dashboard
	//colnumber is the column in wich you want to add the widget
	//plugin/action is the widget to load
	//onTop: boolean specifying if the widget should be added on top of the column
	addEmptyWidget: function(colNumber, plugin, action, onTop)
	{
		var self = this;
		
		if(typeof onTop == "undefined")
			onTop = false;
		
		var item = '<div class="items"><div class="widget"><div class="widgetLoading">'+ _pk_translate('Dashboard_LoadingWidget') +'</div><div plugin="'+plugin+'"'+' id="'+action+'" class="widgetDiv"></div></div></div>';
	
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
	
	//return widget title designated by its plugin/action couple
	getWidgetTitle: function(plugin, action)
	{
		var self = this;
		
		var title = _pk_translate('Dashboard_WidgetNotFound');
		var widgets = piwik.availableWidgets[plugin];
		for(var i in widgets)
		{
			if(action == widgets[i][1])
				title = widgets[i][0];
		}
		return title;
	},
	
	//add the widget and load it
	addWidgetAndLoad: function(colNumber, plugin, action, onTop)
	{
		var self = this;
		
		self.addEmptyWidget(colNumber, plugin, action, onTop);
		self.loadItem($('.items [plugin='+plugin+']#'+action, self.dashArea).parents('.items'));
	},
	
	//add an handle bar to a given widget with a particular title
	addHandleToWidget: function(widget, title)
	{
		widget.prepend('<div class="handle">\
							<div class="button" id="close">\
								<img src="themes/default/images/close.png" />\
							</div>\
							<div class="widgetTitle">'+title+'</div>\
						</div>');
	},

	//auxiliary function calling ajax loading procedure for a given DOM element
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
	
	//apply jquery sortable plugin to the dashboard layout
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

	//on mouse click on close widget button
	//we ask for confirmation and we delete the widget from the dashboard
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
	
	//dummies are invisible item that help for widget positionning
	//and keep the column visible even when there aren't widget anymore in it
	showDummies: function()
	{
		var self = this;
		$('.dummyItem').css('display', 'block');
	},
	
	//see showDummies
	//hide dummies that are not needed for column consistency
	hideUnnecessaryDummies: function()
	{
		var self = this;
		$('.dummyItem').each(function(){
			$(this).appendTo($(this).parent());
			if($(this).siblings().size() > 0)
				$(this).css('display', 'none');
		});
	},
	
	//save the layout in the database/cookie so the user can
	//retrieve it the next time he load the dashboard
	saveLayout: function()
	{
		var self = this;
		var column = new Array;
		
		//parse the dom to see how our div are organized
		//build a list of widget sorted by columns
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
		
		//write layout in a string
		//using '|' as column separator
		// and '~' as widget separator
		var layout = '';
		for(var i=0; i<column.length; i++)
		{
			layout += column[i].join('~');
			layout += '|';
		}
		
		//only save layout if it has changed
		if(layout != self.layout)
		{
			self.layout = layout;
			ajaxRequest.data['layout'] = layout;
			$.ajax(ajaxRequest);
		}
	},
	
	//load widget with an ajax request
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
			data: "module="+pluginId+"&action="+actionId+"&idSite="+piwik.idSite+"&period="+piwik.period+"&date="+piwik.currentDateStr
		};
		$.ajax(ajaxRequest);
	}
};

//auxiliary function: list widgets available in a DOM tree
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

//build everything when DOM is ready
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
