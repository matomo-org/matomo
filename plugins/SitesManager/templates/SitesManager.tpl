{include file="CoreAdminHome/templates/header.tpl"}
{loadJavascriptTranslations plugins='SitesManager'}

{capture assign=excludedIpHelpPlain}
    {'SitesManager_HelpExcludedIps'|translate:"1.2.3.*":"1.2.*.*"}
<br/><br/>
    {'SitesManager_YourCurrentIpAddressIs'|translate:"<i>$currentIpAddress</i>"}
{/capture}
{assign var=excludedIpHelp value=$excludedIpHelpPlain|inlineHelp}

{capture assign=defaultTimezoneHelpPlain}
    {if $timezoneSupported}
        {'SitesManager_ChooseCityInSameTimezoneAsYou'|translate}
        {else}
        {'SitesManager_AdvancedTimezoneSupportNotFound'|translate}
    {/if}
<br/><br/>
    {'SitesManager_UTCTimeIs'|translate:$utcTime}
{/capture}

{capture assign=timezoneHelpPlain}
    {$defaultTimezoneHelpPlain}
<br/><br/>{'SitesManager_ChangingYourTimezoneWillOnlyAffectDataForward'|translate}
{/capture}

{capture assign=currencyHelpPlain}
    {'SitesManager_CurrencySymbolWillBeUsedForGoals'|translate|inlineHelp}
{/capture}

{capture assign=ecommerceHelpPlain}
    {'SitesManager_EcommerceHelp'|translate}
<br/>
    {'SitesManager_PiwikOffersEcommerceAnalytics'|translate:"<a href='http://piwik.org/docs/ecommerce-analytics/' target='_blank'>":"</a>"}
{/capture}

{capture assign=excludedQueryParametersHelp}
    {'SitesManager_ListOfQueryParametersToExclude'|translate}
<br/><br/>
    {'SitesManager_PiwikWillAutomaticallyExcludeCommonSessionParameters'|translate:"phpsessid, sessionid, ..."}
{/capture}
{assign var=excludedQueryParametersHelp value=$excludedQueryParametersHelp|inlineHelp}

{capture assign=excludedUserAgentsHelp}
    {'SitesManager_GlobalExcludedUserAgentHelp1'|translate}
<br/><br/>
    {'SitesManager_GlobalListExcludedUserAgents_Desc'|translate} {'SitesManager_GlobalExcludedUserAgentHelp2'|translate}
{/capture}
{assign var=excludedUserAgentsHelp value=$excludedUserAgentsHelp|inlineHelp}

{capture assign=keepURLFragmentSelectHTML}
<h4 style="display:inline-block;">{'SitesManager_KeepURLFragmentsLong'|translate}</h4>

<select id="keepURLFragmentSelect">
    <option value="0"> {if $globalKeepURLFragments}{'General_Yes'|translate}{else}{'General_No'|translate}{/if}
        ({'General_Default'|translate})
    </option>
    <option value="1">{'General_Yes'|translate}</option>
    <option value="2">{'General_No'|translate}</option>
</select>
{/capture}

<script type="text/javascript">
var excludedIpHelp = '{$excludedIpHelp|escape:javascript}';
var aliasUrlsHelp = '{'SitesManager_AliasUrlHelp'|translate|inlineHelp|escape:javascript}';
var excludedQueryParametersHelp = '{$excludedQueryParametersHelp|escape:javascript}';
var excludedUserAgentsHelp = '{$excludedUserAgentsHelp|escape:javascript}';
var timezoneHelp = '{$timezoneHelpPlain|inlineHelp|escape:javascript}';
var currencyHelp = '{$currencyHelpPlain|escape:javascript}';
var ecommerceHelp = '{$ecommerceHelpPlain|inlineHelp|escape:javascript}';
var ecommerceEnabled = '{'SitesManager_EnableEcommerce'|translate|escape:javascript}';
var ecommerceDisabled = '{'SitesManager_NotAnEcommerceSite'|translate|escape:javascript}';
{assign var=defaultTimezoneHelp value=$defaultTimezoneHelpPlain|inlineHelp}
{assign var=searchKeywordHelp value='SitesManager_SearchKeywordParametersDesc'|translate|inlineHelp}
{capture assign=searchCategoryHelpText}{'Goals_Optional'|translate} {'SitesManager_SearchCategoryParametersDesc'|translate}{/capture}
{assign var=searchCategoryHelp value=$searchCategoryHelpText|inlineHelp}
var sitesearchEnabled = '{'SitesManager_EnableSiteSearch'|translate|escape:javascript}';
var sitesearchDisabled = '{'SitesManager_DisableSiteSearch'|translate|escape:javascript}';
var searchKeywordHelp = '{$searchKeywordHelp|escape:javascript}';
var searchCategoryHelp = '{$searchCategoryHelp|escape:javascript}';
var sitesearchDesc = '{'SitesManager_TrackingSiteSearch'|translate|escape:javascript}';
var keepURLFragmentSelectHTML = '{$keepURLFragmentSelectHTML|escape:javascript}';

