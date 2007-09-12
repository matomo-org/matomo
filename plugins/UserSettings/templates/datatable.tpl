<div id="{$id}" class="parentDiv">
{if isset($dataTable.result) and $dataTable.result == 'error'}
	{$dataTable.message} 
{else}
	{if count($dataTable) == 0}
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
		{foreach from=$dataTable item=row}
		<tr {if $row.idsubdatatable}class="subDataTable" id="{$row.idsubdatatable}"{/if}>
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
		</tbody>
		</table>
	{/if}
	{include file="UserSettings/templates/datatable_footer.tpl"}
	
{/if}
</div>