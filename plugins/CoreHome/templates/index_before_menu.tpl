{include file="CoreHome/templates/warning_invalid_host.tpl"}

{if !isset($showTopMenu) || $showTopMenu}
    {include file="CoreHome/templates/top_bar.tpl"}
{/if}
{include file="CoreHome/templates/top_screen.tpl"}

<div class="ui-confirm" id="alert">
    <h2></h2>
    <input role="yes" type="button" value="{'General_Ok'|translate}"/>
</div>

