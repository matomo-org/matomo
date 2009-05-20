function dashboard()
{
	this.dashboardElement = {};
	this.dashboardColumnsElement = {};
	this.layout = '';
}

dashboard.prototype =
{
	//function called on dashboard initialisation
	init: function(layout)
	{
		var self = this;
		
		//save some often used DOM objects
		self.dashboardElement = $('#dashboardWidgetsArea');
		self.dashboardColumnsElement = $('.col', self.dashDom);
		
		//dashboard layout
		self.layout = layout;
		
		//generate dashboard layout and load every displayed widgets
		self.generateLayout();
		
		self.makeSortable();
	},
	
	getWidgetsElementsInsideElement: function(elementToSearch)
	{
		return $('.sortable:not(.dummyItem) .widget', elementToSearch);
	},
	
	generateLayout: function()
	{
		var self = this;
		
		if(typeof self.layout == 'string') {
			var layout = {};
			//Old dashboard layout format: a string that looks like 'Actions.getActions~Actions.getDownloads|UserCountry.getCountry|Referers.getSearchEngines';
			// '|' separate columns
			// '~' separate widgets
			// '.' separate plugin name from action name
			var columns = self.layout.split('|');
			for(var columnNumber=0; columnNumber<columns.length; columnNumber++) {
				if(columns[columnNumber].length == 0) {
					continue;
				}
				var widgets = columns[columnNumber].split('~');
				layout[columnNumber] = {};
				for(var j=0; j<widgets.length; j++) {
					wid = widgets[j].split('.');
					uniqueId = 'widget'+wid[0]+wid[1];
					layout[columnNumber][j] = { 
						"uniqueId": uniqueId,
						"parameters": { 
							"module": wid[0], 
							"action": wid[1] 
						}
					};
	  			}
	  		}
			self.layout = layout;
		}
		layout = self.layout;
		for(var columnNumber in layout) {
			var widgetsInColumn = layout[columnNumber];
			for(var widgetId in widgetsInColumn) {
				widgetParameters = widgetsInColumn[widgetId]["parameters"];
				uniqueId = widgetsInColumn[widgetId]["uniqueId"];
				if(uniqueId.length>0) {
					self.addEmptyWidget(columnNumber, uniqueId, false);
				}
			}
			self.addDummyWidgetAtBottomOfColumn(columnNumber);
		}

		self.makeSortable();
		
		// load all widgets
		$('.widget', self.dashboardElement).each( function() {
			var uniqueId = $(this).attr('id');
			function onWidgetLoadedReplaceElementWithContent(loadedContent)
			{
				$('#'+uniqueId+'>.widgetContent', self.dashboardElement).html(loadedContent);
			}
			widget = widgetsHelper.getWidgetObjectFromUniqueId(uniqueId);
			widgetParameters = widget["parameters"];
			$.ajax(widgetsHelper.getLoadWidgetAjaxRequest(uniqueId, widgetParameters, onWidgetLoadedReplaceElementWithContent));
		});
	},
	
	addDummyWidgetAtBottomOfColumn: function(columnNumber)
	{
		var self = this;
		var columnElement = $(self.dashboardColumnsElement[columnNumber]);
		$(columnElement).append(	
						'<div class="sortable dummyItem">'+
							'<div class="widgetTop dummyWidgetTop"></div>'+
						'</div>');
	},
	
	addEmptyWidget: function(columnNumber, uniqueId, addWidgetOnTop)
	{
		var self = this;
		
		widgetName = widgetsHelper.getWidgetNameFromUniqueId(uniqueId);
		if(widgetName == false) {
			widgetName = _pk_translate('Dashboard_WidgetNotFound_js');
		}
		columnElement = $(self.dashboardColumnsElement[columnNumber]);
		emptyWidgetContent = '<div class="sortable">'+
								widgetsHelper.getEmptyWidgetHtml(uniqueId, widgetName, _pk_translate('Dashboard_LoadingWidget_js'))+
							'</div>';
		if(addWidgetOnTop) {
			columnElement.prepend(emptyWidgetContent);
		} else {
			columnElement.append(emptyWidgetContent);
		}
		widgetElement = $('#'+ uniqueId);
		widgetElement
			.hover( function() {
					$(this).addClass('widgetHover');
					$('.widgetTop', this).addClass('widgetTopHover');
					$('.button#close', this).show();
				}, function() {
					$(this).removeClass('widgetHover');
					$('.widgetTop', this).removeClass('widgetTopHover');
					$('.button#close', this).hide();
			});
		$('.button#close', widgetElement)
			.click( function(ev){
				self.onDeleteItem(this, ev);
			});

		widgetElement.show();
		return widgetElement;
	},
	
	//apply jquery sortable plugin to the dashboard layout
	makeSortable: function()
	{
		var self = this;
		function getHelper() {
			return $(this).clone().addClass('helper');
		}
		function onStart() {
		}
		function onStop() {
			$('.widgetHover', this).removeClass('widgetHover');
			$('.widgetTopHover', this).removeClass('widgetTopHover');
			$('.button#close', this).hide();
			self.saveLayout();
		}
		//launch 'sortable' property on every dashboard widgets
		self.dashboardElement
					.sortableDestroy()
					.sortable({
						items:'.sortable',
						hoverClass: 'hover',
						handle: '.widgetTop',
						helper: getHelper,
						start: onStart,
						stop: onStop
					});
	},

	// on mouse click on close widget button
	// we ask for confirmation and if 'yes' is clicked, we delete the widget from the dashboard
	onDeleteItem: function(target, ev)
	{
		var self = this;
		   
		var question = $('.dialog#confirm');
		$('#no', question).click($.unblockUI);
		$('#yes', question).click(function() {
			var item = $(target).parents('.sortable');
			$.unblockUI();
			item.fadeOut(200, function() {
				$(this).remove();
				self.saveLayout();
				self.makeSortable();
			});
		});
		$.blockUI({
			message: question, 
			css: { width: '300px', border:'1px solid black' }
		});
	},
	
	saveLayout: function()
	{
		var self = this;
		
		// build the layout object to save
		var layout = new Array;
		var columnNumber = 0;
		self.dashboardColumnsElement.each(function() {
			layout[columnNumber] = new Array;
			var items = self.getWidgetsElementsInsideElement(this);
			for(var j=0; j<items.size(); j++) {
				widgetElement = items[j];
				uniqueId = $(widgetElement).attr('id');
				widget = widgetsHelper.getWidgetObjectFromUniqueId(uniqueId);
				widgetParameters = widget["parameters"];
				layout[columnNumber][j] = 
				{
					"uniqueId": uniqueId,
					"parameters": widgetParameters
				};
			}
			columnNumber++;
		});
		
		//only save layout if it has changed
		layoutString = JSON.stringify(layout);
		if(layoutString != JSON.stringify(self.layout)) {
			self.layout = layout;
			var ajaxRequest =
			{
				type: 'POST',
				url: 'index.php?module=Dashboard&action=saveLayout',
				dataType: 'html',
				async: true,
				error: piwikHelper.ajaxHandleError,
				data: {	"layout": layoutString }
			};
			$.ajax(ajaxRequest);
		}
	}
};
