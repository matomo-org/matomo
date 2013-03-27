{if !isset($dataTableClassName)}{assign var=dataTableClassName value=dataTable}{/if}
<script type="text/javascript" defer="defer">
    $(document).ready(function () {literal}{{/literal}
        piwik.DataTableManager.initNewDataTables({$dataTableClassName});
        {literal}
    }{/literal});
</script>
