{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{loadJavascriptTranslations plugins='SitesManager'}

<script type="text/javascript">
{capture assign=excludedIpHelpPlain}{'SitesManager_HelpExcludedIps'|translate:"1.2.3.*":"1.2.*.*"}<br /><br /> {'SitesManager_YourCurrentIpAddressIs'|translate:"<i>$currentIpAddress</i>"}{/capture}
{assign var=excludedIpHelp value=$excludedIpHelpPlain|inlineHelp}
var excludedIpHelp = '{$excludedIpHelp|escape:javascript}';
var aliasUrlsHelp = '{'SitesManager_AliasUrlHelp'|translate|inlineHelp|escape:javascript}';
{capture assign=defaultTimezoneHelpPlain}
	{if $timezoneSupported}
		{'SitesManager_ChooseCityInSameTimezoneAsYou'|translate}
	{else}
		{'SitesManager_AdvancedTimezoneSupportNotFound'|translate}
	{/if} <br /><br />{'SitesManager_UTCTimeIs'|translate:$utcTime}
{/capture}

{capture assign=timezoneHelpPlain}
	{$defaultTimezoneHelpPlain}
	<br /><br />{'SitesManager_ChangingYourTimezoneWillOnlyAffectDataForward'|translate}
{/capture}

{capture assign=currencyHelpPlain}
	{'SitesManager_CurrencySymbolWillBeUsedForGoals'|translate|inlineHelp}
{/capture}

{capture assign=ecommerceHelpPlain}
	{'SitesManager_EcommerceHelp'|translate}
	<br />
	{'SitesManager_PiwikOffersEcommerceAnalytics'|translate:"<a href='http://piwik.org/docs/ecommerce-analytics/' target='_blank'>":"</a>"}
{/capture}

{capture assign=excludedQueryParametersHelp}
	{'SitesManager_ListOfQueryParametersToExclude'|translate}
	<br /><br />
	{'SitesManager_PiwikWillAutomaticallyExcludeCommonSessionParameters'|translate:"phpsessid, sessionid, ..."}
{/capture}
{assign var=excludedQueryParametersHelp value=$excludedQueryParametersHelp|inlineHelp}
var excludedQueryParametersHelp = '{$excludedQueryParametersHelp|escape:javascript}';
var timezoneHelp = '{$timezoneHelpPlain|inlineHelp|escape:javascript}';
var currencyHelp = '{$currencyHelpPlain|escape:javascript}';
var ecommerceHelp = '{$ecommerceHelpPlain|inlineHelp|escape:javascript}';
var ecommerceEnabled = '{'SitesManager_EnableEcommerce'|translate|escape:javascript}';
var ecommerceDisabled = '{'SitesManager_NotAnEcommerceSite'|translate|escape:javascript}';
{assign var=defaultTimezoneHelp value=$defaultTimezoneHelpPlain|inlineHelp};

var sitesManager = new SitesManager ( {$timezones}, {$currencies}, '{$defaultTimezone}', '{$defaultCurrency}');

{literal}
$(document).ready( function() {
	sitesManager.init();
});
</script>

<style type="text/css">
.entityTable tr td {
    vertical-align: top;
    padding-top:7px;
}

.addRowSite:hover, .editableSite:hover, .addsite:hover, .cancel:hover, .deleteSite:hover, .editSite:hover, .updateSite:hover{
	cursor: pointer;
}
.addRowSite a {
	text-decoration: none;
}
.addRowSite {
	padding:1em;
	font-weight:bold;
}
#editSites {
	vertical-align: top;
}
option, select {
	font-size:11px;
}
textarea {
font-size:9pt;
}
.admin thead th {
vertical-align:middle;
}
.ecommerceInactive {
 color: #666666;
}
</style>
{/literal}

<h2>{'SitesManager_WebsitesManagement'|translate}</h2>
<p>{'SitesManager_MainDescription'|translate}
{'SitesManager_YouCurrentlyHaveAccessToNWebsites'|translate:"<b>$adminSitesCount</b>"}
{if $isSuperUser}
<br />{'SitesManager_SuperUserCan'|translate:"<a href='#globalSettings'>":"</a>"}
{/if}
</p>
{ajaxErrorDiv}
{ajaxLoadingDiv}

{capture assign=createNewWebsite}
	<div class="addRowSite"><img src='plugins/UsersManager/images/add.png' alt="" /> {'SitesManager_AddSite'|translate}</div>
{/capture}

