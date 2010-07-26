<link rel="stylesheet" type="text/css" href="plugins/Goals/templates/goals.css" />
{include file="Goals/templates/title_and_evolution_graph.tpl"}

<div class="clear"></div>
{if $nb_conversions > 0}
    <h2>{'Goals_ConversionsOverview'|translate}</h2>
	<ul class="ulGoalTopElements">
    <li>{'Goals_BestCountries'|translate} {include file='Goals/templates/list_top_segment.tpl' topSegment=$topSegments.country}</li>
    {if count($topSegments.keyword)>0}<li>{'Goals_BestKeywords'|translate} {include file='Goals/templates/list_top_segment.tpl' topSegment=$topSegments.keyword}</li>{/if}
    {if count($topSegments.website)>0}<li>{'Goals_BestReferers'|translate} {include file='Goals/templates/list_top_segment.tpl' topSegment=$topSegments.website}</li>{/if}
    <li>{'Goals_ReturningVisitorsConversionRateIs'|translate:"<b>$conversion_rate_returning</b>"}, {'Goals_NewVisitorsConversionRateIs'|translate:"<b>$conversion_rate_new%</b>"}</li>
	</ul>
{/if}


{literal}
<script type="text/javascript">
$(document).ready( function() {
	$('.goalTopElement').tooltip();
});
</script>
{/literal}

{if $displayFullReport}
	{if $nb_conversions > 0}
		{include file="Goals/templates/table_by_segment.tpl"}
	{/if}
{/if}