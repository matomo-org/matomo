{include file="CoreHome/templates/header.tpl"}
<a id="Overlay_Title" href="http://piwik.org/docs/page-overlay/" target="_blank">
    {'Overlay_Overlay'|translate|escape:'html'}
    <img src="themes/default/images/help.png" alt="Documentation" />
</a>

<div id="Overlay_DateRangeSelection">
    <select id="Overlay_DateRangeSelect" name="Overlay_DateRangeSelect">
        <option value="day;today">{'General_Today'|translate|escape:'html'}</option>
        <option value="day;yesterday">{'General_Yesterday'|translate|escape:'html'}</option>
        <option value="week;today">{'General_CurrentWeek'|translate|escape:'html'}</option>
        <option value="month;today">{'General_CurrentMonth'|translate|escape:'html'}</option>
        <option value="year;today">{'General_CurrentYear'|translate|escape:'html'}</option>
    </select>
</div>

<div id="Overlay_Error_NotLoading">
    <p>
        <span>{'Overlay_ErrorNotLoading'|translate|escape:'html'}</span>
    </p>

    <p>
        {if $ssl}
            {'Overlay_ErrorNotLoadingDetailsSSL'|translate|escape:'html'}
        {else}
            {'Overlay_ErrorNotLoadingDetails'|translate|escape:'html'}
        {/if}
    </p>

    <p>
        <a href="http://piwik.org/docs/page-overlay/#toc-page-overlay-troubleshooting" target="_blank">
            {'Overlay_ErrorNotLoadingLink'|translate|escape:'html'}
        </a>
    </p>
</div>

<div id="Overlay_Location">&nbsp;</div>

<div id="Overlay_Loading">{'General_Loading_js'|translate|escape:'html'}</div>

<div id="Overlay_Sidebar"></div>

<a id="Overlay_RowEvolution">{'General_RowEvolutionRowActionTooltipTitle_js'|translate|escape:'html'}</a>
<a id="Overlay_Transitions">{'General_TransitionsRowActionTooltipTitle_js'|translate|escape:'html'}</a>

<!-- TODO: rethink the way the sidebar works -->
<!-- <a id="Overlay_FullScreen" href="#">
	{'Overlay_OpenFullScreen'|translate|escape:'html'}
</a> -->


<div id="Overlay_Main">
    <iframe id="Overlay_Iframe" src="" frameborder="0"></iframe>
</div>


<script type="text/javascript">
    var iframeSrc = 'index.php?module=Overlay&action=startOverlaySession&idsite={$idSite}&period={$period}&date={$date}';
    Piwik_Overlay.init(iframeSrc, '{$idSite}', '{$period}', '{$date}');

    Piwik_Overlay_Translations = {literal}{{/literal}
        domain: "{'Overlay_Domain'|translate|escape:'html'}"
        {literal}}{/literal};
</script>


<!-- close tag opened in header.tpl -->
</div>
</body>
</html>