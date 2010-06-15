<br/>
<h2 id='titleGoalsBySegment'>{if isset($idGoal)}
	{'Goals_GoalConversionsBySegment'|translate:$goalName}
	{else}{'Goals_ConversionsOverviewBySegment'|translate}{/if}</h2> 

<div class='segmentSelector' style='float: left;width: 220px;padding-left: 10px;height:450px'>
	{foreach from=$goalSegments key=segmentFamilyName item=segments}
		{'Goals_ViewGoalsBySegment'|translate:$segmentFamilyName}
		<ul>
		{foreach from=$segments item=segment}
			<li title='{'Goals_ViewGoalsBySegment'|translate:$segment.name}' class='goalSegment' module='{$segment.module}' action='{$segment.action}'>
				<span class='segment'>{$segment.name}</span>
			</li>
		{/foreach}
		</ul>
		<br/>
	{/foreach}
</div>

<div style='float: left;'>
	{ajaxLoadingDiv id=tableGoalsLoading}
	
	<div id='tableGoalsBySegment'></div>
</div>
<div style='clear:both'></div>
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
		
		var widgetParameters = {
			'module': module,
			'action': action,
			'viewDataTable': 'tableGoals',
			'idGoal': broadcast.getValueFromHash('idGoal')
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
