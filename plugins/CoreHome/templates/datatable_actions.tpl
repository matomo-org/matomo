<div id="{$properties.uniqueId}">
	<div class="dataTableActionsWrapper">
	{if isset($arrayDataTable.result) and $arrayDataTable.result == 'error'}
		{$arrayDataTable.message} 
	{else}
		{if count($arrayDataTable) == 0}
			<div id="emptyDatatable">{'CoreHome_TableNoData'|translate}</div>
		{else}
			<table cellspacing="0" class="dataTable dataTableActions"> 
			<thead>
			<tr>
			{foreach from=$dataTableColumns item=column}
				<th class="sortable" id="{$column.name}">{$column.displayName}</td>
			{/foreach}
			</tr>
			</thead>
			
			<tbody>
			{foreach from=$arrayDataTable item=row}
			<tr {if $row.idsubdatatable}class="rowToProcess subActionsDataTable" id="{$row.idsubdatatable}"{else} class="actionsDataTable rowToProcess"{/if}>
				{foreach from=$dataTableColumns item=column}
				<td>
				{* sometimes all columns are not set in the datatable, we assume the value 0 *}
				{if isset($row.columns[$column.name])}{$row.columns[$column.name]}{else}0{/if}
				</td>
				{/foreach}
			</tr>
			{/foreach}
			</tbody>
		</table>
		{/if}
	
		{if $properties.show_footer}
			{include file="CoreHome/templates/datatable_footer.tpl"}
		{/if}
		{include file="CoreHome/templates/datatable_actions_js.tpl"}
	{/if}
	</div>
</div>
