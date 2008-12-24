{include file="Goals/templates/title_and_evolution_graph.tpl"}

{if $nb_conversions > 0}
	<h2>Conversions Overview</h2>
	<ul class="ulGoalTopElements">
	<li>Your best converting countries are: {include file='Goals/templates/list_top_segment.tpl' topSegment=$topSegments.country}</li>
	<li>Your top converting keywords are: {include file='Goals/templates/list_top_segment.tpl' topSegment=$topSegments.keyword}</li>
	<li>Your best converting websites referers are: {include file='Goals/templates/list_top_segment.tpl' topSegment=$topSegments.website}</li>
	<li>Returning visitors conversion rate is <b>{$conversion_rate_returning}%</b>, New Visitors conversion rate is <b>{$conversion_rate_new}%</b></li>
	</ul>
{/if}


{literal}
<style>
ul.ulGoalTopElements {
	list-style-type:circle;
	margin-left:30px;
}
.ulGoalTopElements a {
	text-decoration:none;
	color:#0033CC;
	border-bottom:1px dotted #0033CC;
	line-height:2em;
}
.goalTopElement { 
	border-bottom:1px dotted; 
} 
</style>
<script>
$(document).ready( function() {
	$('.goalTopElement')
		.Tooltip()
		;
	});
</script>
{/literal}