function widgetsHelper()
{
}

widgetsHelper.getWidgetCategoryNameFromUniqueId = function (uniqueId)
{
	var widgets = piwik.availableWidgets;
	for(var widgetCategory in widgets) {
		var widgetInCategory = widgets[widgetCategory];
		for(var i in widgetInCategory) {
			if(widgetInCategory[i]["uniqueId"] == uniqueId) {
				return widgetCategory;
			}
		}
	}
	return false;
};

widgetsHelper.getWidgetObjectFromUniqueId = function (uniqueId)
{
	var widgets = piwik.availableWidgets;
	for(var widgetCategory in widgets) {
		var widgetInCategory = widgets[widgetCategory];
		for(var i in widgetInCategory) {
			if(widgetInCategory[i]["uniqueId"] == uniqueId) {
				return widgetInCategory[i];
			}
		}
	}
	return false;
};

widgetsHelper.getWidgetNameFromUniqueId = function (uniqueId)
{
	widget = this.getWidgetObjectFromUniqueId(uniqueId);
	if(widget == false) {
		return false;
	}
	return widget["name"];
};

widgetsHelper.getLoadWidgetAjaxRequest = function (widgetUniqueId, widgetParameters, onWidgetLoadedCallback)
{
	var ajaxRequest = 
	{
		widgetUniqueId:widgetUniqueId,
		type: 'GET',
		url: 'index.php',
		dataType: 'html',
		async: true,
		error: piwikHelper.ajaxHandleError,		
		success: onWidgetLoadedCallback,
		data: piwikHelper.getQueryStringFromParameters(widgetParameters) + "&idSite="+piwik.idSite+"&period="+piwik.period+"&date="+piwik.currentDateString
	};
	return ajaxRequest;
};

widgetsHelper.getEmptyWidgetHtml = function (uniqueId, widgetName)
{
	return '<div id="'+uniqueId+'" class="widget">'+
				'<div class="widgetTop">'+
					'<div class="button" id="close">'+
						'<img src="themes/default/images/close.png" title="'+_pk_translate('Dashboard_Close_js')+'" />'+
					'</div>'+
					'<div class="widgetName">'+widgetName+'</div>'+
				'</div>'+
				'<div class="widgetContent">'+ 
					'<div class="widgetLoading">'+
						_pk_translate('Dashboard_LoadingWidget_js') +
					'</div>'+
				'</div>'+
			'</div>';
};

// widgetMenu constructor
function widgetMenu(dashboard)
{
	this.menu = {};
	this.dashboard = dashboard;
}

