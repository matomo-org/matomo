<tr id="{$id}"></tr>
{if isset($arrayDataTable.result) and $arrayDataTable.result == 'error'}
	{$arrayDataTable.message} 
{else}
	{if count($arrayDataTable) == 0}
	<tr><td colspan="{$nbColumns}">No data in this category. Try to "Include all population".</td></tr>
	{else}
		{foreach from=$arrayDataTable item=row}
		<tr {if $row.idsubdatatable}class="subActionsDataTable" id="{$row.idsubdatatable}"{/if}>
			{foreach from=$dataTableColumns key=idColumn item=column}
			<td>
				{$row.columns[$column.name]}
			</td>
			{/foreach}
		</tr>
		{/foreach}
	{/if}		
{/if}