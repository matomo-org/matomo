<div id="{$id}" class="parentDiv">
{if isset($dataTable.result) and $dataTable.result == 'error'}
	{$dataTable.message} 
{else}
	<table border=1> 
	<tr>
		<td>Label</td>
		<td>Visits</td>
	</tr>
	{foreach from=$dataTable item=row}
	<tr>
		<td>{$row.columns.label}</td>
		<td>{$row.columns.nb_visits}</td>
	</tr>
	{/foreach}
	</table>
	
	<a href="#" id="dataTablePrevious">&lt;</a>  <a href="#" id="dataTableNext">&gt;</a>
	<br><a href="#" id="dataTableExcludeLowPopulation">Exclude low population</a>
	
	<script>
	// to throw the ajax query we need
	//- the API module+method
	//- the offset / limit
	//- the exclude filter
	//- the sort filter
	//- the pattern filter
	
	var requestVariables = new Object;
	
	{foreach from=$javascriptVariablesToSet key=name item=value}
	requestVariables.{$name} 		= "{$value}";
	{/foreach}
	</script>
{/if}
</div>