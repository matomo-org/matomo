<div id="topBars">

<div id="topLeftBar">
{foreach from=$topMenu key=label item=menu name=topMenu}
    <span class="topBarElem">
        {if isset($menu._html)}
            {$menu._html}
        {elseif $menu._url.module == $currentModule}
            <b>{$label|translate}</b>
        {else}
            <a id="topmenu-{$menu._url.module|strtolower}" href="index.php{$menu._url|@urlRewriteWithParameters}">{$label|translate}</a>
        {/if}
    </span>
{/foreach}
</div>

<div id="topRightBar">
<nobr>
<small>
{'General_HelloUser'|translate:"<strong>$userLogin</strong>"}
{if $userLogin != 'anonymous'}| <a href='index.php?module=CoreAdminHome'>{'General_Settings'|translate}</a>{/if} 
 {if $showSitesSelection && $showWebsiteSelectorInUserInterface}| {include file=CoreHome/templates/sites_selection.tpl}{/if}
| {if $userLogin == 'anonymous'}<a href='index.php?module={$loginModule}'>{'Login_LogIn'|translate}</a>{else}<a href='index.php?module={$loginModule}&amp;action=logout'>{'Login_Logout'|translate}</a>{/if}
</small>

</nobr>
</div>

<br class="clearAll" />

</div>
