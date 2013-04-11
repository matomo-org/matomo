<div class="dataTable" data-table-type="actionDataTable" data-report="{$properties.uniqueId}" data-params="{$javascriptVariablesToSet|@json_encode|escape:'html'}">
    <div class="dataTableActionsWrapper">
        {if isset($arrayDataTable.result) and $arrayDataTable.result == 'error'}
            {$arrayDataTable.message}
        {else}
            {if count($arrayDataTable) == 0}
                <div class="pk-emptyDataTable">{'CoreHome_ThereIsNoDataForThisReport'|translate}</div>
            {else}
                <table cellspacing="0" class="dataTable dataTableActions">
                    
                    {include file="CoreHome/templates/datatable_head.tpl"}
                    
                    <tbody>
                    {foreach from=$arrayDataTable item=row}
                        <tr {if $row.idsubdatatable}class="level{$row.level} rowToProcess subActionsDataTable" id="{$row.idsubdatatable}"
                            {else}class="actionsDataTable rowToProcess level{$row.level}"{/if}>
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
