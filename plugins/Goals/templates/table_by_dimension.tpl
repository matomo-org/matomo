<h2 id='titleGoalsByDimension'>{if isset($idGoal)}
	{'Goals_GoalConversionsBy'|translate:$goalName}
	{else}{'Goals_ConversionsOverviewBy'|translate}{/if}</h2> 

<div class='entityList goalDimensions'>
	{if isset($ecommerce)}
	<div class='dimensionCategory'>
		{'Goals_EcommerceReports'|translate}
		<ul class='listCircle'>
		<li class='goalDimension' module='Goals' action='getItemsSku'>
			<span class='dimension'>{'Goals_ProductSKU'|translate}</span>
		</li>
		<li class='goalDimension' module='Goals' action='getItemsName'>
			<span class='dimension'>{'Goals_ProductName'|translate}</span>
		</li>
		</li>
		<li class='goalDimension' module='Goals' action='getItemsCategory'>
			<span class='dimension'>{'Goals_ProductCategory'|translate}</span>
		</li>
		<li class='goalDimension' module='Goals' action='getEcommerceLog'>
			<span class='dimension'>{'Goals_EcommerceLog'|translate}</span>
		</li>
		</ul>
	</div>
	{/if}
	{foreach from=$goalDimensions key=dimensionFamilyName item=dimensions}
		<div class='dimensionCategory'>
			{'Goals_ViewGoalsBy'|translate:$dimensionFamilyName}
			<ul class='listCircle'>
			{foreach from=$dimensions item=dimension}
				<li title='{'Goals_ViewGoalsBy'|translate:$dimension.name}' class='goalDimension' module='{$dimension.module}' action='{$dimension.action}'>
					<span class='dimension'>{$dimension.name}</span>
				</li>
			{/foreach}
			</ul>
		</div>
	{/foreach}
</div>

<div style='float: left;'>
	{ajaxLoadingDiv id=tableGoalsLoading}
	
	<div id='tableGoalsByDimension'></div>
</div>
<div class="clear"></div>
{literal}
<script type="text/javascript">
$(document).ready( function() {
	var countLoaded = 0;
	/* 
	 * For each 'dimension' in the list, a click will trigger an ajax request to load the datatable 
	 * showing Goals metrics (conversion, conv. rates, revenue) for this dimension
	 */
	$('.goalDimension').click( function() {
		var self = this;
		$('.goalDimension').removeClass('activeDimension');
		$(this).addClass('activeDimension');
		var module = $(this).attr('module');
		var action = $(this).attr('action');
		widgetUniqueId = module+action;
		self.expectedWidgetUniqueId = widgetUniqueId;
		
		var widgetParameters = {
			'module': module,
			'action': action
		};
		var idGoal = broadcast.getValueFromHash('idGoal');
		widgetParameters['idGoal'] = idGoal.length ? idGoal : 0; //Piwik_DataTable_Filter_AddColumnsProcessedMetricsGoal::GOALS_FULL_TABLE;
		
		// Loading segment table means loading Goals view for Top Countries/etc.
		if(module != 'Goals') {
			widgetParameters['viewDataTable'] = 'tableGoals';
			// 0 is Piwik_DataTable_Filter_AddColumnsProcessedMetricsGoal::GOALS_FULL_TABLE
			widgetParameters['documentationForGoalsPage'] = 1;
		}
		var onWidgetLoadedCallback = function (response) {
			if(widgetUniqueId != self.expectedWidgetUniqueId) {
				return;
			}
			$('#tableGoalsByDimension').html($(response));
			$('#tableGoalsLoading').hide();
			$('#tableGoalsByDimension').show();
			
			countLoaded++;
			// only scroll down to the loaded datatable if this is not the first one
			// otherwise, screen would jump down to the table when loading the report 
			if(countLoaded > 1)
			{
				piwikHelper.lazyScrollTo("#titleGoalsByDimension", 400);
			}
		};
		$('#tableGoalsByDimension').hide();
		$('#tableGoalsLoading').show();

		ajaxRequest = widgetsHelper.getLoadWidgetAjaxRequest(widgetUniqueId, widgetParameters, onWidgetLoadedCallback);
		$.ajax(ajaxRequest);
	});
	$('.goalDimension').first().click();
});
</script>
{/literal}
