
<div id="Insight_Container">

	<div id="Insight_Location">&nbsp;</div>
	
	<div id="Insight_Sidebar">
		
	</div>
	<div id="Insight_Main">
		<iframe 
				id="Insight_Iframe" 
				src="index.php?module=Insight&action=startInsightSession&idsite={$idSite}&period={$period}&date={$date}&urls={$urls}">
		</iframe>
	</div>
	
</div>


<script type="text/javascript">
	Piwik_Insight.init();
</script>