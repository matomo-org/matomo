<script type="text/javascript" defer="defer">
/* TODO 0- REFACTOR */
$(document).ready(function(){literal}{{/literal} 
	actionDataTables['{$properties.uniqueId}'] = new actionDataTable();
	actionDataTables['{$properties.uniqueId}'].param = {literal}{{/literal} 
	{foreach from=$javascriptVariablesToSet key=name item=value name=loop}
		{$name}: '{$value}'{if !$smarty.foreach.loop.last},{/if}
	{/foreach}
	{literal}};{/literal}
	actionDataTables['{$properties.uniqueId}'].init('{$properties.uniqueId}');
{literal}}{/literal});
</script>
