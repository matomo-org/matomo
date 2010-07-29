<div id="topBars">

<div id="topLeftBar">
{foreach from=$topMenu key=label item=menu name=topMenu}
    
        {if isset($menu._html)}
            {$menu._html}
        {elseif $menu._url.module == $currentModule}
            <span class="topBarElem"><b>{$label|translate}</b></span> | 
        {else}
            <span class="topBarElem"><a id="topmenu-{$menu._url.module|strtolower}" href="index.php{$menu._url|@urlRewriteWithParameters}">{$label|translate}</a></span> | 
        {/if}
    
{/foreach}
</div>

<div id="topRightBar">
<span class="topBarElem">{'General_HelloUser'|translate:"<strong>$userLogin</strong>"}</span>
{if $userLogin != 'anonymous'}| <span class="topBarElem"><a href='index.php?module=CoreAdminHome'>{'General_Settings'|translate}</a></span>{/if}
| <span class="topBarElem">{if $userLogin == 'anonymous'}<a href='index.php?module={$loginModule}'>{'Login_LogIn'|translate}</a>{else}<a href='index.php?module={$loginModule}&amp;action=logout'>{'Login_Logout'|translate}</a>{/if}</span>
</div>


</div>

{if $showSitesSelection}{include file=CoreHome/templates/sites_selection.tpl}{/if}