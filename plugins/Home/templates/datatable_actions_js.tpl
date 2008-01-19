
<script type="text/javascript" defer="defer">
actionDataTables['{$id}'] = new actionDataTable();
actionDataTables['{$id}'].param = {literal}{{/literal} 
{foreach from=$javascriptVariablesToSet key=name item=value}
	{$name}: '{$value}',
{/foreach}
{literal}};{/literal}
</script>
