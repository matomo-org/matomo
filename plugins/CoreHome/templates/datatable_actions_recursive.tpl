<div id="{$properties.uniqueId}">
	<div class="dataTableActionsWrapper">
	{if isset($arrayDataTable.result) and $arrayDataTable.result == 'error'}
		{$arrayDataTable.message} 
	{else}
		{if count($arrayDataTable) == 0}
			<div class="pk-emptyDataTable">{'CoreHome_ThereIsNoDataForThisReport'|translate}</div>
		{else}
			<table cellspacing="0" class="dataTable dataTableActions"> 
			<thead>
			<tr>
			{foreach from=$dataTableColumns item=column}
				<th class="sortable" id="{$column}">{$columnTranslations[$column]}</td>
			{/foreach}
			</tr>
			</thead>
			
			<tbody>
			{foreach from=$arrayDataTable item=row}
			<tr {if $row.idsubdatatable}class="level{$row.level} rowToProcess subActionsDataTable" id="{$row.idsubdatatable}"{else}class="actionsDataTable rowToProcess level{$row.level}"{/if}>
				{foreach from=$dataTableColumns item=column}
				<td>
					{if isset($row.columns[$column])}{$row.columns[$column]}{else}{$defaultWhenColumnValueNotDefined}{/if}
				</td>
				{/foreach}
			</tr>
			{/foreach}
			</tbody>
		</table>
		{/if}
	
		{include file="CoreHome/templates/datatable_footer.tpl"}
		{include file="CoreHome/templates/datatable_actions_js.tpl"}
	{/if}
	</div>
</div>
