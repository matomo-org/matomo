<div id="{$id}" class="parentDivActions">
{if isset($arrayDataTable.result) and $arrayDataTable.result == 'error'}
	{$arrayDataTable.message} 
{else}
	{if count($arrayDataTable) == 0}
		<div id="emptyDatatable">{'Home_TableNoData'|translate}</div>
	{else}
		<table cellspacing="0" class="dataTable dataTableActions"> 
		<thead>
		<tr>
		{foreach from=$dataTableColumns item=column}
			<th class="sortable" id="{$column.id}">{$column.name}</td>
		{/foreach}
		</tr>
		</thead>
		
		<tbody>
		{foreach from=$arrayDataTable item=row}
		<tr {if $row.idsubdatatable}class="rowToProcess subActionsDataTable" id="{$row.idsubdatatable}"{else} class="actionsDataTable rowToProcess"{/if}>
			{foreach from=$dataTableColumns key=idColumn item=column}
			<td>
				{* sometimes all columns are not set in the datatable, we assume the value 0 *}
				{if isset($row.columns[$column.name])}{$row.columns[$column.name]}{else}0{/if}
			</td>
			{/foreach}
		</tr>
		{/foreach}
		</tbody>
		
	</tfoot>
	</table>
	{/if}

	{if $showFooter}
		{include file="Home/templates/datatable_footer.tpl"}
	{/if}
	{include file="Home/templates/datatable_actions_js.tpl"}
	
	{/if}
</div>
