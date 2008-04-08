<script type="text/javascript" src="plugins/Home/templates/sparkline.js"></script>

<a name="evolutionGraph" graphId="VisitsSummarygetLastVisitsGraph"></a>
<h2>Evolution on the last 30 {$period}s</h2>
{$graphEvolutionVisitsSummary}

<h2>Report</h2>
{include file=VisitsSummary/sparklines.tpl}


<br><br><br>
<p style='color:lightgrey; size:0.8em;'>{$totalTimeGeneration} seconds {if $totalNumberOfQueries != 0}/ {$totalNumberOfQueries}  queries{/if} to generate the page</p>
