<div id="multisites">
<div id="main">
{include file="MultiSites/templates/row.tpl" assign="row"}
<script type="text/javascript">
	var allSites = new Array();
	var params = new Array();
	{foreach from=$sitesData key=i item=site}
		allSites[{$i}] = new setRowData({$site.idsite}, {$site.visits}, {$site.actions}, {if empty($site.revenue)}0{else}{$site.revenue}{/if}, '{$site.name|escape:"javascript"}', '{$site.main_url|escape:"javascript"}', '{if isset($site.visits_evolution)}{$site.visits_evolution|replace:",":"."}{/if}', '{if isset($site.actions_evolution)}{$site.actions_evolution|replace:",":"."}{/if}', '{if isset($site.revenue_evolution)}{$site.revenue_evolution|replace:",":"."}{/if}');
	{/foreach}
	params['period'] = '{$period}';
	params['date'] = '{$dateRequest}';
	params['evolutionBy'] = '{$evolutionBy}';
	params['mOrderBy'] = '{$orderBy}';
	params['order'] = '{$order}';
	params['limit'] = '{$limit}';
	params['page'] = 1;
	params['prev'] = "{'General_Previous'|translate|escape:"javascript"}";
	params['next'] = "{'General_Next'|translate|escape:"javascript"}";
	params['row'] = '{$row|escape:"javascript"}';
	params['dateSparkline'] = '{$dateSparkline}';
</script>

{postEvent name="template_headerMultiSites"}

</div>

<div class="centerLargeDiv">

<h2>{'General_AllWebsitesDashboard'|translate} 
	<span class='smallTitle'>{'General_TotalVisitsActionsRevenue'|translate:"<strong>$totalVisits</strong>":"<strong>$totalActions</strong>":"<strong>$totalRevenue</strong>"}</span>
</h2>

<table id="mt" class="dataTable" cellspacing="0">
	<thead>
	<tr>
		<th id="names" class="label" onClick="params = setOrderBy(this,allSites, params, 'names');">
			<span>{'General_Website'|translate}</span>
			<span class="arrow {if $evolutionBy=='names'}multisites_{$order}{/if}"></span>
		</th>
		<th id="visits" class="multisites-column" style="width: 100px" onClick="params = setOrderBy(this,allSites, params, 'visits');">
			<span>{'General_ColumnNbVisits'|translate}</span>
			<span class="arrow {if $evolutionBy=='visits'}multisites_{$order}{/if}"></span>
		</th>
		<th id="actions" class="multisites-column" style="width: 110px" onClick="params = setOrderBy(this,allSites, params, 'actions');">
			<span>{'General_ColumnPageviews'|translate}</span>
			<span class="arrow {if $evolutionBy=='actions'}multisites_{$order}{/if}"></span>
		</th>
		{if $displayRevenueColumn}
		<th id="revenue" class="multisites-column" style="width: 110px" onClick="params = setOrderBy(this,allSites, params, 'revenue');">
			<span>{'Goals_ColumnRevenue'|translate}</span>
			<span class="arrow {if $evolutionBy=='revenue'}multisites_{$order}{/if}"></span>
		</th>
		{/if}
		<th id="evolution" style=" width:350px" colspan="{if $show_sparklines}2{else}1{/if}">
		<span class="arrow "></span>
			<span class="evolution" style="cursor:pointer;" onClick="params = setOrderBy(this,allSites, params, $('#evolution_selector').val() + 'Summary');"> {'MultiSites_Evolution'|translate}</span>
			<select class="selector" id="evolution_selector" onchange="params['evolutionBy'] = $('#evolution_selector').val(); switchEvolution(params);">
				<option value="visits" {if $evolutionBy eq 'visits'} selected {/if}>{'General_ColumnNbVisits'|translate}</option>
				<option value="actions" {if $evolutionBy eq 'actions'} selected {/if}>{'General_ColumnPageviews'|translate}</option>
				{if $displayRevenueColumn}<option value="revenue" {if $evolutionBy eq 'revenue'} selected {/if}>{'Goals_ColumnRevenue'|translate}</option>{/if}
			</select>
		</th>
	</tr>
	</thead>

	<tbody id="tb">
	</tbody>

	<tfoot>
	{if $isSuperUser}
	<tr>
		<td colspan="8" class="clean" style="text-align: right; padding-top: 15px;padding-right:10px">
			<a href="{url}&module=SitesManager&action=index&showaddsite=1"><img src='plugins/UsersManager/images/add.png' alt="" style="margin: 0;" /> {'SitesManager_AddSite'|translate}</a>
		</td>
	</tr>
	{/if}
	<tr row_id="last" >
		<td colspan="8" class="clean" style="padding: 20px">
		<span id="prev" class="pager"  style="padding-right: 20px;"></span>
		<span class="dataTablePages">
			<span id="counter">
		</span>
		</span>
		<span id="next" class="clean" style="padding-left: 20px;"></span>
	</td>
	</tr>
	</tfoot>
</table>
</div>
<script type="text/javascript">
prepareRows(allSites, params, '{$orderBy}');

{if $autoRefreshTodayReport}
piwikHelper.refreshAfter({$autoRefreshTodayReport} *1000);
{/if}
</script>
</div>
</div>

{include file="CoreHome/templates/piwik_tag.tpl"}
