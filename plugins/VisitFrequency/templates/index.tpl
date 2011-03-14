{postEvent name="template_headerVisitsFrequency"}

<a name="evolutionGraph" graphId="VisitFrequencygetEvolutionGraph"></a>
<h2>{'VisitFrequency_ColumnReturningVisits'|translate}</h2>
{$graphEvolutionVisitFrequency}
<br />

{include file="VisitFrequency/templates/sparklines.tpl"}
	
{postEvent name="template_footerVisitsFrequency"}
