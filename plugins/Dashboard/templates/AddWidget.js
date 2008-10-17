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
	init: function(onWidgetLoadCallback)
	{
		this.onWidgetLoad = onWidgetLoadCallback;
		var self = this;
		self.buildMenu();
	},
	
	//function called when a clone of an existing menu is built
	initBuilt: function(menuDom, onWidgetLoadCallback)
	{
		var self = this;
		self.onWidgetLoad = onWidgetLoadCallback;
		self.menu = menuDom;
		self.bindEvents();
	},
	
	//create DOM elements of the menu
	buildMenu: function()
	{
		var self = this;
		
		//load menu widgets list
		self.menu = $('#widgetChooser');		
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
					$('#embedThisWidget').empty();
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
								.html('<div id="loadingPiwik"><img src="themes/default/images/loading-blue.gif" /> '+ _pk_translate('Dashboard_LoadingPreview') +'</div>').show();
						$('#embedThisWidget').empty();
						loadWidgetInDiv(plugin, action, self.onWidgetLoad);
					}
				});
			}
		},function(){})
		.click(function(){	self.movePreviewToDashboard(); });
	},
	
	hide: function()
	{
		//simply disable modal dialog box
		$.unblockUI();
	},
	
	show: function()
	{
		var self = this;
		if(self.dashboard != undefined) 
		{
			self.filterOutAlreadyLoadedWidget();
			var dispMenu = $('#widgetChooser').clone(true);
			$.blockUI(dispMenu, {width:'', top: '5%',left:'10%', right:'10%', margin:"0px", textAlign:'', cursor:'', border:'0px'});
			menuDom = $('.blockMsg #widgetChooser');
		}
		else
		{
			menuDom = $('#widgetChooser');
		}		
		var dispMenuObject = new widgetMenu(self.dashboard);
		dispMenuObject.initBuilt(menuDom, self.onWidgetLoad); 
	},

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
		$('#embedThisWidget').empty();
	}
};

function loadWidgetInDiv(pluginId, actionId, onWidgetLoad)
{
	function onLoaded(response)
	{
		var parDiv = $('.widgetDiv#'+actionId+'[plugin='+pluginId+']');
		parDiv.siblings('.widgetLoading').hide();
		parDiv.html($(response));
		parDiv.show();
		
		if(typeof onWidgetLoad != 'undefined')
		{
			onWidgetLoad(parDiv, pluginId, actionId);
		}
	}
	var ajaxRequest = 
	{
		type: 'GET',
		url: 'index.php',
		dataType: 'html',
		async: true,
		error: ajaxHandleError,		
		success: onLoaded,
		data: "module="+pluginId+"&action="+actionId+"&idSite="+piwik.idSite+"&period="+piwik.period+"&date="+piwik.currentDateStr
	};
	$.ajax(ajaxRequest);
}