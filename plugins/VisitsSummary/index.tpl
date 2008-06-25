<script type="text/javascript" src="plugins/Home/templates/sparkline.js"></script>

<a name="evolutionGraph" graphId="VisitsSummarygetLastVisitsGraph"></a>
<h2>{'VisitsSummary_Evolution'|translate:$period}</h2>
{$graphEvolutionVisitsSummary}

<h2>{'VisitsSummary_Report'|translate}</h2>
{include file=VisitsSummary/sparklines.tpl}


<br /><br /><br />
<p style='color:lightgrey; size:0.8em;'>
{'VisitsSummary_GenerateTime'|translate:$totalTimeGeneration:$totalNumberOfQueries}
{if $totalNumberOfQueries != 0}, {'VisitsSummary_GenerateQueries'|translate:$totalNumberOfQueries}{/if}
</p>
