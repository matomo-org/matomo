<div id='leftcolumn'>
	<div class="sparkline">{sparkline src=$urlSparklineNbVisits} {'VisitsSummary_NbVisits'|translate:"<strong>$nbVisits</strong>"}</div>
	{if isset($urlSparklineNbUniqVisitors)}
	<div class="sparkline">{sparkline src=$urlSparklineNbUniqVisitors} {'VisitsSummary_NbUniqueVisitors'|translate:"<strong>$nbUniqVisitors</strong>"}</div>
	{/if}
	<div class="sparkline">{sparkline src=$urlSparklineNbActions} {'VisitsSummary_NbActionsDescription'|translate:"<strong>$nbActions</strong>"}</div>
</div>
<div id='rightcolumn'>
	<div class="sparkline">{sparkline src=$urlSparklineSumVisitLength} {assign var=sumtimeVisitLength value=$sumVisitLength|sumtime} {'VisitsSummary_TotalTime'|translate:"<strong>$sumtimeVisitLength</strong>"}</div>
	<div class="sparkline">{sparkline src=$urlSparklineMaxActions} {'VisitsSummary_MaxNbActions'|translate:"<strong>$maxActions</strong>"}</div>
	<div class="sparkline">{sparkline src=$urlSparklineBounceRate} {'VisitsSummary_NbVisitsBounced'|translate:"<strong>$bounceRate%</strong>"}</div>
</div>
<div style="clear:both;"></div>

{include file=CoreHome/templates/sparkline_footer.tpl}

