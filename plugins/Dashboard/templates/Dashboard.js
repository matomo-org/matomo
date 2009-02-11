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

function dashboard()
{
	this.dashArea = new Object;
	this.dashColumns = new Object;
	this.layout = '';
}
	
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

		if((plugin === "") || (action === ""))  return;

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
				loadWidgetInDiv($(this).attr('plugin'), $(this).attr('id'));
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
		var question = $('.dialog#confirm');
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
		$.blockUI({message: question, css: { width: '300px', border:'1px solid black' }});
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