{if $adminSites|@count == 0}
	{'SitesManager_NoWebsites'|translate}
{else}
    <div class="ui-confirm" id="confirm">
        <h2></h2>
        <input role="yes" type="button" value="{'General_Yes'|translate}" />
        <input role="no" type="button" value="{'General_No'|translate}" />
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
			<th>{'SitesManager_Timezone'|translate}</th>
			<th>{'SitesManager_Currency'|translate}</th>
			<th>{'Goals_Ecommerce'|translate}</th>
			<th> </th>
			<th> </th>
			<th> {'SitesManager_JsTrackingTag'|translate} </th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$adminSites key=i item=site}
			<tr id="row{$i}">
				<td id="idSite">{$site.idsite}</td>
				<td id="siteName" class="editableSite">{$site.name}</td>
				<td id="urls" class="editableSite">{foreach from=$site.alias_urls item=url}{$url|replace:"http://":""}<br />{/foreach}</td>       
				<td id="excludedIps" class="editableSite">{foreach from=$site.excluded_ips item=ip}{$ip}<br />{/foreach}</td>       
				<td id="excludedQueryParameters" class="editableSite">{foreach from=$site.excluded_parameters item=parameter}{$parameter}<br />{/foreach}</td>       
				<td id="timezone" class="editableSite">{$site.timezone}</td>
				<td id="currency" class="editableSite">{$site.currency}</td>
				<td id="ecommerce" class="editableSite">{if $site.ecommerce}<span class='ecommerceActive'>{'General_Yes'|translate}</span>{else}<span class='ecommerceInactive'>-</span>{/if}</td>
				<td><span id="row{$i}" class='editSite link_but'><img src='themes/default/images/ico_edit.png' title="{'General_Edit'|translate}" border="0"/> {'General_Edit'|translate}</span></td>
				<td><span id="row{$i}" class="deleteSite link_but"><img src='themes/default/images/ico_delete.png' title="{'General_Delete'|translate}" border="0" /> {'General_Delete'|translate}</span></td>
				<td><a href='{url action=displayJavascriptCode idSite=$site.idsite updated=false}'>{'SitesManager_ShowTrackingTag'|translate}</a></td>
			</tr>
			{/foreach}
		</tbody>
	</table>
	{if $isSuperUser}	
		{$createNewWebsite}	
	{/if}
	</div>
{/if}

{if $isSuperUser}	
<br />
	<a name='globalSettings'></a>
	<h2>{'SitesManager_GlobalWebsitesSettings'|translate}</h2>
	<br />
	<table style='width:600px' class="adminTable" >
		
		<tr><td colspan="2">
				<b>{'SitesManager_GlobalListExcludedIps'|translate}</b>
				<p>{'SitesManager_ListOfIpsToBeExcludedOnAllWebsites'|translate} </p>
			</td></tr>
			<tr><td>
			<textarea cols="30" rows="3" id="globalExcludedIps">{$globalExcludedIps}
</textarea>
			</td><td>
				{$excludedIpHelp}
		</td></tr>
		
		<tr><td colspan="2">
				<b>{'SitesManager_GlobalListExcludedQueryParameters'|translate}</b>
				<p>{'SitesManager_ListOfQueryParametersToBeExcludedOnAllWebsites'|translate} </p>
			</td></tr>
			<tr><td>
			<textarea cols="30" rows="3" id="globalExcludedQueryParameters">{$globalExcludedQueryParameters}
</textarea>
			</td><td>
				{$excludedQueryParametersHelp}
		</td></tr>
		
		<tr><td colspan="2">
				<b>{'SitesManager_DefaultTimezoneForNewWebsites'|translate}</b>
				<p>{'SitesManager_SelectDefaultTimezone'|translate} </p>
			</td></tr>
			<tr><td>
				<div id='defaultTimezone'></div>
			</td><td>
				{$defaultTimezoneHelp}
		</td></tr>
		
		<tr><td colspan="2">
				<b>{'SitesManager_DefaultCurrencyForNewWebsites'|translate}</b>
				<p>{'SitesManager_SelectDefaultCurrency'|translate} </p>
			</td></tr>
			<tr><td>
				<div id='defaultCurrency'></div>
			</td><td>
				{$currencyHelpPlain}
		</td></tr>
	</table>
	<span style='margin-left:20px'>
		<input type="submit" class="submit" id='globalSettingsSubmit' value="{'General_Save'|translate}" />
	</span>
	{ajaxErrorDiv id=ajaxErrorGlobalSettings}
	{ajaxLoadingDiv id=ajaxLoadingGlobalSettings}
{/if}
{if $showAddSite}
<script type="text/javascript">{literal}
$(document).ready(function(){
    $('.addRowSite:first').trigger('click');
});
{/literal}</script>
{/if}

<br /><br /><br /><br />
{include file="CoreAdminHome/templates/footer.tpl"}
