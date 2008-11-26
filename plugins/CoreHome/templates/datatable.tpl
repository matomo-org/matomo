<div id="{$id}">
	<div class="{if $javascriptVariablesToSet.viewDataTable=='tableAllColumns'}dataTableAllColumnsWrapper{else}dataTableWrapper{/if}">
	{if isset($arrayDataTable.result) and $arrayDataTable.result == 'error'}
		{$arrayDataTable.message} 
	{else}
		{if count($arrayDataTable) == 0}
		<div id="emptyDatatable">{'CoreHome_TableNoData'|translate}</div>
		{else}
			<a name="{$id}"></a>
			<table cellspacing="0" class="dataTable"> 
			<thead>
			<tr>
			{foreach from=$dataTableColumns item=column}
				<th class="sortable" id="{$column.name}"><div id="thDIV">{$column.displayName}</div></th>
			{/foreach}
			</tr>
			</thead>
			
			<tbody>
			{foreach from=$arrayDataTable item=row}
			<tr {if $row.idsubdatatable}class="subDataTable" id="{$row.idsubdatatable}"{/if}>
{foreach from=$dataTableColumns item=column}
<td>
{if $column.name=='label' && isset($row.metadata.url)}<span id="urlLink">{$row.metadata.url}</span>{/if}
{if $column.name=='label' && isset($row.metadata.logo)}<img {if isset($row.metadata.logoWidth)}width="{$row.metadata.logoWidth}"{/if} {if isset($row.metadata.logoHeight)}height="{$row.metadata.logoHeight}"{/if} src="{$row.metadata.logo}" />{/if}
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
		{include file="CoreHome/templates/datatable_js.tpl"}
	{/if}
	</div>
</div>