var sitesManager = new SitesManager({$timezones}, {$currencies}, '{$defaultTimezone}', '{$defaultCurrency}');
{assign var=searchKeywordLabel value='SitesManager_SearchKeywordLabel'|translate}
{assign var=searchCategoryLabel value='SitesManager_SearchCategoryLabel'|translate}
var searchKeywordLabel = '{$searchKeywordLabel|escape:javascript}';
var searchCategoryLabel = '{$searchCategoryLabel|escape:javascript}';
{assign var=sitesearchIntro value='SitesManager_SiteSearchUse'|translate}
var sitesearchIntro = '{$sitesearchIntro|inlineHelp|escape:javascript}';
var sitesearchUseDefault = '{if $isSuperUser}{'SitesManager_SearchUseDefault'|translate:'<a href="#globalSiteSearch">':'</a>'|escape:'javascript'}{else}{'SitesManager_SearchUseDefault'|translate:'':''|escape:'javascript'}{/if}';
var strDefault = '{'General_Default'|translate:escape:'javascript'}';
{literal}
$(document).ready(function () {
    sitesManager.init();
});
</script>

<style type="text/css">
    .entityTable tr td {
        vertical-align: top;
        padding-top: 7px;
    }

    .addRowSite:hover, .editableSite:hover, .addsite:hover, .cancel:hover, .deleteSite:hover, .editSite:hover, .updateSite:hover {
        cursor: pointer;
    }

    .addRowSite a {
        text-decoration: none;
    }

    .addRowSite {
        padding: 1em;
        font-weight: bold;
    }

    #editSites {
        vertical-align: top;
    }

    option, select {
        font-size: 11px;
    }

    textarea {
        font-size: 9pt;
    }

    .admin thead th {
        vertical-align: middle;
    }

    .ecommerceInactive, .sitesearchInactive {
        color: #666666;
    }

    #searchSiteParameters {
        display: none;
    }

    #editSites h4 {
        font-size: .8em;
        margin: 1em 0 1em 0;
        font-weight: bold;
    }
</style>
{/literal}

<h2>{'SitesManager_WebsitesManagement'|translate}</h2>
<p>{'SitesManager_MainDescription'|translate}
{'SitesManager_YouCurrentlyHaveAccessToNWebsites'|translate:"<b>$adminSitesCount</b>"}
{if $isSuperUser}
    <br/>
    {'SitesManager_SuperUserCan'|translate:"<a href='#globalSettings'>":"</a>"}
{/if}
</p>
{ajaxErrorDiv}
{ajaxLoadingDiv}

{capture assign=createNewWebsite}
<div class="addRowSite"><img src='plugins/UsersManager/images/add.png' alt=""/> {'SitesManager_AddSite'|translate}</div>
{/capture}

{if $adminSites|@count == 0}
    {'SitesManager_NoWebsites'|translate}
    {else}
<div class="ui-confirm" id="confirm">
    <h2></h2>
    <input role="yes" type="button" value="{'General_Yes'|translate}"/>
    <input role="no" type="button" value="{'General_No'|translate}"/>
