<div id="{$id}" class="parentDivActions">
{if isset($arrayDataTable.result) and $arrayDataTable.result == 'error'}
	{$arrayDataTable.message} 
{else}
	{if count($arrayDataTable) == 0}
	No data for this table.
	{else}
		<table class="dataTable"> 
		<thead>
		<tr>
		{foreach from=$dataTableColumns item=column}
			<th class="sortable" id="{$column.id}">{$column.name}</td>
		{/foreach}
		</tr>
		</thead>
		
		<tbody>
		{foreach from=$arrayDataTable item=row}
		<tr {if $row.idsubdatatable}class="rowToProcess subActionsDataTable" id="{$row.idsubdatatable}"{else} class="rowToProcess"{/if}>
			{foreach from=$dataTableColumns key=idColumn item=column}
			<td>
				{$row.columns[$column.name]}
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
