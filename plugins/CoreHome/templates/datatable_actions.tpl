<div id="{$properties.uniqueId}" class="dataTable">
	<div class="reportDocumentation">
		{if !empty($reportDocumentation)}<p>{$reportDocumentation}</p>{/if}
		{if isset($properties.metadata.archived_date)}<span class='helpDate'>{$properties.metadata.archived_date}</span>{/if}
	</div>
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
				<th class="sortable {if $smarty.foreach.head.first}first{elseif $smarty.foreach.head.last}last{/if}" id="{$column}">
					{if !empty($columnDocumentation[$column])}
						<div class="columnDocumentation">
							<div class="columnDocumentationTitle">
								{$columnTranslations[$column]|escape:'html'|replace:"&amp;nbsp;":"&nbsp;"}
							</div>
							{$columnDocumentation[$column]|escape:'html'}
						</div>
					{/if}
					<div id="thDIV">{$columnTranslations[$column]|escape:'html'}</div>
				</th>
			{/foreach}
			</tr>
			</thead>
			
			<tbody>
			{foreach from=$arrayDataTable item=row}
			<tr {if $row.idsubdatatable}class="rowToProcess subActionsDataTable" id="{$row.idsubdatatable}"{else}class="actionsDataTable rowToProcess"{/if}>
			{foreach from=$dataTableColumns item=column}
			<td>
				{include file="CoreHome/templates/datatable_cell.tpl"}
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
