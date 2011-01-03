{if !$row.idsubdatatable && $column=='label' && !empty($row.metadata.url)}
<a target="_blank" href='{if !in_array(substr($row.metadata.url,0,4), array('http','ftp:'))}http://{/if}{$row.metadata.url|escape:'html'}'>
	{if empty($row.metadata.logo)}
		<img class="link" width="10" height="9" src="themes/default/images/link.gif" />
	{/if}
{/if}
{if $column=='label'}
	{logoHtml metadata=$row.metadata alt=$row.columns.label}
	<span class='label'>	
{/if}
{if isset($row.columns[$column])}{$row.columns[$column]}{else}{$defaultWhenColumnValueNotDefined}{/if}
{if $column=='label'}
</span>
{/if}
{if !$row.idsubdatatable && $column=='label' && !empty($row.metadata.url)}
	</a>
{/if}