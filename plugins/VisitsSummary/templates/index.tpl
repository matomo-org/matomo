{* This graphId must be unique for this report *}
<a name="evolutionGraph" graphId="VisitsSummarygetEvolutionGraph"></a>

<h2>{if $period=='range'}{'Referers_Evolution'|translate}
	{else}{'VisitsSummary_EvolutionOverLastPeriods'|translate:$periodsNames.$period.plural}{/if}
</h2>
{$graphEvolutionVisitsSummary}

<h2>{'General_Report'|translate}</h2>
{include file="VisitsSummary/templates/sparklines.tpl"}
{*
Time page generation
	<p style='color:lightgrey; size:0.8em;'>
	{'VisitsSummary_GenerateTime'|translate:$totalTimeGeneration:$totalNumberOfQueries}
	{if $totalNumberOfQueries != 0}, {'VisitsSummary_GenerateQueries'|translate:$totalNumberOfQueries}{/if}
	</p>
*}
