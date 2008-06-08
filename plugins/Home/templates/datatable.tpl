<div id="{$id}" class="parentDiv">
	{if isset($arrayDataTable.result) and $arrayDataTable.result == 'error'}
		{$arrayDataTable.message} 
	{else}
		{if count($arrayDataTable) == 0}
		<div id="emptyDatatable">{'Home_TableNoData'|translate}</div>
		{else}
			<a name="{$id}"></a>
			<table cellspacing="0" class="dataTable"> 
			<thead>
			<tr>
			{foreach from=$dataTableColumns item=column}
				<th class="sortable" id="{$column.id}"><div id="thDIV">{$column.displayName}</div></th>
			{/foreach}
			</tr>
			</thead>
			
			<tbody>
			{foreach from=$arrayDataTable item=row}
			<tr {if $row.idsubdatatable}class="subDataTable" id="{$row.idsubdatatable}"{/if}>
				{foreach from=$dataTableColumns key=idColumn item=column}
				<td>
					{if $idColumn==0 && isset($row.metadata.url)}<span id="urlLink">{$row.metadata.url}</span>{/if}
					{if $idColumn==0 && isset($row.metadata.logo)}<img {if isset($row.metadata.logoWidth)}width="{$row.metadata.logoWidth}"{/if} {if isset($row.metadata.logoHeight)}height="{$row.metadata.logoHeight}"{/if} src="{$row.metadata.logo}" />{/if}
					{* sometimes all columns are not set in the datatable, we assume the value 0 *}
					{if isset($row.columns[$column.name])}{$row.columns[$column.name]}{else}0{/if}
				</td>
				{/foreach}
			</tr>
			{/foreach}
			</tbody>
			</table>
		{/if}
		
		{if $showFooter}
			{include file="Home/templates/datatable_footer.tpl"}
		{/if}
		{include file="Home/templates/datatable_js.tpl"}
	{/if}
</div>
