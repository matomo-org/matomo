
<a name="evolutionGraph" graphId="VisitsSummarygetEvolutionGraph"></a>
<h2>{'VisitsSummary_EvolutionOverLastPeriods'|translate:$periodsNames.$period.plural}</h2>
{$graphEvolutionVisitsSummary}

<h2>{'VisitsSummary_Report'|translate}</h2>
{include file=VisitsSummary/templates/sparklines.tpl}

<br /><br /><br />
<p style='color:lightgrey; size:0.8em;'>
{'VisitsSummary_GenerateTime'|translate:$totalTimeGeneration:$totalNumberOfQueries}
{if $totalNumberOfQueries != 0}, {'VisitsSummary_GenerateQueries'|translate:$totalNumberOfQueries}{/if}
</p>
