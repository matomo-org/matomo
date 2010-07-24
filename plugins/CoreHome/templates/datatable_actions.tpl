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
			{foreach from=$dataTableColumns item=column name=head}
				<th class="sortable {if $smarty.foreach.head.first}first{elseif $smarty.foreach.head.last}last{/if}" id="{$column}"><div id="thDIV">{if !empty($columnDescriptions[$column])}<label title='{$columnDescriptions[$column]|escape:'html'}'>{/if}{$columnTranslations[$column]|escape:'html'}{if !empty($columnDescriptions[$column])}</label>{/if}</div></td>
			{/foreach}
			</tr>
			</thead>
			
			<tbody>
			{foreach from=$arrayDataTable item=row}
			<tr {if $row.idsubdatatable}class="rowToProcess subActionsDataTable" id="{$row.idsubdatatable}"{else} class="actionsDataTable rowToProcess"{/if}>
				{foreach from=$dataTableColumns item=column}
				<td>
				{if !$row.idsubdatatable && $column=='label' && isset($row.metadata.url)}<span class="urlLink">{$row.metadata.url}</span>{/if}
				{if isset($row.columns[$column])}{$row.columns[$column]}{else}{$defaultWhenColumnValueNotDefined}{/if}
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
