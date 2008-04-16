<tr id="{$id}"></tr>
{if isset($arrayDataTable.result) and $arrayDataTable.result == 'error'}
	{$arrayDataTable.message} 
{else}
	{if count($arrayDataTable) == 0}
	<tr><td colspan="{$nbColumns}">{'Home_CategoryNoData'|translate}</td></tr>
	{else}
		{foreach from=$arrayDataTable item=row}
		<tr {if $row.idsubdatatable}class="subActionsDataTable" id="{$row.idsubdatatable}"{else}class="actionsDataTable"{/if}>
			{foreach from=$dataTableColumns key=idColumn item=column}
			<td>
				{* sometimes all columns are not set in the datatable, we assume the value 0 *}
				{if isset($row.columns[$column.name])}{$row.columns[$column.name]}{else}0{/if}
			</td>
			{/foreach}
		</tr>
		{/foreach}
	{/if}		
{/if}
