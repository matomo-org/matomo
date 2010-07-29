
<a name="evolutionGraph" graphId="VisitsSummarygetEvolutionGraph"></a>
<h2>{'VisitsSummary_EvolutionOverLastPeriods'|translate:$periodsNames.$period.plural}</h2>
{$graphEvolutionVisitsSummary}

<h2>{'General_Report'|translate}</h2>
{include file=VisitsSummary/templates/sparklines.tpl}
{*
Time page generation
	<p style='color:lightgrey; size:0.8em;'>
	{'VisitsSummary_GenerateTime'|translate:$totalTimeGeneration:$totalNumberOfQueries}
	{if $totalNumberOfQueries != 0}, {'VisitsSummary_GenerateQueries'|translate:$totalNumberOfQueries}{/if}
	</p>
*}