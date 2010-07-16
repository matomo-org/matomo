{assign var=showSitesSelection value=true}

{include file="CoreHome/templates/header.tpl"}

{if isset($menu) && $menu}{include file="CoreHome/templates/menu.tpl"}{/if}

<div class="page">
<div class="pageWrap">
	<div class="nav_sep"></div>
    <div class="top_controls">
        {include file="CoreHome/templates/period_select.tpl"}
        {include file="CoreHome/templates/header_message.tpl"}
    </div>
    
    {ajaxLoadingDiv}
    {ajaxRequestErrorDiv}
    
    <div id="content" class="home">
        {if $content}{$content}{/if}
    </div>
    <div class="clear"></div>
</div>
</div>


{include file="CoreHome/templates/piwik_tag.tpl"}
</div>
</body>
</html>
