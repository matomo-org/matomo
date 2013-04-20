{assign var="tooltipIndex" value=$column|cat:"_tooltip"}
{if isset($row.metadata[$tooltipIndex])}<span class="cell-tooltip" data-tooltip="{$row.metadata[$tooltipIndex]|escape:'html'}">{/if}
{if !$row.idsubdatatable && $column=='label' && !empty($row.metadata.url)}
<a target="_blank" href='{if !in_array(substr($row.metadata.url,0,4), array('http','ftp:'))}http://{/if}{$row.metadata.url|escape:'html'}'>
    {if empty($row.metadata.logo)}
        <img class="link" width="10" height="9" src="themes/default/images/link.gif"/>
    {/if}
    {/if}
    {if $column=='label'}
    {logoHtml metadata=$row.metadata alt=$row.columns.label}
    {if !empty($row.metadata.html_label_prefix)}<span class='label-prefix'>{$row.metadata.html_label_prefix}</span>{/if}
    <span class='label{if !empty($row.metadata.is_aggregate) && $row.metadata.is_aggregate } highlighted{/if}'
          {if !empty($properties.tooltip_metadata_name)}title="{$row.metadata[$properties.tooltip_metadata_name]|escape:'html'}"{/if}>{* make sure there are no whitespaces inside the span
*}{if !empty($row.metadata.html_label_suffix)}<span class='label-suffix'>{$row.metadata.html_label_suffix}</span>{/if}
        {/if}{*
*}{if isset($row.columns[$column])}{$row.columns[$column]}{else}{$defaultWhenColumnValueNotDefined}{/if}{*
*}{if $column=='label'}</span>{/if}
    {if !$row.idsubdatatable && $column=='label' && !empty($row.metadata.url)}
</a>
{/if}
{if isset($row.metadata[$tooltipIndex])}</span>{/if}