// widgetMenu object
widgetMenu.prototype =
{
	init: function()
	{
		var self = this;
		self.menuElement = $('#widgetChooser');		
		self.buildMenu();
		
		//close widget onClickOutside
		if($('#addWidget')[0]){
			$('#addWidget').hover(
					function(){self.isHover=1}, 
					function(){self.isHover=0}
			);
			$('body').bind('mouseup',function(e){ 
				if(self.widgetIsOpen && !self.isHover){
					self.hide();
				}
			});
		}
	},
	
	registerCallbackOnWidgetLoad: function( callbackOnWidgetLoad )
	{
		this.onWidgetLoad = callbackOnWidgetLoad;
	},
	
	registerCallbackOnMenuHover:  function( callbackOnMenuHover )
	{
		this.onMenuHover = callbackOnMenuHover;
	},
	
	//create DOM elements of the menu
	buildMenu: function()
	{
		var self = this;
		var menuWidgetCategories = $('.subMenu#sub1', self.menuElement);
		var menuWidgetNames = $('.subMenu#sub2', self.menuElement);
		
		menuWidgetCategories.append('<ol id="menuList"></ol>');
		menuWidgetNames.append('<ul id="widgetList"></ul>');
		var lineHeight = $('ol', menuWidgetCategories).css('line-height');
		lineHeight = Number(lineHeight.substring(0, lineHeight.length-2));
	
		var i=0;
		for(var widgetCategory in piwik.availableWidgets) {
			var widgets = piwik.availableWidgets[widgetCategory];
			for(var j in widgets) {
				widgetName = widgets[j]["name"];
				widgetUniqueId = widgets[j]["uniqueId"];
				widgetParameters = widgets[j]["parameters"];
				widgetCategoryId = 'category'+i;
				exist = $('.subMenuItem#'+widgetCategoryId, menuWidgetCategories);
				if(exist.size() == 0) {
					$('ol', menuWidgetCategories)
						.append('<li class="subMenuItem" id="'+widgetCategoryId+'">'+
									'<span>'+widgetCategory+'</span>'+
								'</li>');
					$('ul', menuWidgetNames)
						.append('<li class="subMenuItem" id="'+widgetCategoryId+'"></li>');
				}
				// we prepend the ID with "ID" to not conflict with the <div> 
				// that contains the widget preview and that has the widgetUniqueId already
				$('.subMenuItem#'+widgetCategoryId, menuWidgetNames)
						.append('<div class="button menuWidgetName" id="'+ 'ID' + widgetUniqueId +'">'+ 
									widgetName + 
								'</div>')
						.css('padding-top', i*lineHeight+'px');
			}
			i++;
		}
		$('.subMenuItem', menuWidgetNames).hide();
	},
	
	resetMenuState: function ()
	{
		$('.menuSelected', self.menuElement).removeClass('menuSelected');
		$('#sub2 .subMenuItem', self.menuElement).hide();
		$('#sub3').empty().html('<div class="widget"></div>');
		$('#sub3').width(0);
	},
	
	bindEvents: function()
	{
		var self = this;
		if(typeof self.menuInitialized != 'undefined') {
			return;
		}
		self.menuInitialized = true;
		
		// Main menu (widget categories)
		$('.subMenu#sub1 .subMenuItem', self.menuElement)
			.hover(function() {
				self.resetMenuState();
				categoryIdHovered = $(this).attr('id');
				$('#sub2 #'+categoryIdHovered, self.menuElement).show();
				$(this).addClass('menuSelected');
			}, function() {}
		);

		// Sub menu (each widget in the middle column)
		$('.menuWidgetName', self.menuElement)
			.hover( function() {
				if($(this).hasClass('menuDisabled')) {
					return;
				}
				// the ID is prefixed with "ID"
				widgetUniqueId = $(this).attr('id').substr(2);
				
				// only reload preview if necessary
				if($('#sub3 .widget').attr('id') == widgetUniqueId) {
					return;
				}
				self.expectedWidgetUniqueId = widgetUniqueId;
				
				widget = widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId);
				widgetParameters = widget['parameters'];
				
				$('.subMenu#sub2 .menuSelected').removeClass('menuSelected');
				$(this).addClass('menuSelected');

				if(typeof self.onMenuHover != 'undefined') {
					self.onMenuHover(widgetUniqueId);
				}
				emptyWidgetHtml = widgetsHelper.getEmptyWidgetHtml(
										widgetUniqueId, 
										'<div title="'+_pk_translate("Dashboard_AddPreviewedWidget_js")+'">'+
											_pk_translate('Dashboard_WidgetPreview_js')+
										'</div>'
				);
				$('#sub3').html(emptyWidgetHtml);
				
				//Widget width auto detection
				var defaultWidgetWidth=600;
				if(self.dashboard) {
                    var currentWidgetWidth=$(".col:first", self.dashboard.dashboardElement).width();
                }
				$('#sub3').width(self.dashboard?currentWidgetWidth:defaultWidgetWidth);
				
				$('#sub3 .widgetTop').click(function() {
					self.movePreviewToDashboard();
				});
				
				var onWidgetLoadedCallback = function (response) {
					if(this.widgetUniqueId != self.expectedWidgetUniqueId) {
						return;
					}
					widgetElement = $('#'+this.widgetUniqueId);
					$('.widgetContent', widgetElement).html($(response));
					if(typeof self.onWidgetLoad != 'undefined') {
						self.onWidgetLoad(	widgetUniqueId, 
											widgetElement
						);
					}
				};
				ajaxRequest = widgetsHelper.getLoadWidgetAjaxRequest(widgetUniqueId, widgetParameters, onWidgetLoadedCallback);
				$.ajax(ajaxRequest);
			}, function() {}
		);
	},
	
	show: function()
	{
		var self = this;
		if(typeof self.dashboard != 'undefined') {
			self.initWidgetMenuForDashboard();
			self.filterOutAlreadyLoadedWidget();
			self.menuElement.show();
		}
		self.resetMenuState();
		self.bindEvents();
		self.widgetIsOpen=1;
	},
	
	hide: function()
	{
		if(!$(this.menuElement).parents('#addWidget')[0]) return false; //don't close if not in #addWidget block
 		this.menuElement.hide();
		this.widgetIsOpen=0;
	},
	
	toggle:function(e){
		if(!this.widgetIsOpen) this.show();
		else this.hide();
	},
	
	hideMenu: function()
	{
		$.unblockUI();
	},
	
	filterOutAlreadyLoadedWidget: function()
	{
		var self = this;
		function contains(array, searchElem) {
			for(var i=0; i<array.length; i++) {
				if (array[i] == searchElem) {
					return true;
				}
			}
			return false;
		}
		var widgets = self.dashboard.getWidgetsElementsInsideElement( self.dashboard.dashboardElement );
		var widgetInDashboardUniqueIds = new Array();
		for(var i=0; i<widgets.size(); i++) {
			widgetInDashboardUniqueIds.push($(widgets[i]).attr('id'));
		}
		$('.menuWidgetName', self.menuElement).each( function() {
			// the ID is prefixed with "ID"
			var uniqueId = $(this).attr('id').substr(2);
			if(contains(widgetInDashboardUniqueIds, uniqueId)) {
				$(this).addClass('menuDisabled');
				$(this).attr('title', _pk_translate('Dashboard_TitleWidgetInDashboard_js'));
			} else {
				$(this).removeClass('menuDisabled');
				$(this).attr('title', _pk_translate('Dashboard_TitleClickToAdd_js'));
			}
		});
	},
	
	movePreviewToDashboard: function()
	{
		var self = this;
		if($('#sub3 .widget .widgetLoading', self.menuElement)[0]) return false; //don't move widget while it in loading
		
		self.hide();
		
		if(typeof self.dashboard == 'undefined') {
			return;
		}
		$('#sub3 .widget', self.menuElement).each(function() {
			uniqueId = $(this).attr('id');
			widgetAddedToDashboard = self.dashboard.addEmptyWidget(0, uniqueId, true);
			widgetContentToReplace = $('.widgetContent', widgetAddedToDashboard );
			widgetContentLoadedInPreview = $('.widgetContent', this).clone(true);
			widgetContentToReplace.replaceWith( widgetContentLoadedInPreview );
		});
		self.dashboard.makeSortable();
		self.dashboard.saveLayout();
	},

	initWidgetMenuForDashboard: function()
	{
		var self = this;
		if(typeof self.menuInitialized == 'undefined') {
			$('.menuWidgetName', self.menuElement)
				.click( function() { 
						if(!$(this).hasClass('menuDisabled')) {
							self.movePreviewToDashboard(); 
						}
			});
			$(document).keydown( function(e) {
				var key = e.keyCode || e.which;
				if(key == 27) {
					self.hide();
				}
			});
			
		}
	}
};

