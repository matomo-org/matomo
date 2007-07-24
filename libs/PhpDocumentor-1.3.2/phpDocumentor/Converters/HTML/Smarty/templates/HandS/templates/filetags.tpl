{if count($tags) > 0}
<table border="0" cellspacing="0" cellpadding="0">
	{section name=tag loop=$tags}
		<tr><td><strong>{$tags[tag].keyword|capitalize}:</strong>&nbsp;&nbsp;</td><td>{$tags[tag].data}</td></tr>
	{/section}
</table>
{/if}
