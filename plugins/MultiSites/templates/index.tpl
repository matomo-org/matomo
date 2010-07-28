{assign var=showSitesSelection value=false}
{include file="CoreHome/templates/header.tpl"}

<div id="multisites">
<div id="main">
{include file="MultiSites/templates/row.tpl" assign="row"}
<script type="text/javascript">
	var allSites = new Array();
	var params = new Array();
	{foreach from=$mySites key=i item=site}
		allSites[{$i}] = new setRowData({$site.idsite}, {$site.visits}, {$site.actions}, {$site.unique}, '{$site.name|escape:"quotes"}', '{$site.main_url}', '{$site.visitsSummaryValue|replace:",":"."}', '{$site.actionsSummaryValue|replace:",":"."}', '{$site.uniqueSummaryValue|replace:",":"."}');
	{/foreach}
	params['period'] = '{$period}';
	params['date'] = '{$date}';
	params['dateToStr'] = '{$dateToStr}';
	params['evolutionBy'] = '{$evolutionBy}';
	params['mOrderBy'] = '{$orderBy}';
	params['order'] = '{$order}';
	params['site'] = '{$site}';
	params['limit'] = '{$limit}';
	params['page'] = 1;
	params['prev'] = "{'General_Previous'|translate}";
	params['next'] = "{'General_Next'|translate}";
	params['row'] = '{$row|escape:"javascript"}';
</script>

{postEvent name="template_headerMultiSites"}

<div class="top_controls_inner">
    {include file="CoreHome/templates/period_select.tpl"}
    {include file="CoreHome/templates/header_message.tpl"}
</div>

<div class="centerLargeDiv">

<h2>{'General_AllWebsitesDashboard'|translate}</h2>

<table id="mt" class="dataTable" cellspacing="0">
	<thead>
		<th id="names" class="label" onClick="params = setOrderBy(this,allSites, params, 'names');">
			<span>{'General_Website'|translate}</span>
			<span class="arrow multisites_desc"></span>
		</th>
		<th id="visits" class="multisites-column" style="width: 100px" onClick="params = setOrderBy(this,allSites, params, 'visits');">
			<span>{'General_ColumnNbVisits'|translate}</span>
			<span class="arrow"></span>
		</th>
		<th id="actions" class="multisites-column" style="width: 110px" onClick="params = setOrderBy(this,allSites, params, 'actions');">
			<span>{'General_ColumnPageviews'|translate}</span>
			<span class="arrow"></span>
		</th>
		<th id="unique" class="multisites-column" style="width: 120px" onClick="params = setOrderBy(this,allSites, params, 'unique');">
			<span>{'General_ColumnNbUniqVisitors'|translate}</span>
			<span class="arrow"></span>
		</th>
		<th id="evolution" style=" width:350px" colspan="2">
		<span class="arrow "></span>
			<span class="evolution" style="cursor:pointer;" onClick="params = setOrderBy(this,allSites, params, $('#evolution_selector').val() + 'Summary');"> {'MultiSites_Evolution'|translate}</span>
			<select class="selector" id="evolution_selector" onchange="params['evolutionBy'] = $('#evolution_selector').val(); switchEvolution(params);">
				<option value="visits" {if $evolutionBy eq 'visits'} selected {/if}>{'General_ColumnNbVisits'|translate}</option>
				<option value="actions" {if $evolutionBy eq 'actions'} selected {/if}>{'General_ColumnPageviews'|translate}</option>
				<option value="unique"{if $evolutionBy eq 'unique'} selected {/if}>{'General_ColumnNbUniqVisitors'|translate}</option>
			</select>
		</th>
	</thead>

	<tbody id="tb">
	</tbody>

	<tfoot>
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
{literal}
function refreshAfter(timeoutPeriod) {
	setTimeout("location.reload(true);",timeoutPeriod);
}
refreshAfter(5*60*1000);
{/literal}
{/if}
</script>
</div>
</div>

{include file="CoreHome/templates/piwik_tag.tpl"}

</div>
</body>
</html>
