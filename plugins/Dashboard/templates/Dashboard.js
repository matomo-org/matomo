/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function dashboard()
{
	this.dashboardElement = {};
	this.dashboardColumnsElement = {};
	this.viewDataTableToSave = {};
	this.layout = '';
}

dashboard.prototype =
{
	isMaximised: false,
	widgetDialog: null,
	
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
		widgetViewDataTableToRestore = {};
		for(var columnNumber in layout) {
			var widgetsInColumn = layout[columnNumber];
			for(var widgetId in widgetsInColumn) {
				widgetParameters = widgetsInColumn[widgetId]["parameters"];
				uniqueId = widgetsInColumn[widgetId]["uniqueId"];
				widgetViewDataTableToRestore[uniqueId] = widgetParameters['viewDataTable'];
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
			self.reloadWidget(uniqueId, widgetViewDataTableToRestore[uniqueId]);
		});
	},

	reloadEnclosingWidget: function(domNodeInsideWidget)
	{
		var uniqueId = $(domNodeInsideWidget).parents('.widget').attr('id');
		this.reloadWidget(uniqueId);
	},
	
	reloadWidget: function(uniqueId, viewDataTableToRestore) 
	{
		function onWidgetLoadedReplaceElementWithContent(loadedContent)
		{
			$('#'+uniqueId+'>.widgetContent', self.dashboardElement).html(loadedContent);
		}
		widget = widgetsHelper.getWidgetObjectFromUniqueId(uniqueId);
		if(widget == false)
		{
			return;
		}
		widgetParameters = widget["parameters"];
		if(viewDataTableToRestore)
		{
			widgetParameters['viewDataTable'] = viewDataTableToRestore;
		}
		piwikHelper.queueAjaxRequest( $.ajax(widgetsHelper.getLoadWidgetAjaxRequest(uniqueId, widgetParameters, onWidgetLoadedReplaceElementWithContent)) );
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
								widgetsHelper.getEmptyWidgetHtml(uniqueId, widgetName)+
							'</div>';
		if(addWidgetOnTop) {
			columnElement.prepend(emptyWidgetContent);
		} else {
			columnElement.append(emptyWidgetContent);
		}
		
		widgetElement = $('#'+ uniqueId, self.dashboardElement);
		widgetElement
			.hover( function() {
					if(!self.isMaximised) {
						$(this).addClass('widgetHover');
						$('.widgetTop', this).addClass('widgetTopHover');
						$('.button#close, .button#maximise', this).show();
					}
 				}, function() {
 					if(!self.isMaximised) {
 						$(this).removeClass('widgetHover');
 						$('.widgetTop', this).removeClass('widgetTopHover');
 						$('.button#close, .button#maximise', this).hide();
 					}
			});
		$('.button#close', widgetElement)
			.click( function(ev){
				self.onDeleteItem(this, ev);
			});

		$('.button#maximise', widgetElement)
			.click( function(ev){
				self.onMaximiseItem(this, ev);
			});

		widgetElement.show();
		return widgetElement;
	},
	
	//apply jquery sortable plugin to the dashboard layout
	makeSortable: function()
	{
		var self = this;

		function onStart(event, ui) {
			if(!jQuery.support.noCloneEvent) {
				$('object', this).hide();
			}
		}

		function onStop(event, ui) {
			$('object', this).show();
			$('.widgetHover', this).removeClass('widgetHover');
			$('.widgetTopHover', this).removeClass('widgetTopHover');
			$('.button#close, .button#maximise', this).hide();
			self.saveLayout();
		}

		//launch 'sortable' property on every dashboard widgets
		self.dashboardElement
					.sortable('destroy')
					.sortable({
						items: 'div.sortable',
						opacity: 0.6,
						forceHelperSize: true,
						forcePlaceholderSize: true,
						placeholder: 'hover',
						handle: '.widgetTop',
						helper: 'clone',
						start: onStart,
						stop: onStop
					});
	},

	closeWidgetDialog: function() {
	    if(piwik.dashboardObject.widgetDialog) {
	        piwik.dashboardObject.widgetDialog.dialog('close');
	    }
	},
	
	onMaximiseItem: function(target, ev) {
		var self = this;
		self.isMaximised = true;
		self.widgetDialog = $(target).parents('.sortable');
		$(self.widgetDialog).css({'minWidth': '500px', 'maxWidth': '1000px'});
		$('.button#close, .button#maximise', self.widgetDialog).hide();
		self.widgetDialog.before('<div id="placeholder"> </div>');
		self.widgetDialog.dialog({
			title: '',
			modal: true,
			width: 'auto',
			position: ['center', 'center'],
			resizable: true,
			autoOpen: true,
			close: function(event, ui) {
				self.isMaximised = false;
				self.widgetDialog.dialog("destroy");
				$('#placeholder').replaceWith(self.widgetDialog);
				self.widgetDialog.removeAttr('style');
				self.saveLayout();
				self.widgetDialog.find('div.piwik-graph').trigger('piwikResizeGraph');
			}
		});
		self.widgetDialog.find('div.piwik-graph').trigger('piwikResizeGraph');
        $('body').click(function(ev) {
            if(ev.target.className == "ui-widget-overlay") {
                self.widgetDialog.dialog("close");
            }
        });
	},
	
	
	// on mouse click on close widget button
	// we ask for confirmation and if 'yes' is clicked, we delete the widget from the dashboard
	onDeleteItem: function(target, ev)
	{
		var self = this;
		function onDelete()
		{			
			var item = $(target).parents('.sortable');
			item.fadeOut(200, function() {
				$(this).remove();
				self.saveLayout();
				self.makeSortable();
			});
		}
		piwikHelper.windowModal('#confirm', onDelete)
	},
	
	// Called by DataTables when the View type changes.
    // We want to restore the Dashboard with the same view types as the user selected
	setDataTableViewChanged: function(uniqueId, newViewDataTable)
	{
		this.viewDataTableToSave[uniqueId] = newViewDataTable;
		if(newViewDataTable == 'tableAllColumns' || newViewDataTable == 'tableGoals') {
		    $('#maximise', $('#'+uniqueId)).click();
		}
		if(!this.isMaximised) {
		    this.saveLayout();
		}
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
				if(self.viewDataTableToSave[uniqueId])
				{
					widgetParameters['viewDataTable'] = self.viewDataTableToSave[uniqueId];
				}
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
				url: 'index.php?module=Dashboard&action=saveLayout&token_auth='+piwik.token_auth,
				dataType: 'html',
				async: true,
				error: piwikHelper.ajaxHandleError,
				data: {	"layout": layoutString }
			};
			$.ajax(ajaxRequest);
		}
	}
};