</div>
<div class="entityContainer">
    {if $isSuperUser}
        {$createNewWebsite}
    {/if}
    <table class="entityTable dataTable" id="editSites">
        <thead>
        <tr>
            <th>{'General_Id'|translate}</th>
            <th>{'General_Name'|translate}</th>
            <th>{'SitesManager_Urls'|translate}</th>
            <th>{'SitesManager_ExcludedIps'|translate}</th>
            <th>{'SitesManager_ExcludedParameters'|translate|replace:" ":"<br />"}</th>
            <th id='exclude-user-agent-header'
                {if !$allowSiteSpecificUserAgentExclude}style="display:none"{/if}>{'SitesManager_ExcludedUserAgents'|translate}</th>
            <th>{'Actions_SubmenuSitesearch'|translate}</th>
            <th>{'SitesManager_Timezone'|translate}</th>
            <th>{'SitesManager_Currency'|translate}</th>
            <th>{'Goals_Ecommerce'|translate}</th>
            <th></th>
            <th></th>
            <th> {'SitesManager_JsTrackingTag'|translate} </th>
        </tr>
        </thead>
        <tbody>
            {foreach from=$adminSites key=i item=site}
            <tr id="row{$site.idsite}" data-keep-url-fragments="{$site.keep_url_fragment}">
                <td id="idSite">{$site.idsite}</td>
                <td id="siteName" class="editableSite">{$site.name}</td>
                <td id="urls" class="editableSite">{foreach from=$site.alias_urls item=url}{$url|replace:"http://":""}
                    <br/>{/foreach}</td>
                <td id="excludedIps" class="editableSite">{foreach from=$site.excluded_ips item=ip}{$ip}<br/>{/foreach}
                </td>
                <td id="excludedQueryParameters"
                    class="editableSite">{foreach from=$site.excluded_parameters item=parameter}{$parameter}
                    <br/>{/foreach}
                </td>
                <td id="excludedUserAgents" class="editableSite"
                    {if !$allowSiteSpecificUserAgentExclude}style="display:none"{/if}>{foreach from=$site.excluded_user_agents item=ua}{$ua}
                    <br/>{/foreach}
                </td>
                <td id="sitesearch" class="editableSite">{if $site.sitesearch}<span
                        class='sitesearchActive'>{'General_Yes'|translate}</span>{else}<span
                        class='sitesearchInactive'>-</span>{/if}<span class='sskp'
                                                                      sitesearch_keyword_parameters="{$site.sitesearch_keyword_parameters|escape:'html'}"
                                                                      sitesearch_category_parameters="{$site.sitesearch_category_parameters|escape:'html'}"
                                                                      id="sitesearch_parameters"></span></td>
                <td id="timezone" class="editableSite">{$site.timezone}</td>
                <td id="currency" class="editableSite">{$site.currency}</td>
                <td id="ecommerce" class="editableSite">{if $site.ecommerce}<span
                        class='ecommerceActive'>{'General_Yes'|translate}</span>{else}
                    <span class='ecommerceInactive'>-</span>
                {/if}</td>
                <td><span id="row{$site.idsite}" class='editSite link_but'><img src='themes/default/images/ico_edit.png'
                                                                                title="{'General_Edit'|translate}"
                                                                                border="0"/> {'General_Edit'|translate}</span>
                </td>
                <td><span id="row{$site.idsite}" class="deleteSite link_but"><img
                        src='themes/default/images/ico_delete.png'
                        title="{'General_Delete'|translate}"
                        border="0"/> {'General_Delete'|translate}</span></td>
                <td>
                    <a href='{url module=CoreAdminHome action=trackingCodeGenerator idSite=$site.idsite updated=false}'>{'SitesManager_ShowTrackingTag'|translate}</a>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
    {if $isSuperUser}
        {$createNewWebsite}
    {/if}
</div>
{/if}


{* Admin users use these values for Site Search column, when editing websites *}
{if !$isSuperUser}
<input type="hidden" size="15" id="globalSearchKeywordParameters"
       value="{$globalSearchKeywordParameters|escape:'html'}"/>
<input type="hidden" size="15" id="globalSearchCategoryParameters"
       value="{$globalSearchCategoryParameters|escape:'html'}"/>
{/if}

{if $isSuperUser}
<br/>
<a name='globalSettings'></a>
<h2>{'SitesManager_GlobalWebsitesSettings'|translate}</h2>
<br/>
<table style='width:600px' class="adminTable">

    <tr>
        <td colspan="2">
            <b>{'SitesManager_GlobalListExcludedIps'|translate}</b>

            <p>{'SitesManager_ListOfIpsToBeExcludedOnAllWebsites'|translate} </p>
        </td>
    </tr>
    <tr>
        <td>
            <textarea cols="30" rows="3" id="globalExcludedIps">{$globalExcludedIps}
            </textarea>
        </td>
        <td>
            <label for="globalExcludedIps">{$excludedIpHelp}</label>
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <b>{'SitesManager_GlobalListExcludedQueryParameters'|translate}</b>

            <p>{'SitesManager_ListOfQueryParametersToBeExcludedOnAllWebsites'|translate} </p>
        </td>
    </tr>

    <tr>
        <td>
            <textarea cols="30" rows="3" id="globalExcludedQueryParameters">{$globalExcludedQueryParameters}
            </textarea>
        </td>
        <td><label for="globalExcludedQueryParameters">{$excludedQueryParametersHelp}</label>
        </td>
    </tr>

