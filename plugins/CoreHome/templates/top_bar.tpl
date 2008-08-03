{assignTopBar}
<div id="topLeftBar">
{foreach from=$topBarElements item=element}
	<span class="topBarElem">{if $element.0 == $currentModule}<b>{else}<a href="{$element.2|@urlRewriteWithParameters}" {if isset($element.3)}{$element.3}{/if}>{/if}{$element.1}{if $element.0 == $currentModule}</b>{else}</a>{/if}
	</span>
{/foreach}
</div>

<div id="topRightBar">
<nobr>
<form action="{url idSite=null}" method="get">
<small>
	<strong>{$userLogin}</strong>
| <a href='?module=CoreAdminHome'>Admin</a>
{if $showSitesSelection}| {include file=CoreHome/templates/sites_selection.tpl}{/if}
| {if $userLogin == 'anonymous'}<a href='?module=Login'>{'Login_LogIn'|translate}</a>{else}<a href='?module=Login&amp;action=logout'>{'Login_Logout'|translate}</a>{/if}
</small>
</form>
</nobr>
</div>

<br clear="all" />