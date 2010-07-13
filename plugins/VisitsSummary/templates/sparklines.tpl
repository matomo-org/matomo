<div id='leftcolumn'>
	<div class="sparkline">{sparkline src=$urlSparklineNbVisits} {'VisitsSummary_NbVisits'|translate:"<strong>$nbVisits</strong>"}</div>
	<div class="sparkline">{sparkline src=$urlSparklineNbUniqVisitors} {'VisitsSummary_NbUniqueVisitors'|translate:"<strong>$nbUniqVisitors</strong>"}</div>
	<div class="sparkline">{sparkline src=$urlSparklineNbActions} {'VisitsSummary_NbActionsDescription'|translate:"<strong>$nbActions</strong>"}</div>
	<div class="sparkline">{sparkline src=$urlSparklineActionsPerVisit} {'VisitsSummary_NbActionsPerVisit'|translate:"<strong>$nbActionsPerVisit</strong>"}</div>
</div>
<div id='rightcolumn'>
	<div class="sparkline">{sparkline src=$urlSparklineAvgVisitDuration} {assign var=averageVisitDuration value=$averageVisitDuration|sumtime} {'VisitsSummary_AverageVisitDuration'|translate:"<strong>$averageVisitDuration</strong>"}</div>
	<div class="sparkline">{sparkline src=$urlSparklineBounceRate} {'VisitsSummary_NbVisitsBounced'|translate:"<strong>$bounceRate%</strong>"}</div>
	<div class="sparkline">{sparkline src=$urlSparklineMaxActions} {'VisitsSummary_MaxNbActions'|translate:"<strong>$maxActions</strong>"}</div>
</div>
<div style="clear:both;"></div>

{include file=CoreHome/templates/sparkline_footer.tpl}

