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
						piwik_DashboardLayout = 'getCountry~getKeywords|getPlugin|getSearchEngines';
					{/if}
				{literal}
				
				//generate dashboard layout
				var col = piwik_DashboardLayout.split('|');
				
				for(var i=0; i<col.length; i++)
				{
					if(col[i] != '')
					{
						var widgets = col[i].split('~');
						for(var j=0; j<widgets.length; j++)
						{
	    					$('.col#'+(i+1)).append('<div class="items"><div id="'+widgets[j]+'" class="parentDiv"></div></div>');
	    				}
	    			}
				}
				
				//add an handle to each items
				$('.items:not(.dummyItem)').each(
					function()
					{
						$(this).prepend('<div class="handle"><div class="button" id="close"><img src="themes/default/images/close.png" /></div></div>');
					});
					
				//add a dummy item on each columns
				$('.col').each(
					function()
					{
  						$(this).append('<div class="items dummyItem"><div class="handle dummyHandle"></div></div>');
  					});
  					
  				//load every parentDiv with asynchronous ajax
				$('.parentDiv').each(
					function()
					{
						// get the ID of the div and load with ajax						
						ajaxLoading($(this).attr('id'));
					});
					
				//launch 'sortable' property on every dashboard widgets
				$('.sortDiv').sortable({
				 	items:'.items',
				 	hoverClass: 'hover',
				 	handle: '.handle',
				 	helper: getHelper,
				 	start: onStart,
				 	stop: onStop
				 	});
				 	
				 //Bind click event on close button
				 $('.button#close').click(onDeleteItem);
				 				 
				 hideUnnecessaryDummies();
			}
		);
		
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
				widgets.push($(items[i]).attr('id'));
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
	
	function ajaxLoading(divId)
	{		
		// When ajax replied, we replace the right div with the response
		function onLoaded(response)
		{
			var content = $(response);
			$('#'+divId).html( $(content).html() );
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
			data: {	module: 'Home',
					action: divId,
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

.hover {
	border: 1px dashed;
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


	<div class="col" id="1">
	</div>
  
	<div class="col" id="2">
	</div>
	
	<div class="col" id="3">
	</div>
</div>
