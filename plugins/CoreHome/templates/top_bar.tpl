{assignTopBar}

<div id="topLeftBar">
{foreach from=$topBarElements item=element}
	<span class="topBarElem">{if $element.0 == $currentModule}<b>{else}<a href="{$element.2|@urlRewriteWithParameters}" {if isset($element.3)}{$element.3}{/if}>{/if}{$element.1}{if $element.0 == $currentModule}</b>{else}</a>{/if}</span>
{/foreach}
{postEvent name="template_topBar"}
</div>

<div id="topRightBar">
<nobr>
<small>
Hello, <strong>{$userLogin}</strong>!
{if isset($userHasSomeAdminAccess) && $userHasSomeAdminAccess}| <a href='?module=CoreAdminHome'>Admin</a>{/if} 
 {if $showSitesSelection}| {include file=CoreHome/templates/sites_selection.tpl}{/if}
| {if $userLogin == 'anonymous'}<a href='?module=Login'>{'Login_LogIn'|translate}</a>{else}<a href='?module=Login&amp;action=logout'>{'Login_Logout'|translate}</a>{/if}
</small>

</nobr>
</div>

<br clear="all" />