{* global excluded user agents *}
    <tr>
        <td colspan="2">
            <b>{'SitesManager_GlobalListExcludedUserAgents'|translate}</b>

            <p>{'SitesManager_GlobalListExcludedUserAgents_Desc'|translate}</p>
        </td>
    </tr>

    <tr>
        <td>
            <textarea cols="30" rows="3" id="globalExcludedUserAgents">{$globalExcludedUserAgents}</textarea>
        </td>
        <td><label for="globalExcludedUserAgents">{$excludedUserAgentsHelp}</label>
        </td>
    </tr>

    <tr>
        <td>
            <input type="checkbox" id="enableSiteUserAgentExclude" name="enableSiteUserAgentExclude"
                   {if $allowSiteSpecificUserAgentExclude}checked="checked"{/if}/><label
                for="enableSiteUserAgentExclude">{'SitesManager_EnableSiteSpecificUserAgentExclude'|translate}</label>
                <span id='enableSiteUserAgentExclude-loading' class='loadingPiwik' style='display:none'><img
                        src='./themes/default/images/loading-blue.gif'/></span>
        </td>
        <td>{'SitesManager_EnableSiteSpecificUserAgentExclude_Help'|translate:'<a href="#editSites">':'</a>'|inlineHelp}
        </td>
    </tr>

{* global keep URL fragments *}
    <tr>
        <td colspan="2">
            <strong>{'SitesManager_KeepURLFragments'|translate}</strong>

            <p>{'SitesManager_KeepURLFragmentsHelp'|translate:"<em>#</em>":"<em>example.org/index.html#first_section</em>":"<em>example.org/index.html</em>"}
            </p>
            <input type="checkbox" id="globalKeepURLFragments" name="globalKeepURLFragments"
                   {if $globalKeepURLFragments}checked="checked"{/if}/>
            <label for="globalKeepURLFragments">{'SitesManager_KeepURLFragmentsLong'|translate}</label>

            <p>{'SitesManager_KeepURLFragmentsHelp2'|translate}</p>
        </td>
    </tr>

{* global site search *}
    <tr>
        <td colspan="2">
            <a name='globalSiteSearch'></a><b>{'SitesManager_TrackingSiteSearch'|translate}</b>

            <p>{$sitesearchIntro}</p>
                <span class="form-description"
                      style='font-size:8pt'>{'SitesManager_SearchParametersNote'|translate} {'SitesManager_SearchParametersNote2'|translate}</span>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <label>{$searchKeywordLabel} &nbsp;<input type="text" size="15" id="globalSearchKeywordParameters"
                                                      value="{$globalSearchKeywordParameters|escape:'html'}"/>

                <div style='width: 200px;float:right;'>{$searchKeywordHelp}</div>
            </label>
        </td>
    </tr>

<tr>
<td colspan="2">
    {if !$isSearchCategoryTrackingEnabled}
        <input value='globalSearchCategoryParametersIsDisabled' id="globalSearchCategoryParameters" type='hidden'/>
        <span class='form-description'>Note: you could also track your Internal Search Engine Categories, but the plugin Custom Variables is required. Please enable the plugin CustomVariables (or ask your Piwik admin).</span>
        {else}
        {'Goals_Optional'|translate} {'SitesManager_SearchCategoryDesc'|translate} <br/>
    </td>
    </tr>
    <tr>
    <td colspan="2">
        <label>{$searchCategoryLabel}  &nbsp;<input type="text" size="15" id="globalSearchCategoryParameters"
                                                    value="{$globalSearchCategoryParameters|escape:'html'}"/>

            <div style='width: 200px;float:right;'>{$searchCategoryHelp}</div>
        </label>
    {/if}
</td>
</tr>

    <tr>
        <td colspan="2">
            <b>{'SitesManager_DefaultTimezoneForNewWebsites'|translate}</b>

            <p>{'SitesManager_SelectDefaultTimezone'|translate} </p>
        </td>
    </tr>
    <tr>
        <td>
            <div id='defaultTimezone'></div>
        </td>
        <td>
            {$defaultTimezoneHelp}
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <b>{'SitesManager_DefaultCurrencyForNewWebsites'|translate}</b>

            <p>{'SitesManager_SelectDefaultCurrency'|translate} </p>
        </td>
    </tr>
    <tr>
        <td>
            <div id='defaultCurrency'></div>
        </td>
        <td>
            {$currencyHelpPlain}
        </td>
    </tr>
</table>
<span style="margin-left:20px">
    <input type="submit" class="submit" id='globalSettingsSubmit' value="{'General_Save'|translate}"/>
</span>
    {ajaxErrorDiv id=ajaxErrorGlobalSettings}
    {ajaxLoadingDiv id=ajaxLoadingGlobalSettings}
{/if}
{if $showAddSite}
<script type="text/javascript">{literal}
$(document).ready(function () {
    $('.addRowSite:first').trigger('click');
});
{/literal}</script>
{/if}

<br/><br/><br/><br/>
{include file="CoreAdminHome/templates/footer.tpl"}
