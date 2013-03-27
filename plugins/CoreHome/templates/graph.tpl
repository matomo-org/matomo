<div class="dataTable" data-report="{$properties.uniqueId}" data-params="{$javascriptVariablesToSet|@json_encode|escape:'html'}">

    <div class="reportDocumentation">
        {if !empty($reportDocumentation)}<p>{$reportDocumentation}</p>{/if}
        {if isset($properties.metadata.archived_date)}<p>{$properties.metadata.archived_date}</p>{/if}
    </div>

    <div class="{if $graphType=='evolution'}dataTableGraphEvolutionWrapper{else}dataTableGraphWrapper{/if}">

        {if $isDataAvailable}
            <div class="jqplot-{$graphType}" style="padding-left: 6px;">
                <div class="piwik-graph"
                     style="position: relative; width: {$width}{if substr($width, -1) != '%'}px{/if}; height: {$height}{if substr($height, -1) != '%'}px{/if};"
                     data-data="{$data|escape:'html'}"
                     data-graph-type="{$graphType|escape:'html'}"
                        {if isset($properties.externalSeriesToggle) && $properties.externalSeriesToggle}
                    data-external-series-toggle="{$properties.externalSeriesToggle|escape:'html'}"
                    data-external-series-show-all="{if $properties.externalSeriesToggleShowAll}1{else}0{/if}"
                        {/if}>
                </div>
            </div>
        {else}
            <div>
                <div class="pk-emptyGraph">
                    {if $showReportDataWasPurgedMessage}
                        {'General_DataForThisGraphHasBeenPurged'|translate:$deleteReportsOlderThan}
                    {else}
                        {'General_NoDataForGraph_js'|translate}
                    {/if}
                </div>
            </div>
        {/if}

        {if $properties.show_footer}
            {include file="CoreHome/templates/datatable_footer.tpl"}
            {include file="CoreHome/templates/datatable_js.tpl"}
        {/if}

    </div>
</div>
