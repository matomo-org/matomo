{assign var=showSitesSelection value=true}

{include file="CoreHome/templates/header.tpl"}

{if isset($menu) && $menu}{include file="CoreHome/templates/menu.tpl"}{/if}

<div class="page">
<div class="pageWrap">
	<div class="nav_sep"></div>
    <div class="top_controls">
        {include file="CoreHome/templates/period_select.tpl"}
        {include file="CoreHome/templates/header_message.tpl"}
	    {ajaxRequestErrorDiv}
    </div>
    
	{* untrusted host warning *}
	{if isset($isValidHost) && isset($invalidHostMessage) && !$isValidHost}
	<div class="ajaxSuccess">
		<a style="float:right" href="http://piwik.org/faq/troubleshooting/#faq_171" target="_blank"><img src="themes/default/images/help_grey.png" /></a>
		<strong>{'General_Warning'|translate}:&nbsp;</strong>{$invalidHostMessage}
	</div>
	{/if}

    {ajaxLoadingDiv}
    
    <div id="content" class="home">
        {if $content}{$content}{/if}
    </div>
    <div class="clear"></div>
</div>
</div>

<br/><br/>
{include file="CoreHome/templates/piwik_tag.tpl"}
</div>
</body>
</html>
