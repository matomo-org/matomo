
<script type="text/javascript" defer="defer">
$(document).ready(function(){literal}{{/literal} 
	dataTables['{$id}'] = new dataTable();
	dataTables['{$id}'].param = {literal}{{/literal} 
	{foreach from=$javascriptVariablesToSet key=name item=value name=loop}
		{$name}: '{$value}'{if !$smarty.foreach.loop.last},{/if}
	{/foreach}
	{literal}};{/literal}
	dataTables['{$id}'].init('{$id}');
{literal}}{/literal});
</script>
