{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
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

<table class="adminTable">
	<tr>
		<th>{'UserCountry_LocationProvider'|translate}</th>
		<th>{'General_Description'|translate}</th>
		<th>{'General_InfoFor'|translate:$thisIP}</th>
	</tr>
	{foreach from=$locationProviders key=id item=provider}
	<tr>
		<td width="140">
			<p>
				<input class="current-location-provider" name="current-location-provider" value="{$id}" type="radio" {if $currentProviderId eq $id}checked="checked"{/if} id="provider_input_{$id}" style="cursor:pointer" {if $provider.status neq 1}disabled="disabled"{/if}/>
				<label for="provider_input_{$id}" style="font-size:1.2em">{$provider.title|translate}</label><br/>
				<span class='loadingPiwik' style='display:none'><img src='./themes/default/images/loading-blue.gif' /></span>
				<span class="ajaxSuccess" style='display:none'>{'General_Done'|translate}</span>
			</p>
			<p style="margin-left:.5em">
				<strong><em>
				{if $provider.status eq 0}
				{'General_NotInstalled'|translate}
				{elseif $provider.status eq 1}
				<span style="color:green">{'General_Installed'|translate}</span>
				{elseif $provider.status eq 2}
				<span style="color:red">{'General_Broken'|translate}</span>
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
				<span class='loadingPiwik' style='display:none;position:absolute'><img src='./themes/default/images/loading-blue.gif' /> {'General_Loading_js'|translate}</span>
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
		{elseif $provider.status eq 2}
			{capture assign=brokenReason}
				<strong><em>{'General_Error'|translate}:</strong></em> {$provider.statusMessage}
			{/capture}
			{$brokenReason|inlineHelp}
		{/if}
		</td>
	{/foreach}
</table>

</div>

{include file="CoreAdminHome/templates/footer.tpl"}

