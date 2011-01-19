<h2 id='titleGoalsByDimension'>{if isset($idGoal)}
	{'Goals_GoalConversionsBy'|translate:$goalName}
	{else}{'Goals_ConversionsOverviewBy'|translate}{/if}</h2> 

<div class='entityList goalDimensions'>
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
		
		var idGoal = broadcast.getValueFromHash('idGoal');
		var widgetParameters = {
			'module': module,
			'action': action,
			'viewDataTable': 'tableGoals',
			'filter_only_display_idgoal': idGoal.length ? idGoal : 0 // 0 is Piwik_DataTable_Filter_AddColumnsProcessedMetricsGoal::GOALS_FULL_TABLE
		};
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
