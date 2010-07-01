<a name="evolutionGraph" graphId="{$nameGraphEvolutionReferers}"></a>
<h2>{'Referers_Evolution'|translate}</h2>
{$graphEvolutionReferers}

<br />
<div id='leftcolumn'>
	<h2>{'Referers_Type'|translate}</h2>
	<div id='leftcolumn'>
			<div class="sparkline">{sparkline src=$urlSparklineDirectEntry}
			{'Referers_TypeDirectEntries'|translate:"<strong>$visitorsFromDirectEntry</strong>"}</div>
			<div class="sparkline">{sparkline src=$urlSparklineSearchEngines}
			{'Referers_TypeSearchEngines'|translate:"<strong>$visitorsFromSearchEngines</strong>"}</div>
	</div>
	<div id='rightcolumn'>
			<div class="sparkline">{sparkline src=$urlSparklineWebsites}
			{'Referers_TypeWebsites'|translate:"<strong>$visitorsFromWebsites</strong>"}</div>
			<div class="sparkline">{sparkline src=$urlSparklineCampaigns}
			{'Referers_TypeCampaigns'|translate:"<strong>$visitorsFromCampaigns</strong>"}</div>
	</div>
</div>

<div id='rightcolumn'>
	<h2>{'Referers_DetailsByRefererType'|translate}</h2>
	{$dataTableRefererType}
</div>

<div style="clear:both;"></div>

<p>View 
	<a href="javascript:broadcast.propagateAjax('module=Referers&action=getSearchEnginesAndKeywords')">{'Referers_SubmenuSearchEngines'|translate}</a>,
	<a href="javascript:broadcast.propagateAjax('module=Referers&action=getWebsites')">{'Referers_SubmenuWebsites'|translate}</a>,
	<a href="javascript:broadcast.propagateAjax('module=Referers&action=getCampaigns')">{'Referers_SubmenuCampaigns'|translate}</a>.
</p>
	

<h2>{'Referers_Distinct'|translate}</h2>
<table cellpadding="15">
<tr><td style="padding-right:50px">
	<div class="sparkline">{sparkline src=$urlSparklineDistinctSearchEngines}
	<strong>{$numberDistinctSearchEngines}</strong> {'Referers_DistinctSearchEngines'|translate}</div>
	<div class="sparkline">{sparkline src=$urlSparklineDistinctKeywords}
	<strong>{$numberDistinctKeywords}</strong> {'Referers_DistinctKeywords'|translate}</div>
</td>
	<div class="sparkline">{sparkline src=$urlSparklineDistinctWebsites}
	<strong>{$numberDistinctWebsites}</strong> {'Referers_DistinctWebsites'|translate} {'Referers_UsingNDistinctUrls'|translate:"<strong>$numberDistinctWebsitesUrls</strong>"}</div>
	<div class="sparkline">{sparkline src=$urlSparklineDistinctCampaigns} 
	<strong>{$numberDistinctCampaigns}</strong> {'Referers_DistinctCampaigns'|translate}</div>
</td></tr>
</table>

{include file=CoreHome/templates/sparkline_footer.tpl}

