<div class="dataTable" data-table-type="dataTable" data-report="{$properties.uniqueId}" data-params="{$javascriptVariablesToSet|@json_encode|escape:'html'}">
    <div class="reportDocumentation">
        {if !empty($reportDocumentation)}<p>{$reportDocumentation}</p>{/if}
        {if isset($properties.metadata.archived_date)}<span class='helpDate'>{$properties.metadata.archived_date}</span>{/if}
    </div>
    <div class="{if isset($javascriptVariablesToSet.idSubtable)&& $javascriptVariablesToSet.idSubtable!=0}sub{/if}{if $javascriptVariablesToSet.viewDataTable=='tableAllColumns'}dataTableAllColumnsWrapper{elseif $javascriptVariablesToSet.viewDataTable=='tableGoals'}dataTableAllColumnsWrapper{else}dataTableWrapper{/if}">
        {if isset($arrayDataTable.result) and $arrayDataTable.result == 'error'}
            {$arrayDataTable.message}
        {else}
            {if count($arrayDataTable) == 0}
                {if isset($showReportDataWasPurgedMessage) && $showReportDataWasPurgedMessage}
                    <div class="pk-emptyDataTable">{'CoreHome_DataForThisReportHasBeenPurged'|translate:$deleteReportsOlderThan}</div>
                {else}
                    <div class="pk-emptyDataTable">{'CoreHome_ThereIsNoDataForThisReport'|translate}</div>
                {/if}
            {else}
                <a name="{$properties.uniqueId}"></a>
                <table cellspacing="0" class="dataTable">
                    
                    {include file="CoreHome/templates/datatable_head.tpl"}
                    
                    <tbody>
                    {foreach from=$arrayDataTable item=row}
                        <tr {if $row.idsubdatatable && $javascriptVariablesToSet.controllerActionCalledWhenRequestSubTable != null}class="subDataTable"
                            id="{$row.idsubdatatable}"{/if}{if isset($row.issummaryrow) && $row.issummaryrow && $properties.highlight_summary_row} class="highlight"{/if}>
                            {foreach from=$dataTableColumns item=column}
                                <td>
                                    {include file="CoreHome/templates/datatable_cell.tpl"}
                                </td>
                            {/foreach}
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            {/if}

            {if $properties.show_footer}
                {include file="CoreHome/templates/datatable_footer.tpl"}
            {/if}
            {include file="CoreHome/templates/datatable_js.tpl"}
        {/if}
    </div>
</div>
