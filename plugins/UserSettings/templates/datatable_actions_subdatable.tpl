<tr id="{$id}"></tr>
{if isset($dataTable.result) and $dataTable.result == 'error'}
	{$dataTable.message} 
{else}
	{if count($dataTable) == 0}
	<tr><td colspan="{$nbColumns}">No data in this category. Try to "Include all population".</td></tr>
	{else}
		{foreach from=$dataTable item=row}
		<tr {if $row.idsubdatatable}class="subActionsDataTable" id="{$row.idsubdatatable}"{/if}>
			{foreach from=$dataTableColumns key=idColumn item=column}
			<td>
				{if $idColumn==0 && isset($row.details.url)}<span id="urlLink">{$row.details.url}</span>{/if}
				{if $idColumn==0 && isset($row.details.logo)}<img src="{$row.details.logo}" />{/if}
				{if false && $idColumn==0}
					<span id="label">{$row.columns[$column.name]}</span>
				{else}
					{$row.columns[$column.name]}
				{/if}				
			</td>
			{/foreach}
		</tr>
		{/foreach}
	{/if}		
{/if}