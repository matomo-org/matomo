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
    
    <p><b>{'API_MoreInformation'|translate:"<a target='_blank' href='?module=Proxy&action=redirect&url=http://piwik.org/docs/analytics-api'>":"</a>":"<a target='_blank' href='?module=Proxy&action=redirect&url=http://piwik.org/docs/analytics-api/reference'>":"</a>"}</b></p>
    
    <h2>{'API_UserAuthentication'|translate}</h2>
    <p>
    {'API_UsingTokenAuth'|translate:'<b>':'</b>':""}<br />
    <span id='token_auth'>&amp;token_auth=<b>{$token_auth}</b></span><br />
    {'API_KeepTokenSecret'|translate:'<b>':'</b>'}
    <!-- {'API_LoadedAPIs'|translate:$countLoadedAPI} -->
    {$list_api_methods_with_links}
    <br />
</div>

{include file="CoreHome/templates/piwik_tag.tpl"}
