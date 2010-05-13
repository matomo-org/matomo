{postEvent name="template_headerVisitsFrequency"}
<script type="text/javascript" src="plugins/CoreHome/templates/sparkline.js"></script>

<a name="evolutionGraph" graphId="VisitFrequencygetEvolutionGraph"></a>
<h2>{'VisitFrequency_ColumnReturningVisits'|translate}</h2>
{$graphEvolutionVisitFrequency}
<br />

{include file=VisitFrequency/templates/sparklines.tpl}
	
{postEvent name="template_footerVisitsFrequency"}
