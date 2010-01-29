
{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=true}
{include file="CoreHome/templates/header.tpl"}

<script type="text/javascript" src="plugins/MultiSites/templates/common.js"></script>
<style>
{fetch file="plugins/MultiSites/templates/styles.css"}
</style>

<div id="multisites" style="margin: auto">
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
	params['row'] = "{$row|escape:"javascript"}";
	params['arrow_desc'] = '<span id="arrow_desc" class="desc">{$arrowDesc}</span>';
	params['arrow_asc'] = '<span id="arrow_asc" class="asc">{$arrowAsc}</span>';
</script>

{postEvent name="template_headerMultiSites"}
<table id="mt" class="dataTable" cellspacing="0" style="width:850px;margin: auto">
	<thead>
		<th class="label" style="text-align:center">
			<span style="cursor:pointer;" onClick="params = setOrderBy(this,allSites, params, 'names');">{'General_Website'|translate}</span>
		</th>
		<th class="multisites-column">
			<span style="cursor:pointer;" onClick="params = setOrderBy(this,allSites, params, 'visits');">{'General_ColumnNbVisits'|translate}</span>
		</th>
		<th class="multisites-column">
			<span style="cursor:pointer;" onClick="params = setOrderBy(this,allSites, params, 'actions');">{'General_ColumnPageviews'|translate}</span>
		</th>
		<th class="multisites-column">
			<span style="cursor:pointer;" onClick="params = setOrderBy(this,allSites, params, 'unique');">{'General_ColumnNbUniqVisitors'|translate}</span>
		</th>
		<th style="text-align:center;width:350px" colspan="2">
			<span style="cursor:pointer;" onClick="params = setOrderBy(this,allSites, params, $('#evolution_selector').val() + 'Summary');"> Evolution</span>
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
	<tr row_id="last">
		<td colspan="8" class="clean">
		<span id="prev" class="pager"  style="padding-right: 20px;"></span>
		<div id="dataTablePages">
			<span id="counter">
			</span>
		</div>
		<span id="next" class="clean" style="padding-left: 20px;"></span>
	</td>
	</tr>
	</tfoot>
</table>

<script type="text/javascript">
prepareRows(allSites, params, '{$orderBy}');
</script>
</div>
</div>

</body>
</html>
