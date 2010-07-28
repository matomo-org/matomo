{assign var=showSitesSelection value=true}
{assign var=showPeriodSelection value=false}
{assign var=showMenu value=false}
{include file="CoreHome/templates/header.tpl"}

<div class="page_api">
    <div class="top_controls_inner">
        {include file="CoreHome/templates/period_select.tpl"}
    </div>
    
    <h2>{'API_QuickDocumentationTitle'|translate}</h2>
    <p>{'API_PluginDescription'|translate}</p>
    
    {if $isSuperUser}
        <p>{'API_GenerateVisits'|translate:'VisitorGenerator':'VisitorGenerator'}</p>
    {/if}
    
    <p><b>{'API_MoreInformation'|translate:"<a target='_blank' href='misc/redirectToUrl.php?url=http://dev.piwik.org/trac/wiki/API'>":"</a>":"<a target='_blank' href='misc/redirectToUrl.php?url=http://dev.piwik.org/trac/wiki/API/Reference'>":"</a>"}</b></p>
    
    <h2>{'API_UserAuthentication'|translate}</h2>
    <p>
    {'API_UsingTokenAuth'|translate:'<b>':'</b>':"<u><code>&amp;token_auth=$token_auth</code></u>"}<br />
    <span id='token_auth'>token_auth = <b>{$token_auth}</b></span><br />
    {'API_KeepTokenSecret'|translate:'<b>':'</b>'}
    <p><i>{'API_LoadedAPIs'|translate:$countLoadedAPI}</i></p>
    {$list_api_methods_with_links}
    <br />
</div>

{include file="CoreHome/templates/piwik_tag.tpl"}