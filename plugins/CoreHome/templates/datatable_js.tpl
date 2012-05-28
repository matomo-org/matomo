{if !isset($dataTableClassName)}{assign var=dataTableClassName value=dataTable}{/if}
<script type="text/javascript">
$(document).ready(function(){literal}{{/literal} 
	var id = '{$properties.uniqueId}',
		table = new {$dataTableClassName}();
	
	// make sure we don't overwrite an existing id
	{literal}
	if (dataTables[id])
	{
		if (!(dataTables[id] instanceof Array))
			dataTables[id] = [dataTables[id]];
		
		dataTables[id].push(table);
	}
	else
	{
		dataTables[id] = table;
	}
	{/literal}
	
	table.param = {literal}{{/literal} 
	{foreach from=$javascriptVariablesToSet key=name item=value name=loop}
		'{$name|escape:'javascript'}': {if is_array($value)}'{','|implode:$value}'{else}'{$value}'{/if} {if !$smarty.foreach.loop.last},{/if}
	{/foreach}
	{literal}};{/literal}
	table.init(id);
{literal}}{/literal});
</script>
