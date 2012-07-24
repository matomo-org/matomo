{if $column=='label'}
	<div class="dataTableRowActions">
		{if (!isset($properties.disable_row_evolution) || $properties.disable_row_evolution === false)
				&& !(isset($javascriptVariablesToSet.flat) && $javascriptVariablesToSet.flat == 1)}
			<a href="#" class="actionRowEvolution"><img src="themes/default/images/row_evolution.png" alt="" /></a>
		{/if}
	</div>
{/if}

{if !$row.idsubdatatable && $column=='label' && !empty($row.metadata.url)}
<a target="_blank" href='{if !in_array(substr($row.metadata.url,0,4), array('http','ftp:'))}http://{/if}{$row.metadata.url|escape:'html'}'>
	{if empty($row.metadata.logo)}
		<img class="link" width="10" height="9" src="themes/default/images/link.gif" />
	{/if}
{/if}
{if $column=='label'}
	{logoHtml metadata=$row.metadata alt=$row.columns.label}
	<span class='label{if !empty($row.metadata.is_aggregate) && $row.metadata.is_aggregate } highlighted{/if}' {if !empty($properties.tooltip_metadata_name)}title="{$row.metadata[$properties.tooltip_metadata_name]}"{/if}>{* make sure there are no whitespaces inside the span
*}{/if}{*
*}{if isset($row.columns[$column])}{$row.columns[$column]}{else}{$defaultWhenColumnValueNotDefined}{/if}{*
*}{if $column=='label'}</span>{/if}
{if !$row.idsubdatatable && $column=='label' && !empty($row.metadata.url)}
	</a>
{/if}
