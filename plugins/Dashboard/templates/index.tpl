<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd ">

{literal}

<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script type="text/javascript" src="libs/jquery/jquery.scrollTo.js"></script>
<script type="text/javascript" src="libs/jquery/jquery.dimensions.js"></script>

<script type="text/javascript" src="libs/jquery/tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="libs/jquery/truncate/jquery.truncate.js"></script>
<script type="text/javascript" src="themes/default/common.js"></script>
<script type="text/javascript" src="plugins/Home/templates/datatable.js"></script>

<script type="text/javascript" src="libs/jquery/jquery.blockUI.js"></script>

<script type="text/javascript" src="libs/jquery/ui.mouse.js"></script>
<script type="text/javascript" src="libs/jquery/ui.sortable_modif.js"></script>

<script type="text/javascript" src="libs/swfobject/swfobject.js"></script>

<link rel="stylesheet" href="libs/jquery/tooltip/jquery.tooltip.css">
<link rel="stylesheet" href="plugins/Home/templates/datatable.css">

<script type="text/javascript">

	 $(document).ready(
			function()
			{
				//get layout
				var piwik_DashboardLayout = '';
				{/literal}
					{if isset($layout) }
						piwik_DashboardLayout = '{$layout}';
					{else}
						//Load default layout...
						piwik_DashboardLayout = 'Actions.getActions~Actions.getDownloads|UserCountry.getCountry~UserSettings.getPlugin|Referers.getSearchEngines~Referers.getKeywords';
					{/if}
				{literal}
				
				//generate dashboard layout
				var col = piwik_DashboardLayout.split('|');
				for(var i in col)
				{
					if(col[i] != '')
					{
						var widgets = col[i].split('~');
						for(var j in widgets)
						{
							var wid = widgets[j].split('.');
							addWidget(Number(i)+1, wid[0], wid[1]);
	    				}
	    			}
				}
				
				//menu show button
				$('.button#addWidget').click(function(){
					$(this).hide();
					$('.menu#widgetChooser').show('slow');
				});
				
				//load menu widgets list
{/literal}		var availableWidgets = {$availableWidgets};
{literal}
				var menu = $('.menu#widgetChooser');
				for(var plugin in availableWidgets)
				{
					var widgets = availableWidgets[plugin];
					for(var i in widgets)
					{
						menu.append('<div class="button menuItem" pluginToLoad="'+plugin+'" actionToLoad="'+widgets[i][1]+'">'+widgets[i][0] + ' => (' + plugin +'.'+ widgets[i][1] + ')</div>');
					}
				}
				
				//bind menu ui events
				$('.menuItem', menu).click(function(){
					menu.hide('slow');
					var plugin = $(this).attr('pluginToLoad');
					var action = $(this).attr('actionToLoad');
					addWidget(1, plugin, action);
					saveLayout();
					$('.button#addWidget').show();
				});
					
				//load every widgets
				//$('.items').each(function(){loadItem(this)});
		
				//add a dummy item on each columns
				$('.col').each(
					function()
					{
  						$(this).append('<div class="items dummyItem"><div class="handle dummyHandle"></div></div>');
  					});
				 				 
				 hideUnnecessaryDummies();
				 
				 makeSortable();
			}
		);
		
	function addWidget(colNumber, plugin, action)
	{
		var item = '<div class="items"><div plugin="'+plugin+'"'+' id="'+action+'" class="parentDiv"></div></div>';
	    $('.col#'+colNumber).append(item);
	    loadItem($('.items #'+action).parents('.items'));
		makeSortable();
	}

	function loadItem(domElem)
	{		
		//load every parentDiv with asynchronous ajax
		$('.parentDiv', domElem).each(
			function()
			{
				// get the ID of the div and load with ajax						
				ajaxLoading($(this).attr('plugin'), $(this).attr('id'));
			});
			
		//add an handle to each items
		$(domElem).prepend('<div class="handle"><div class="button" id="close"><img src="themes/default/images/close.png" /></div></div>');
			
		//Bind click event on close button
		$('.button#close', domElem).click(onDeleteItem);
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
			$(target).parents('.items').remove();
			ShowNecessaryDummies();
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
	
	function ShowNecessaryDummies()
	{
		showDummies();
		hideUnnecessaryDummies();
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
			var items = $('.items:not(.dummyItem) .parentDiv', this);
			var widgets = new Array;
			for(var i=0; i<items.size(); i++)
			{
				widgets.push($(items[i]).attr('plugin')+'.'+$(items[i]).attr('id'));
			}
			column.push(widgets);
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
{/literal}			idSite: {$idSite},
					period: '{$period}',
					date: '{$date}'					
{literal} 	}
		};
		$.ajax(ajaxRequest);
	}
	
</script>

<style type="text/css">
.col {
	float:left;
	width: 33%;
}

/*Overriding some dataTable css for better dashboard display*/
.parentDiv {
	width: 95%;
}
table.dataTable {
	width: 100%;
}	
#dataTableFeatures {
	width: 100%;
}

.hover {
	border: 2px dashed;
}

.items {
    background: white;
}

.handle {
	background: gray;
	width: 100%;
	height: 14px;
	cursor: move;
}

.dummyItem {
	width: 100%;
	height: 1px;
	display: block;
}

.button {
	cursor: pointer;
}

#close.button {
	float: right;
}

.dialog {
	display: none;
}

.menu {
	display: none;
}

.helper {
	width: 33%;
	opacity: .6;
	filter : alpha(opacity=60); /*for IE*/
}

.dummyHandle {
	display: none;
}

</style>

{/literal}


<div class="sortDiv">
 
	<div class="dialog" id="confirm"> 
	        <h2>Are you sure you want to delete this widget from your dashboard ?</h2> 
	        <input type="button" id="yes" value="Yes" /> 
	        <input type="button" id="no" value="No" /> 
	</div> 

	<div class="button" id="addWidget">
		Add a widget...
	</div>
	
	<div class="menu" id="widgetChooser">
	</div>

	<div class="col" id="1">
	</div>
  
	<div class="col" id="2">
	</div>
	
	<div class="col" id="3">
	</div>
</div>
