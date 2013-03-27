{loadJavascriptTranslations plugins='UserCountry'}
{include file="CoreAdminHome/templates/header.tpl"}

<h2 id="location-providers">{'UserCountry_Geolocation'|translate}</h2>

<div style="width:900px">

    <p>{'UserCountry_GeolocationPageDesc'|translate}</p>

    {if !$isThereWorkingProvider}
        <h3 style="margin-top:0">{'UserCountry_HowToSetupGeoIP'|translate}</h3>
        <p>{'UserCountry_HowToSetupGeoIPIntro'|translate}</p>
        <ul style="list-style:disc;margin-left:2em">
            <li>{'UserCountry_HowToSetupGeoIP_Step1'|translate:'<a href="http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz">':'</a>':'<a target="_blank" href="http://www.maxmind.com/?rId=piwik">':'</a>'}</li>
            <li>{'UserCountry_HowToSetupGeoIP_Step2'|translate:"'GeoLiteCity.dat'":'<strong>':'</strong>'}</li>
            <li>{'UserCountry_HowToSetupGeoIP_Step3'|translate:'<strong>':'</strong>':'<span style="color:green"><strong>':'</strong></span>'}</li>
            <li>{'UserCountry_HowToSetupGeoIP_Step4'|translate}</li>
        </ul>
        <p>&nbsp;</p>
    {/if}

    <table class="adminTable locationProviderTable">
        <tr>
            <th>{'UserCountry_LocationProvider'|translate}</th>
            <th>{'General_Description'|translate}</th>
            <th>{'General_InfoFor'|translate:$thisIP}</th>
        </tr>
        {foreach from=$locationProviders key=id item=provider}
        <tr>
            <td width="140">
                <p>
                    <input class="location-provider" name="location-provider" value="{$id}" type="radio" {if $currentProviderId eq $id}checked="checked"{/if}
                           id="provider_input_{$id}" {if $provider.status neq 1}disabled="disabled"{/if}/>
                    <label for="provider_input_{$id}">{$provider.title|translate}</label><br/>
                    <span class='loadingPiwik' style='display:none'><img src='./themes/default/images/loading-blue.gif'/></span>
                    <span class="ajaxSuccess" style='display:none'>{'General_Done'|translate}</span>
                </p>

                <p class="loc-provider-status">
                    <strong><em>
                            {if $provider.status eq 0}
                                <span class="is-not-installed">{'General_NotInstalled'|translate}</span>
                            {elseif $provider.status eq 1}
                                <span class="is-installed">{'General_Installed'|translate}</span>
                            {elseif $provider.status eq 2}
                                <span class="is-broken">{'General_Broken'|translate}</span>
                            {/if}
                        </em></strong>
                </p>
            </td>
            <td>
                <p>{$provider.description|translate}</p>
                {if $provider.status neq 1 && isset($provider.install_docs)}
                    <p>{$provider.install_docs}</p>
                {/if}
            </td>
            <td width="164">
                {if $provider.status eq 1}
                    {capture assign=currentLocation}
                        {if $thisIP neq '127.0.0.1'}
                            {'UserCountry_CurrentLocationIntro'|translate}:
                            <div style="text-align:left;">
                                <br/>
                                <span class='loadingPiwik' style='display:none;position:absolute'><img
                                            src='./themes/default/images/loading-blue.gif'/> {'General_Loading_js'|translate}</span>
                                <span class='location'><strong><em>{$provider.location}</em></strong></span>
                            </div>
                            <div style="text-align:right;">
                                <a href="#" class="refresh-loc" data-impl-id="{$id}"><em>{'Dashboard_Refresh_js'|translate}</em></a>
                            </div>
                        {else}
                            {'UserCountry_CannotLocalizeLocalIP'|translate:$thisIP}
                        {/if}
                    {/capture}
                    {$currentLocation|inlineHelp}
                {/if}
                {if isset($provider.statusMessage) && $provider.statusMessage}
                    {capture assign=brokenReason}
                        {if $provider.status eq 2}<strong><em>{'General_Error'|translate}:</strong></em> {/if}{$provider.statusMessage}
                    {/capture}
                    {$brokenReason|inlineHelp}
                {/if}
                {if isset($provider.extra_message) && $provider.extra_message}
                    {capture assign=extraMessage}
                        {$provider.extra_message}
                    {/capture}
                    <br/>
                    {$extraMessage|inlineHelp}
                {/if}
            </td>
            {/foreach}
    </table>

</div>

{if !$geoIPDatabasesInstalled}
    <h2 id="geoip-db-mangement">{'UserCountry_GeoIPDatabases'|translate}</h2>
{else}
    <h2 id="geoip-db-mangement">{'UserCountry_SetupAutomaticUpdatesOfGeoIP_js'|translate}</h2>
{/if}

{if $showGeoIPUpdateSection}
    <div id="manage-geoip-dbs" style="width:900px" class="adminTable">

    {if !$geoIPDatabasesInstalled}
        <div id="geoipdb-screen1">
            <p>{'UserCountry_PiwikNotManagingGeoIPDBs'|translate}</p>

            <div class="geoipdb-column-1">
                <p>{'UserCountry_IWantToDownloadFreeGeoIP'|translate}</p>
                <input type="button" class="submit" value="{'General_GetStarted'|translate}..." id="start-download-free-geoip"/>
            </div>
            <div class="geoipdb-column-2">
                <p>{'UserCountry_IPurchasedGeoIPDBs'|translate:'<a href="http://www.maxmind.com/en/geolocation_landing?rId=piwik">':'</a>'}</p>
                <input type="button" class="submit" value="{'General_GetStarted'|translate}..." id="start-automatic-update-geoip"/>
            </div>
        </div>
        <div id="geoipdb-screen2-download" style="display:none">
            <p class='loadingPiwik'><img src='./themes/default/images/loading-blue.gif'/>
        {'UserCountry_DownloadingDb'|translate:"<a href=\"$geoLiteUrl\">GeoLiteCity.dat</a>"}...</p>
	<div id="geoip-download-progress"></div>
</div>
{/if}
{include file="UserCountry/templates/updaterSetup.tpl"}
{else}
<p style="width:900px" class="form-description">{'UserCountry_CannotSetupGeoIPAutoUpdating'|translate}</p>
{/if}
</div>

{include file="CoreAdminHome/templates/footer.tpl"}

