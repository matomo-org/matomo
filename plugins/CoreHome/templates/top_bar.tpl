<div id="topBars">

<div id="topLeftBar">
{foreach from=$topMenu key=label item=menu name=topMenu}
    
        {if isset($menu._html)}
            {$menu._html}
        {elseif $menu._url.module == $currentModule && (empty($menu._url.action) || $menu._url.action == $currentAction)}
            <span class="topBarElem" {if isset($menu._url.tooltip)}title="{$menu._url.tooltip}"{/if}><b>{$label|translate}</b></span> | 
        {else}
            <span class="topBarElem" {if isset($menu._url.tooltip)}title="{$menu._url.tooltip}"{/if}><a id="topmenu-{$menu._url.module|strtolower}" href="index.php{$menu._url|@urlRewriteWithParameters}">{$label|translate}</a></span> | 
        {/if}
    
{/foreach}
</div>

<div id="topRightBar">
{capture assign=helloAlias}{if !empty($userAlias)}{$userAlias}{else}{$userLogin}{/if}{/capture}
<span class="topBarElem">{'General_HelloUser'|translate:"<strong>$helloAlias</strong>"}</span>
{if $userLogin != 'anonymous'}| <span class="topBarElem"><a href='index.php?module=CoreAdminHome'>{'General_Settings'|translate}</a></span>{/if}
| <span class="topBarElem">{if $userLogin == 'anonymous'}<a href='index.php?module={$loginModule}'>{'Login_LogIn'|translate}</a>{else}<a href='index.php?module={$loginModule}&amp;action=logout'>{'Login_Logout'|translate}</a>{/if}</span>
</div>


</div>

{if $showSitesSelection}
<div class="top_bar_sites_selector">
    <label>{'General_Website'|translate}</label>
    {include file="CoreHome/templates/sites_selection.tpl"}
</div>
{/if}
