<span id="loadingDataTable"><img src="{$piwikUrl}themes/default/images/loading-blue.gif" /> {'General_LoadingData'|translate}</span>
<div id="multisites" style="margin: auto">
<div id="main">
{include file='MultiSites/templates/row.tpl' assign=row}
<script type="text/javascript">
var allSites = new Array();
var params = new Array();
{foreach from=$mySites key=i item=site}
	allSites[{$i}] = new setRowData({$site.idsite}, {$site.visits}, {$site.actions}, {$site.unique}, '{$site.name}', '{$site.main_url}', '{$site.visitsSummaryValue|replace:",":"."}', '{$site.actionsSummaryValue|replace:",":"."}', '{$site.uniqueSummaryValue|replace:",":"."}');
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
<table id="mt" class="dataTable" cellspacing="0" style="width: 90%; margin: auto">
<thead>
<th width="30px" class="label"></th>
<th width="30px"></th>
<th style="text-align:center">
	<span style="cursor:pointer;" onClick="params = setOrderBy(allSites, params, 'names');">{'General_ColumnLabel'|translate}</span><span id="names_asc" class="asc" style="display: none;">{$arrowDown}</span><span id="names_desc" class="desc" style="display: none;">{$arrowUp}</span >
</th>
<th style="text-align:center">
	<span style="cursor:pointer;" onClick="params = setOrderBy(allSites, params, 'visits');">{'General_ColumnNbVisits'|translate}</span><span id="visits_asc" class="asc" style="display: none;">{$arrowDown}</span><span id="visits_desc" class="desc" style="display: none;">{$arrowUp}</span>
</th>
<th style="text-align:center">
	<span style="cursor:pointer;" onClick="params = setOrderBy(allSites, params, 'actions');">{'General_ColumnPageviews'|translate}</span><span id="actions_asc" class="asc" style="display: none;">{$arrowDown}</span><span id="actions_desc" class="desc" style="display: none;">{$arrowUp}</span>
</th>
<th style="text-align:center">
	<span style="cursor:pointer;" onClick="params = setOrderBy(allSites, params, 'unique');">{'General_ColumnNbUniqVisitors'|translate}</span><span id="unique_asc" class="asc" style="display: none;">{$arrowDown}</span><span id="unique_desc" class="desc" style="display: none;">{$arrowUp}</span>
</th>
<th style="text-align:center" colspan="2">
	<span style="cursor:pointer;" onClick="params = setOrderBy(allSites, params, $('#evolution_selector').val() + '_summary');"> Evolution</span><span id="evolution_asc" class="asc" style="display: none;">{$arrowDown}</span><span id="evolution_desc" class="desc" style="display: none;">{$arrowUp}</span>
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
<td colspan="8" style="text-align: center" class="clean">
<span id="prev" class="pager"  style="padding-right: 20px;"></span>
<span id="counter">
</span>
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
