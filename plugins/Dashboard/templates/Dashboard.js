/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function dashboard()
{
	this.dashboardElement = {};
	this.viewDataTableToSave = {};
	this.layout = '';
}

dashboard.prototype =
{
	isMaximised: false,
	widgetDialog: null,
	idDashboard: 1,
	
	//function called on dashboard initialisation
	init: function(layout, idDashboard)
	{
		//save some often used DOM objects
		this.dashboardElement = $('#dashboardWidgetsArea');
		
		//dashboard id
		if(idDashboard) {
			this.idDashboard = idDashboard;
		}
		
		//dashboard layout
		this.layout = layout;
		
		//generate dashboard layout and load every displayed widgets
		this.generateLayout();
		
		this.makeSortable();
	},
	
	getWidgetsElementsInsideElement: function(elementToSearch)
	{
		return $('.sortable .widget', elementToSearch);
	},
	
	/**
	 * Adjust the dashboard columns to fit the new layout
	 * removes or adds new columns if needed and sets the column sizes
	 * 
	 * @param layout new layout in format xx-xx-xx
	 * @return void
	 */
	adjustDashboardColumns: function(layout)
	{
		var columnWidth = layout.split('-');
		var columnCount = columnWidth.length;
		
		var currentCount = $('.col').length;
		
		if(currentCount < columnCount) {
			$('.menuClear').remove();
			for(var i=currentCount;i<columnCount;i++) {
				this.dashboardElement.append('<div class="col"> </div>');
			}
			this.dashboardElement.append('<div class="menuClear"> </div>');
		} else if(currentCount > columnCount) {
			for(var i=columnCount;i<currentCount;i++) {
				$('.col:last').remove();
			}
		}
		
		for(var i=0; i < columnCount; i++) {
			$('.col')[i].className = 'col width-'+columnWidth[i];
		}
		
		this.currentColumnLayout = layout;
		
		this.makeSortable();
		if(currentCount > 0) {
			this.saveLayout();
		}
		
		// reload all widgets containing a graph to make them display correct
		var self = this;
		$('.widget:has(".piwik-graph")').each(function(id, elem){
			self.reloadWidget($(elem).attr('id'));
		});
	},
	
	generateLayout: function()
	{
		// Handle old dashboard layout format used in piwik before 0.2.33
		// A string that looks like 'Actions.getActions~Actions.getDownloads|UserCountry.getCountry|Referers.getSearchEngines';
		// '|' separate columns
		// '~' separate widgets
		// '.' separate plugin name from action name
		if(typeof this.layout == 'string') {
			var layout = {};
			var columns = this.layout.split('|');
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
			this.layout = layout;
		}
	    
		var layout = this.layout;
		
		// Handle layout array used in piwik before 1.7
		// column count was always 3, so use layout 33-33-33 as default
		if($.isArray(layout)) {
			var layout = {
					config: {layout: '33-33-33'},
					columns: layout
			};
		}
		
		if(!layout.config.layout) {
			layout.config.layout = '33-33-33';
		}
		
		this.adjustDashboardColumns(layout.config.layout);
		
		widgetViewDataTableToRestore = widgetParametersToRestore = {};
		for(var columnNumber in layout.columns) {
			var widgetsInColumn = layout.columns[columnNumber];
			for(var widgetId in widgetsInColumn) {
				widgetParameters = widgetsInColumn[widgetId]["parameters"];
				uniqueId = widgetsInColumn[widgetId]["uniqueId"];
				widgetViewDataTableToRestore[uniqueId] = widgetParameters['viewDataTable'];
				widgetParametersToRestore[uniqueId] = widgetParameters;
				var isHidden = widgetsInColumn[widgetId]['isHidden'] ? widgetsInColumn[widgetId]['isHidden'] : false;
				if(uniqueId.length>0) {
					this.addEmptyWidget(columnNumber, uniqueId, false, isHidden);
				}
			}
		}

		this.makeSortable();
		
		// load all widgets
		var self = this;
		$('.widget', this.dashboardElement).each( function() {
			var uniqueId = $(this).attr('id');
			self.reloadWidget(uniqueId, widgetViewDataTableToRestore[uniqueId], widgetParametersToRestore[uniqueId]);
		});
	},

	/**
	 * reloads the widget inside the given dom element
	 * 
	 * @param domNodeInsideWidget   selector for a dom element
	 * @return void
	 */
	reloadEnclosingWidget: function(domNodeInsideWidget)
	{
		var uniqueId = $(domNodeInsideWidget).parents('.widget').attr('id');
		this.reloadWidget(uniqueId);
	},
	
	/**
	 * reloads a widget with the given uniqueId
	 * 
	 * @param uniqueId  id of a widget
	 * @param viewDataTableToRestore  datatable view to restore
	 * @return void
	 */
	reloadWidget: function(uniqueId, viewDataTableToRestore, parametersToRestore) 
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
		var segment = broadcast.getValueFromHash('segment');
		if(segment.length) {
			widgetParameters['segment'] = segment;
		}
		
		for(var i in parametersToRestore) {
		    widgetParameters[i] = parametersToRestore[i];
		}
		
		if(!widgetParameters['filter_limit']) {
		    widgetParameters['filter_limit'] = 10;
		}
		piwikHelper.queueAjaxRequest( $.ajax(widgetsHelper.getLoadWidgetAjaxRequest(uniqueId, widgetParameters, onWidgetLoadedReplaceElementWithContent)) );
	},
	
	addEmptyWidget: function(columnNumber, uniqueId, addWidgetOnTop, isHidden)
	{
		var self = this;
		
		widgetName = widgetsHelper.getWidgetNameFromUniqueId(uniqueId);
		if(widgetName == false) {
			widgetName = _pk_translate('Dashboard_WidgetNotFound_js');
		}
		columnElement = $($('.col')[columnNumber]);
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
						if(!$('.widgetContent', this).hasClass('hidden')) {
							$('.button#minimise', this).show();
						}
					}
 				}, function() {
 					if(!self.isMaximised) {
 						$(this).removeClass('widgetHover');
 						$('.widgetTop', this).removeClass('widgetTopHover');
 						$('.button#close, .button#maximise, .button#minimise', this).hide();
 					}
			});
		
		if(isHidden) {
			$('.widgetContent', widgetElement).toggleClass('hidden');
		}
		
		$('.button#close', widgetElement)
			.click( function(ev){
				self.onDeleteItem(this, ev);
			});

		$('.button#maximise', widgetElement)
			.click( function(ev){
				if($('.widgetContent', $(this).parents('.widget')).hasClass('hidden')) {
					$('.widgetContent', $(this).parents('.widget')).removeClass('hidden');
					$('.button#minimise', $(this).parents('.widget')).show();
					self.saveLayout();
				} else {
					self.onMaximiseItem(this, ev);
				}
			});

		$('.button#minimise', widgetElement)
			.click( function(ev){
				if(!self.isMaximised) {
					$('.widgetContent', $(this).parents('.widget')).addClass('hidden');
					$('.button#minimise', $(this).parents('.widget')).hide();
					self.saveLayout();
				} else {
					self.widgetDialog.dialog("close");
				}
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
			if($('.widget:has(".piwik-graph")', ui.item).length) {
			    self.reloadWidget($('.widget', ui.item).attr('id'));
			}
			self.saveLayout();
		}

		//launch 'sortable' property on every dashboard widgets
		$('div.col', self.dashboardElement)
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
						stop: onStop,
						connectWith: 'div.col'
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
		var minWidth = self.widgetDialog.width() < 500 ? 500 : self.widgetDialog.width();
		var maxWidth = minWidth > 1000 ? minWidth+100 : 1000;
		$(self.widgetDialog).css({'minWidth': minWidth+'px', 'maxWidth': maxWidth+'px'});
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
				$('.button#minimise', self.widgetDialog).hide()
				self.widgetDialog.dialog("destroy");
				$('#placeholder').replaceWith(self.widgetDialog);
				self.widgetDialog.removeAttr('style');
				self.saveLayout();
				self.widgetDialog.find('div.piwik-graph').trigger('resizeGraph');
			}
		});
		self.widgetDialog.find('div.piwik-graph').trigger('resizeGraph');
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
	
	setWidgetParameters: function(uniqueId, params)
    {
        var widget = widgetsHelper.getWidgetObjectFromUniqueId(uniqueId);
        for(var i in params) {
            widget.parameters[i] = params[i];
        }
        if(!this.isMaximised) {
            this.saveLayout();
        }
    },
    
    saveLayout: function()
	{
		var self = this;
		
		// build the layout object to save
		var layout = {
				config: {},
				columns: []
		};
		
		layout.config.layout = this.currentColumnLayout;
		
		var columnNumber = 0;
		$('.col').each(function() {
			layout.columns[columnNumber] = new Array;
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
				layout.columns[columnNumber][j] = 
				{
					"uniqueId": uniqueId,
					"parameters": widgetParameters,
					"isHidden": $('.widgetContent', $(widgetElement)).hasClass('hidden') ? 1 : 0
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
				data: { "layout": layoutString, "idDashboard": self.idDashboard }
			};
			$.ajax(ajaxRequest);
		}
	}, 
	
	resetLayout: function()
	{
		var ajaxRequest =
		{
			type: 'POST',
			url: 'index.php?module=Dashboard&action=resetLayout&token_auth='+piwik.token_auth,
			dataType: 'html',
			async: false,
			error: piwikHelper.ajaxHandleError,
			success: function() { window.location.reload(); },
			data: { "idDashboard": this.idDashboard, "idSite": piwik.idSite }
		};
		$.ajax(ajaxRequest);
		piwikHelper.showAjaxLoading();
	}
};
