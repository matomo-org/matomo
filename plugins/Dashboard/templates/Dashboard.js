
//there is a problem with loop when we extends javascript Array
function contains(array, searchElem) {
	for(var i=0; i<array.length; i++) {
		if (array[i]==searchElem) {
			return true;
		}
	}
	return false;
}
 
 //fire-up everything on DOM ready event
 $(document).ready(
	function()
	{
		//generate dashboard layout and load every displayed widgets
		generateLayout();

		//build the menu
		buildWidgetChooserMenu();
		
		//setup widget dynamic behaviour
		setupWidgetSortable();
	}
);

function buildWidgetChooserMenu()
{
	//menu show button
	$('.button#addWidget').click(function(){
		$(this).hide();
		filterOutAlreadyLoadedWidget();
		$('.menu#widgetChooser').show('slow');
	});
	
	//load menu widgets list
	var menu = $('.menu#widgetChooser');
	for(var plugin in piwik.availableWidgets)
	{
		var widgets = piwik.availableWidgets[plugin];
		for(var i in widgets)
		{
			var subMenu1 = $('.subMenu1', menu);
			var subMenu2 = $('.subMenu2', menu);
		
			var exist = $('.subMenuItem#'+plugin, subMenu1);
			if(exist.size()==0)
			{
				subMenu1.append('<div class="subMenuItem" id="'+plugin+'">'+plugin+'<div>');
				subMenu2.append('<div class="subMenuItem" id="'+plugin+'"><div>');
			}
			
			var sm2Div = $('.subMenuItem#'+plugin, subMenu2);
			sm2Div.append('<div class="button menuItem" pluginToLoad="'+plugin+'" actionToLoad="'+widgets[i][1]+'">'+widgets[i][0] + ' => (' + plugin +'.'+ widgets[i][1] + ')</div>');
		}
	}
	$('.subMenuItem', subMenu2).hide();
	bindMenuEvents(menu);
}

//disable widgets that are already in the dashboard
function filterOutAlreadyLoadedWidget()
{
	//list loaded widget:
	var widgets = new Array;	
	$('.col').each(
		function()
		{
			widgets = widgets.concat(getWidgetInDom(this));
		}
	);
	
	$('.menu#widgetChooser .menuItem').each(function(){
		var plugin = $(this).attr('pluginToLoad');
		var action = $(this).attr('actionToLoad');
		if(contains(widgets, plugin+'.'+action))
		{
			$(this).hide();
		}
		else
		{
			$(this).show();
		}
	});
}

function bindMenuEvents(menu)
{
	$('.subMenu1 .subMenuItem', menu).each(function(){
		var plugin = $(this).attr('id');
		var item = $('.subMenu2 .subMenuItem#'+plugin);
		
		$(this).hover(
			function()
			{
				$('.subMenu1 .subMenuItem', menu).removeClass('menuSelected');
				$('.subMenu2 .subMenuItem', menu).hide();
				$(this).addClass('menuSelected');
				item.show();
			},
			
			function()
			{
			});
	});

	$('.menuItem', menu).click(function(){
		var plugin = $(this).attr('pluginToLoad');
		var action = $(this).attr('actionToLoad');

		var exists = $('.parentDiv#'+action);
		
		if(exists.size()>0)
		{
			alert('Widget already in dashboard');
		}
		else
		{
			menu.hide('slow');
			addWidget(1, plugin, action);
			saveLayout();
			$('.button#addWidget').show();
		}
	});
}

function getWidgetInDom(domElem)
{
	var items = $('.items:not(.dummyItem) .parentDiv', domElem);
	var widgets = new Array;
	for(var i=0; i<items.size(); i++)
	{
		widgets.push($(items[i]).attr('plugin')+'.'+$(items[i]).attr('id'));
	}
	return widgets;
}
	
function setupWidgetSortable()
{
	//add a dummy item on each columns
	$('.col').each(
		function()
		{
			$(this).append('<div class="items dummyItem"><div class="handle dummyHandle"></div></div>');
		});
	 
	hideUnnecessaryDummies();
	 
	makeSortable();
}

