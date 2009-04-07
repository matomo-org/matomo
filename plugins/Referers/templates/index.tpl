<script type="text/javascript" src="plugins/CoreHome/templates/sparkline.js"></script>

<a name="evolutionGraph" graphId="{$nameGraphEvolutionReferers}"></a>
<h2>{'Referers_Evolution'|translate}</h2>
{$graphEvolutionReferers}

<br/>
<div id='leftcolumn'>
	<h2>{'Referers_Type'|translate}</h2>
	<div id='leftcolumn'>
			<p>{sparkline src=$urlSparklineDirectEntry}<span>
			{'Referers_TypeDirectEntries'|translate:"<strong>$visitorsFromDirectEntry</strong>"}</span></p>
			<p>{sparkline src=$urlSparklineSearchEngines}<span>
			{'Referers_TypeSearchEngines'|translate:"<strong>$visitorsFromSearchEngines</strong>"}</span></p>
	</div>
	<div id='rightcolumn'>
			<p>{sparkline src=$urlSparklineWebsites}<span>
			{'Referers_TypeWebsites'|translate:"<strong>$visitorsFromWebsites</strong>"}</span></p>
			<p>{sparkline src=$urlSparklineCampaigns}<span>
			{'Referers_TypeCampaigns'|translate:"<strong>$visitorsFromCampaigns</strong>"}</span></p>
	</div>
</div>

<div id='rightcolumn'>
	<h2>{'Referers_DetailsByRefererType'|translate}</h2>
	{$dataTableRefererType}
</div>

<div style="clear:both" />
<h2>{'Referers_Other'|translate}</h2>
<table>
	<tr><td>
		<p>{sparkline src=$urlSparklineDistinctSearchEngines}<span>
		{'Referers_OtherDistinctSearchEngines'|translate:"<strong>$numberDistinctSearchEngines</strong>"}</span></p>
		<p>{sparkline src=$urlSparklineDistinctKeywords}<span>
		{'Referers_OtherDistinctKeywords'|translate:"<strong>$numberDistinctKeywords</strong>"}</span></p>
	</td><td>
		<p>{sparkline src=$urlSparklineDistinctWebsites}<span>
		{'Referers_OtherDistinctWebsites'|translate:"<strong>$numberDistinctWebsites</strong>":"<strong>$numberDistinctWebsitesUrls</strong>"}</span></p>
		<p>{sparkline src=$urlSparklineDistinctCampaigns}<span> 
		{'Referers_OtherDistinctCampaigns'|translate:"<strong>$numberDistinctCampaigns</strong>"}</span></p>
		</td></tr>
</table>
