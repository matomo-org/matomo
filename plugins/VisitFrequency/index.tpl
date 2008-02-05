{postEvent name="template_headerVisitsFrequency"}
<script type="text/javascript" src="plugins/Home/templates/sparkline.js"></script>

<a name="evolutionGraph" graphId="VisitFrequencygetLastVisitsReturningGraph"></a>
<h2>Evolution over the period</h2>
{$graphEvolutionVisitFrequency}

{include file=VisitFrequency/sparklines.tpl}
	
{postEvent name="template_footerVisitsFrequency"}