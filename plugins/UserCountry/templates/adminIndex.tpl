{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}

<h2>{'UserCountry_Geolocation'|translate}</h2>

<div class="adminTable" style="max-width:65%">

<p>{'UserCountry_GeolocationPageDesc'|translate}</p>

{foreach from=$locationProviders key=id item=provider}
<p>
<input class="current-location-provider" name="current-location-provider" value="{$id}" type="radio" {if $currentProviderId eq $id}checked="checked"{/if}>{$provider.title|translate}</input>
<strong><em>
{if $provider.status eq 0}
{'General_NotInstalled'|translate}
{elseif $provider.status eq 1}
<span style="color:green">{'General_Installed'|translate}</span>
{elseif $provider.status eq 2}
<span style="color:red">{'General_Broken'|translate}</span>
{/if}
</em></strong>
<span class='loadingPiwik' style='display:none'><img src='./themes/default/images/loading-blue.gif' /></span>
</p>
<p>
{$provider.description|translate}
</p>
{if $provider.status eq 1}
<span class="ajaxSuccess">
	{'UserCountry_CurrentLocationIntro'|translate}:
	<p style="text-align:left;">
		<span class='loadingPiwik' style='display:none'><img src='./themes/default/images/loading-blue.gif' /> {'General_Loading_js'|translate}</span>
		<span class='location'><strong><em>{$provider.location}</em></strong></span>
	</p>
	<a href="#" class="refresh-loc" data-impl-id="{$id}"><em>{'Dashboard_Refresh_js'|translate}</em></a>
</span>
{/if}
{/foreach}

</div>

{include file="CoreAdminHome/templates/footer.tpl"}

