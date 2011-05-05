<div id="{$properties.uniqueId}">
	{if !empty($reportDocumentation)}
		<div class="reportDocumentation"><p>{$reportDocumentation}</p></div>
	{/if}
	<div class="{if isset($javascriptVariablesToSet.idSubtable)&& $javascriptVariablesToSet.idSubtable!=0}sub{/if}{if $javascriptVariablesToSet.viewDataTable=='tableAllColumns'}dataTableAllColumnsWrapper{elseif $javascriptVariablesToSet.viewDataTable=='tableGoals'}dataTableAllColumnsWrapper{else}dataTableWrapper{/if}">
	{if isset($arrayDataTable.result) and $arrayDataTable.result == 'error'}
		{$arrayDataTable.message}
	{else}
		{if count($arrayDataTable) == 0}
		<div class="pk-emptyDataTable">{'CoreHome_ThereIsNoDataForThisReport'|translate}</div>
		{else}
			<a name="{$properties.uniqueId}"></a>
			<table cellspacing="0" class="dataTable"> 
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
					<div id="thDIV">{$columnTranslations[$column]|escape:'html'|replace:"&amp;nbsp;":"&nbsp;"}</div>
				</th>
			{/foreach}
			</tr>
			</thead>
			
			<tbody>
			{foreach from=$arrayDataTable item=row}
			<tr {if $row.idsubdatatable && $javascriptVariablesToSet.controllerActionCalledWhenRequestSubTable != null}class="subDataTable" id="{$row.idsubdatatable}"{/if}>
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
		{include file="CoreHome/templates/datatable_js.tpl"}
	{/if}
	</div>
</div>
