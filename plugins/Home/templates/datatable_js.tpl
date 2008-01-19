
<script type="text/javascript" defer="defer">
dataTables['{$id}'] = new dataTable();
dataTables['{$id}'].param = {literal}{{/literal} 
{foreach from=$javascriptVariablesToSet key=name item=value}
	{$name}: '{$value}',
{/foreach}
{literal}};{/literal}
</script>
