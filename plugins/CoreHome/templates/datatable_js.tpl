{if !isset($dataTableClassName)}{assign var=dataTableClassName value=dataTable}{/if}
<script type="text/javascript" defer="defer">
$(document).ready(function(){literal}{{/literal} 
	var id = '{$properties.uniqueId}',
		table = new {$dataTableClassName}();
	dataTables[id] = table;
	table.param = {literal}{{/literal} 
	{foreach from=$javascriptVariablesToSet key=name item=value name=loop}
		'{$name|escape:'javascript'}': {if is_array($value)}'{','|implode:$value}'{else}'{$value}'{/if} {if !$smarty.foreach.loop.last},{/if}
	{/foreach}
	{literal}};{/literal}
	table.init(id);
{literal}}{/literal});
</script>
