
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
	//load menu widgets list
	var menu = $('.menu#widgetChooser');
	var count=0;
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
			
			//var sm1Div = $('.subMenuItem#'+plugin, subMenu1);
			//console.log('%d', $(sm1Div).outerHeight());
			
			var sm2Div = $('.subMenuItem#'+plugin, subMenu2);
			sm2Div.append('<div class="button menuItem" pluginToLoad="'+plugin+'" actionToLoad="'+widgets[i][1]+'">'+widgets[i][0] + '</div>');
			sm2Div.css('padding-top', count*18+'px');
		}
		count++;
	}
	$('.subMenuItem', subMenu2).hide();
	$('.button#hideMenu').hide();
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

function showMenu()
{
	$('.button#addWidget').hide();
	filterOutAlreadyLoadedWidget();
	$('.menu#widgetChooser').fadeIn('fast',function(){
	$('.button#hideMenu').show();});//queue event	
}

function hideMenu()
{
	$('.button#hideMenu').hide();
	$('.menu#widgetChooser').fadeOut('fast',function(){
	$('.button#addWidget').show();});//queue event
}

function bindMenuEvents(menu)
{
	//menu show button
	$('.button#addWidget').click(showMenu);
	$('.button#hideMenu').click(hideMenu);
	
	$('.subMenu1 .subMenuItem', menu).each(function(){
		var plugin = $(this).attr('id');
		var item = $('.subMenu2 .subMenuItem#'+plugin);
		
		$(this).hover(
			function()
			{
				$('.menuItem', menu).removeClass('menuSelected');
				$('.subMenu1 .subMenuItem', menu).removeClass('menuSelected');
				$('.subMenu2 .subMenuItem', menu).hide();
				$(this).addClass('menuSelected');
				item.show();
			},function(){});
	});

	$('.menuItem', menu).hover(
	function()
	{
		var plugin = $(this).attr('pluginToLoad');
		var action = $(this).attr('actionToLoad');
		
		$('.menuItem', menu).removeClass('menuSelected');
		$(this).addClass('menuSelected');
		
		$('.widgetDiv.previewDiv').each(function(){
			//format the div for upcomming ajax loading and set a temporary content
			$(this)	.attr('plugin', plugin)
					.attr('id', action)
					.html('<div id="previewLoading"><img src="themes/default/loading.gif" /> Loading preview, please wait...</div>').show();
			ajaxLoading(plugin, action);
		});
		
	},function(){})
	.click(function(){
		var plugin = $(this).attr('pluginToLoad');
		var action = $(this).attr('actionToLoad');

		movePreviewToDashboard();
		hideMenu();
		clearPreviewDiv();
		saveLayout();
		$('.button#addWidget').show();
	});
}

function movePreviewToDashboard()
{
	$('.widgetDiv.previewDiv').each(function(){
		var plugin = $(this).attr('plugin');
		var action = $(this).attr('id');
		var htmlContent = $(this).html()
		
		addEmptyWidget(1, plugin, action, true);
		
		var parDiv = $('.widgetDiv#'+action);
		parDiv.show();
		parDiv.siblings('.widgetLoading').hide();
		
		//var helper = $('.helperPreview');
		//helper.css({left: '400px', top: '200px'});
		//helper.animate({left: '10px', top: '250px'}, 5000, 'linear', function(){});
		//helper.html(htmlContent);
		//helper.empty();
		
		parDiv.html(htmlContent);
	});
}

function clearPreviewDiv()
{		
	$('.widgetDiv.previewDiv').empty()
		.attr('id', '')
		.attr('plugin', '');
}

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
				addWidgetAndLoad(i+1, wid[0], wid[1]);
  			}
  		}
	}
}


function addEmptyWidget(colNumber, plugin, action, onTop)
{	
	if(typeof onTop == "undefined")
		onTop = false;
	
	var item = '<div class="items"><div class="widget"><div class="widgetLoading">Loading widget, please wait...</div><div plugin="'+plugin+'"'+' id="'+action+'" class="widgetDiv"></div></div></div>';
    
    if(onTop)
    {
   		$('.col#'+colNumber).prepend(item);
    }
    else
    {
   		$('.col#'+colNumber).append(item);
   	}
   	
   	//find the title of the widget
	var title = 'Widget not found';
	var widgets = piwik.availableWidgets[plugin];
	for(var i in widgets)
	{
		if(action == widgets[i][1])
			title = widgets[i][0];
	}
	
	//add an handle to each items
	var widget = $('.widgetDiv#'+action).parents('.widget');
	widget.prepend('<div class="handle"><div class="button" id="close"><img src="themes/default/images/close.png" /></div><div class="widgetTitle">'+title+'</div></div>');
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
	button.click(onDeleteItem);
	
	makeSortable();
}

function addWidgetAndLoad(colNumber, plugin, action, onTop)
{
	addEmptyWidget(colNumber, plugin, action, onTop);
    loadItem($('.items #'+action).parents('.items'));
}

function loadItem(domElem)
{	
	var plugin;
	var action;
	//load every widgetDiv with asynchronous ajax
	$('.widgetDiv', domElem).each(
		function()
		{
			plugin = $(this).attr('plugin');
			action = $(this).attr('id')
			
			// get the ID of the div and load with ajax						
			ajaxLoading(plugin, action);
		});
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
		var item = $(target).parents('.items');
		var plugin = $('.widgetDiv', item).attr('plugin');
		var action = $('.widgetDiv', item).attr('id');
		
		//the item disapear slowly and is removed from the DOM
		item.fadeOut(500, function()
			{
				$(this).remove(); showNecessaryDummies();
				saveLayout();
				makeSortable();
				$.unblockUI();
			});
		
		//show menu item
		$('.menu#widgetChooser .menuItem[pluginToLoad='+plugin+'][actionToLoad='+action+']').show();
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
		var parDiv = $('.widgetDiv#'+actionId);
		parDiv.siblings('.widgetLoading').hide();
		parDiv.html( $(response)).show();
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
