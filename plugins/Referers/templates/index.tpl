<a name="evolutionGraph" graphId="{$nameGraphEvolutionReferers}"></a>
<h2>{'Referers_Evolution'|translate}</h2>
{$graphEvolutionReferers}

<br />
<div id='leftcolumn' style="position:relative">
	<h2>{'Referers_Type'|translate}</h2>
	<div id='leftcolumn'>
			<div class="sparkline">{sparkline src=$urlSparklineDirectEntry}
				{'Referers_TypeDirectEntries'|translate:"<strong>$visitorsFromDirectEntry</strong>"}{if !empty($visitorsFromDirectEntryPercent)}, <strong>{$visitorsFromDirectEntryPercent}%</strong> of visits{/if}{if !empty($visitorsFromDirectEntryEvolution)}, {$visitorsFromDirectEntryEvolution}{/if}
			</div>
			<div class="sparkline">{sparkline src=$urlSparklineSearchEngines}
				{'Referers_TypeSearchEngines'|translate:"<strong>$visitorsFromSearchEngines</strong>"}{if !empty($visitorsFromSearchEnginesPercent)}, <strong>{$visitorsFromSearchEnginesPercent}%</strong> of visits{/if}{if !empty($visitorsFromSearchEnginesEvolution)}, {$visitorsFromSearchEnginesEvolution}{/if}
			</div>
	</div>
	<div id='rightcolumn'>
			<div class="sparkline">{sparkline src=$urlSparklineWebsites}
				{'Referers_TypeWebsites'|translate:"<strong>$visitorsFromWebsites</strong>"}{if !empty($visitorsFromWebsitesPercent)}, <strong>{$visitorsFromWebsitesPercent}%</strong> of visits{/if}{if !empty($visitorsFromWebsitesEvolution)}, {$visitorsFromWebsitesEvolution}{/if}
			</div>
			<div class="sparkline">{sparkline src=$urlSparklineCampaigns}
				{'Referers_TypeCampaigns'|translate:"<strong>$visitorsFromCampaigns</strong>"}{if !empty($visitorsFromCampaignsPercent)}, <strong>{$visitorsFromCampaignsPercent}%</strong> of visits{/if}{if !empty($visitorsFromCampaignsEvolution)}, {$visitorsFromCampaignsEvolution}{/if}
			</div>
	</div>
	
	<div style="clear:both" />
	
	<p style="float:left">
		<br/><br/>
		<strong>{'General_MoreSparklines'|translate}</strong>&nbsp;<a href="#" class="section-toggler-link" data-section-id="distinctReferrersByType">({'General_Show_js'|translate})</a>
	</p>

	<div id="distinctReferrersByType" style="display:none;float:left">
	<table cellpadding="15">
	<tr><td width="50%">
		<div class="sparkline">{sparkline src=$urlSparklineDistinctSearchEngines}
			<strong>{$numberDistinctSearchEngines}</strong> {'Referers_DistinctSearchEngines'|translate}{if !empty($numberDistinctSearchEnginesEvolution)}, {$numberDistinctSearchEnginesEvolution}{/if}
		</div>
		<div class="sparkline">{sparkline src=$urlSparklineDistinctKeywords}
			<strong>{$numberDistinctKeywords}</strong> {'Referers_DistinctKeywords'|translate}{if !empty($numberDistinctKeywordsEvolution)}, {$numberDistinctKeywordsEvolution}{/if}
		</div>
	</td>
	<td width="50%">
		<div class="sparkline">{sparkline src=$urlSparklineDistinctWebsites}
			<strong>{$numberDistinctWebsites}</strong> {'Referers_DistinctWebsites'|translate} {'Referers_UsingNDistinctUrls'|translate:"<strong>$numberDistinctWebsitesUrls</strong>"}{if !empty($numberDistinctWebsitesEvolution)}, {$numberDistinctWebsitesEvolution}{/if}
		</div>
		<div class="sparkline">{sparkline src=$urlSparklineDistinctCampaigns} 
			<strong>{$numberDistinctCampaigns}</strong> {'Referers_DistinctCampaigns'|translate}{if !empty($numberDistinctCampaignsEvolution)}, {$numberDistinctCampaignsEvolution}{/if}
		</div>
	</td></tr>
	</table>
	<br/><br/>
	</div>
</div>

<div id='rightcolumn'>
	<h2>{'Referers_DetailsByRefererType'|translate}</h2>
	{$dataTableRefererType}
</div>

<div style="clear:both;"></div>

<p>{'General_View'|translate} 
	<a href="javascript:broadcast.propagateAjax('module=Referers&action=getSearchEnginesAndKeywords')">{'Referers_SubmenuSearchEngines'|translate}</a>,
	<a href="javascript:broadcast.propagateAjax('module=Referers&action=indexWebsites')">{'Referers_SubmenuWebsites'|translate}</a>,
	<a href="javascript:broadcast.propagateAjax('module=Referers&action=indexCampaigns')">{'Referers_SubmenuCampaigns'|translate}</a>.
</p>

{include file="CoreHome/templates/sparkline_footer.tpl"}

