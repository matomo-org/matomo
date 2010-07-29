<h2 id='titleGoalsBySegment'>{if isset($idGoal)}
	{'Goals_GoalConversionsBySegment'|translate:$goalName}
	{else}{'Goals_ConversionsOverviewBySegment'|translate}{/if}</h2> 

<div class='entityList goalSegments'>
	{foreach from=$goalSegments key=segmentFamilyName item=segments}
		<div class='segmentCategory'>
			{'Goals_ViewGoalsBySegment'|translate:$segmentFamilyName}
			<ul class='listCircle'>
			{foreach from=$segments item=segment}
				<li title='{'Goals_ViewGoalsBySegment'|translate:$segment.name}' class='goalSegment' module='{$segment.module}' action='{$segment.action}'>
					<span class='segment'>{$segment.name}</span>
				</li>
			{/foreach}
			</ul>
		</div>
	{/foreach}
</div>

<div style='float: left;'>
	{ajaxLoadingDiv id=tableGoalsLoading}
	
	<div id='tableGoalsBySegment'></div>
</div>
<div class="clear"></div>
{literal}
<script type="text/javascript">
$(document).ready( function() {
	var countLoaded = 0;
	/* 
	 * For each 'segment' in the list, a click will trigger an ajax request to load the datatable 
	 * showing Goals metrics (conversion, conv. rates, revenue) for this segment
	 */
	$('.goalSegment').click( function() {
		var self = this;
		$('.goalSegment').removeClass('activeSegment');
		$(this).addClass('activeSegment');
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
			$('#tableGoalsBySegment').html($(response));
			$('#tableGoalsLoading').hide();
			$('#tableGoalsBySegment').show();
			
			countLoaded++;
			// only scroll down to the loaded datatable if this is not the first one
			// otherwise, screen would jump down to the table when loading the report 
			if(countLoaded > 1)
			{
				piwikHelper.lazyScrollTo("#titleGoalsBySegment", 400);
			}
		};
		$('#tableGoalsBySegment').hide();
		$('#tableGoalsLoading').show();
		ajaxRequest = widgetsHelper.getLoadWidgetAjaxRequest(widgetUniqueId, widgetParameters, onWidgetLoadedCallback);
		$.ajax(ajaxRequest);
	});
	$('.goalSegment').first().click();
});
</script>
{/literal}
