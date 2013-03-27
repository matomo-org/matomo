<div id="topBars">

    {include file="CoreHome/templates/top_bar_top_menu.tpl"}
    {include file="CoreHome/templates/top_bar_hello_menu.tpl"}

</div>

{if $showSitesSelection}
    <div class="top_bar_sites_selector">
        <label>{'General_Website'|translate}</label>
        {include file="CoreHome/templates/sites_selection.tpl"}
    </div>
{/if}
