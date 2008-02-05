<div id="{$id}" class="parentDiv">
	{if isset($arrayDataTable.result) and $arrayDataTable.result == 'error'}
		{$arrayDataTable.message} 
	{else}
		{if count($arrayDataTable) == 0}
		<div id="emptyDatatable">No data for this table.</div>
		{else}
			<a name="{$id}"></a>
			<table class="dataTable"> 
			<thead>
			<tr>
			{foreach from=$dataTableColumns item=column}
				<th class="sortable" id="{$column.id}"><div id="thDIV">{$column.name}</div></th>
			{/foreach}
			</tr>
			</thead>
			
			<tbody>
			{foreach from=$arrayDataTable item=row}
			<tr {if $row.idsubdatatable}class="subDataTable" id="{$row.idsubdatatable}"{/if}>
				{foreach from=$dataTableColumns key=idColumn item=column}
				<td>
					{if $idColumn==0 && isset($row.details.url)}<span id="urlLink">{$row.details.url}</span>{/if}
					{if $idColumn==0 && isset($row.details.logo)}<img {if isset($row.details.logoWidth)}width="{$row.details.logoWidth}"{/if} {if isset($row.details.logoHeight)}height="{$row.details.logoHeight}"{/if} src="{$row.details.logo}" />{/if}
					{$row.columns[$column.name]}
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
