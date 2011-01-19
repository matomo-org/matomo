<link rel="stylesheet" type="text/css" href="plugins/Goals/templates/goals.css" />
{include file="Goals/templates/title_and_evolution_graph.tpl"}

<div class="clear"></div>
{if $nb_conversions > 0}
    <h2>{'Goals_ConversionsOverview'|translate}</h2>
	<ul class="ulGoalTopElements">
    <li>{'Goals_BestCountries'|translate} {include file='Goals/templates/list_top_dimension.tpl' topDimension=$topDimensions.country}</li>
    {if count($topDimensions.keyword)>0}<li>{'Goals_BestKeywords'|translate} {include file='Goals/templates/list_top_dimension.tpl' topDimension=$topDimensions.keyword}</li>{/if}
    {if count($topDimensions.website)>0}<li>{'Goals_BestReferers'|translate} {include file='Goals/templates/list_top_dimension.tpl' topDimension=$topDimensions.website}</li>{/if}
    <li>{'Goals_ReturningVisitorsConversionRateIs'|translate:"<b>$conversion_rate_returning</b>"}, {'Goals_NewVisitorsConversionRateIs'|translate:"<b>$conversion_rate_new</b>"}</li>
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
		{include file="Goals/templates/table_by_dimension.tpl"}
	{/if}
{/if}
