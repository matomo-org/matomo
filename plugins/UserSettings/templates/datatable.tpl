<div id="{$id}" class="parentDiv">
	{if isset($dataTable.result) and $dataTable.result == 'error'}
		{$dataTable.message} 
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
			{foreach from=$dataTableColumns item=column}
			<td> {$row.columns[$column.name]}</td>
			{/foreach}
		</tr>
		{/foreach}
		</tbody>
		</table>
		
		<div id="dataTableFeatures">
		<span id="dataTableExcludeLowPopulation"></span>
		
		<span id="dataTableSearchPattern">
			<input id="keyword" type="text" length="15">
			<input type="submit" value="Search">
		</span>
		
		<span id="dataTablePages"></span>
		<span id="dataTablePrevious">&lt; Previous</span>
		<span id="dataTableNext">Next &gt;</span>
		<span id="loadingDataTable"><img src="themes/default/images/loading-blue.gif"> Loading...</span>
		
		</div>
		
		
		
		<script type="text/javascript"  defer="defer">
			function populateVar()
			{$smarty.ldelim}
				requestVariables.{$id} = new Object;
				
				{foreach from=$javascriptVariablesToSet key=name item=value}
				requestVariables.{$id}.{$name} 		= '{$value}';
				{/foreach}
				
				//alert('loaded');
			{$smarty.rdelim}
			populateVar();
		</script>
	{/if}
</div>