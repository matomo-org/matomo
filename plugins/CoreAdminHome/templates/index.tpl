{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{include file="CoreAdminHome/templates/menu.tpl"}

<div style="clear:both;"></div>

<div id="content">
{if $content}{$content}{/if}
</div>

<div id="footer" style="border-top:1px solid gray; margin-top:20px;padding-top:10px;">
<a href="index.php?module=CoreHome">{'General_BackToHomepage'|translate}</a>
</div>

{include file="CoreAdminHome/templates/footer.tpl"}