function generateLayout()
{
	//dashboardLayout look like :
	//'Actions.getActions~Actions.getDownloads|UserCountry.getCountry|Referers.getSearchEngines';
	//'|' separate columns
	//'~' separate widgets
	//'.' separate plugin name from action name
	var col = piwik.dashboardLayout.split('|');
	for(var i=0; i<col.length; i++)
	{
		if(col[i] != '')
		{
			var widgets = col[i].split('~');
			for(var j=0; j<widgets.length; j++)
			{
				var wid = widgets[j].split('.');
				addWidget(i+1, wid[0], wid[1]);
  			}
  		}
	}
}

function addWidget(colNumber, plugin, action)
{
	var item = '<div class="items"><div class="widget"><div plugin="'+plugin+'"'+' id="'+action+'" class="parentDiv"></div></div></div>';
    $('.col#'+colNumber).append(item);
    loadItem($('.items #'+action).parents('.items'));
	makeSortable();
}

function loadItem(domElem)
{	
	var plugin;
	var action;
	var title;
	//load every parentDiv with asynchronous ajax
	$('.parentDiv', domElem).each(
		function()
		{
			plugin = $(this).attr('plugin');
			action = $(this).attr('id')
			
			// get the ID of the div and load with ajax						
			ajaxLoading(plugin, action);
		});
		
	//find the title of the widget
	var widgets = piwik.availableWidgets[plugin];
	for(var i in widgets)
	{
		if(action == widgets[i][1])
			title = widgets[i][0]
	}
	
	//add an handle to each items
	$('.widget', domElem).prepend('<div class="handle"><div class="widgetTitle">'+title+'</div><div class="button" id="close"><img src="themes/default/images/close.png" /></div></div>');
	
	var button = $('.button#close', domElem);
	
	//Only show handle buttons on mouse hover
	$('.handle', domElem).hover(
		function()
		{
			$(this).parent().addClass('widgetHover');
			$(this).addClass('handleHover');
			button.fadeIn(100);
		},
		function()
		{
			$(this).parent().removeClass('widgetHover');
			$(this).removeClass('handleHover');
			button.fadeOut(200);
		}
	);
	
	//Bind click event on close button
	button.click(onDeleteItem);
}

function makeSortable()
{
	//launch 'sortable' property on every dashboard widgets
	$('.sortDiv').sortableDestroy()
	.sortable({
	 	items:'.items',
	 	hoverClass: 'hover',
	 	handle: '.handle',
	 	helper: getHelper,
	 	start: onStart,
	 	stop: onStop
	 	});
}

function getHelper()
{
	return $(this).clone().addClass('helper');
}

function onStart()
{
	showDummies();
}

function onStop()
{
	hideUnnecessaryDummies();
	saveLayout();
}

function onDeleteItem(ev)
{
	var target = this;       
	//ask confirmation and delete item
	var question = $('.dialog#confirm').clone();
	$('#yes', question).click(function()
	{
		$(target).parents('.items').fadeOut(500, function(){$(this).remove()});
		showNecessaryDummies();
		saveLayout();
		makeSortable();
		$.unblockUI(); 
	});
	$('#no', question).click($.unblockUI);
	$.blockUI(question, { width: '300px' }); 
}

function showDummies()
{
	$('.dummyItem').css('display', 'block');
}

function showNecessaryDummies()
{
	showDummies();
	//hideUnnecessaryDummies();
}

function hideUnnecessaryDummies()
{
	$('.dummyItem').each(function(){
		$(this).appendTo($(this).parent());
		if($(this).siblings().size() > 0)
			$(this).css('display', 'none');
	});
}
	
function saveLayout()
{
	var column = new Array;
	//parse the dom to see how our div are sorted
	$('.sortDiv .col').each(function() {
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
	ajaxRequest.data['layout'] = layout;
	$.ajax(ajaxRequest);
}	

function ajaxLoading(pluginId, actionId)
{
	// When ajax replied, we replace the right div with the response
	function onLoaded(response)
	{
		var content = $(response);
		$('#'+actionId).html( $(content).html() );
	}
	//prepare and launch the ajax request
	var ajaxRequest = 
	{
		type: 'GET',
		url: 'index.php',
		dataType: 'html',
		async: true,
		error: ajaxHandleError,		// Callback when the request fails
		success: onLoaded,			// Callback when the request succeeds
		data: {	module: pluginId,
				action: actionId,
				idSite: piwik.idSite,
				period: piwik.period,
				date: piwik.currentDateStr					
 			}
	};
	$.ajax(ajaxRequest);
}
