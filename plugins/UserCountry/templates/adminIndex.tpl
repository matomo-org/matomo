{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}

<h2>{'UserCountry_Geolocation'|translate}</h2>

<div style="width:900px">

<p>{'UserCountry_GeolocationPageDesc'|translate}</p>

<table class="adminTable">
	{foreach from=$locationProviders key=id item=provider}
	<tr>
		<td width="120">
			<p>
				<input class="current-location-provider" name="current-location-provider" value="{$id}" type="radio" {if $currentProviderId eq $id}checked="checked"{/if} id="provider_input_{$id}" style="cursor:pointer" {if $provider.status neq 1}disabled="disabled"{/if}/>
				<label for="provider_input_{$id}">{$provider.title|translate}</label><br/>
				<span class='loadingPiwik' style='display:none'><img src='./themes/default/images/loading-blue.gif' /></span>
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
			<span class="ajaxSuccess">{$provider.description|translate}</span>
		</td>
		<td>
		{if $provider.status eq 1}
			{capture assign=currentLocation}
			{'UserCountry_CurrentLocationIntro'|translate}:
			<p style="text-align:left;">
				<span class='loadingPiwik' style='display:none'><img src='./themes/default/images/loading-blue.gif' /> {'General_Loading_js'|translate}</span>
				<span class='location'><strong><em>{$provider.location}</em></strong></span>
			</p>
			<a href="#" class="refresh-loc" data-impl-id="{$id}"><em>{'Dashboard_Refresh_js'|translate}</em></a>
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

