<div id="{$id}" class="parentDivActions">
{if isset($arrayDataTable.result) and $arrayDataTable.result == 'error'}
	{$arrayDataTable.message} 
{else}
	{if count($arrayDataTable) == 0}
	No data for this table.
	{else}
		<table class="dataTable dataTableActions"> 
		<thead>
		<tr>
		{foreach from=$dataTableColumns item=column}
			<th class="sortable" id="{$column.id}">{$column.name}</td>
		{/foreach}
		</tr>
		</thead>
		
		<tbody>
		{foreach from=$arrayDataTable item=row}
		<tr {if $row.idsubdatatable}class="level{$row.level} rowToProcess subActionsDataTable" id="{$row.idsubdatatable}"{else}class="actionsDataTable rowToProcess level{$row.level}"{/if}>
			{foreach from=$dataTableColumns key=idColumn item=column}
			<td>
				{if isset($row.columns[$column.name])}{$row.columns[$column.name]}{else}0{/if}
			</td>
			{/foreach}
		</tr>
		{/foreach}
		</tbody>
		
		
		</foot>
		<tr><td colspan="{$dataTableColumns|@count}">
	
	</td>
	</tr>
	</tfoot>
	</table>
	{/if}

	{include file="Home/templates/datatable_footer.tpl"}
	{include file="Home/templates/datatable_actions_js.tpl"}
	
	{/if}
</div>