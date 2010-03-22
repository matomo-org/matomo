{assign var=showSitesSelection value=true}

{include file="CoreHome/templates/header.tpl"}

{if isset($menu) && $menu}{include file="CoreHome/templates/menu.tpl"}{/if}
<div style="clear:both;"></div>
{ajaxLoadingDiv}
{ajaxRequestErrorDiv}

<div id="content">
{if $content}{$content}{/if}
</div>

{include file="CoreHome/templates/piwik_tag.tpl"}
</body>
</html>
