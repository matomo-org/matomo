

{if isset($displayfirstWebsiteSetupSuccess)}

<span id="toFade" class="success">
	{'Installation_SetupWebsiteSetupSuccess'|translate:$displaySiteName}
	<img src="themes/default/images/success_medium.png" />
</span>
{/if}

<h1>{'SitesManager_TrackingTags'|translate:$displaySiteName}</h1>
{$trackingHelp}
<br/><br/>
<h1>{'Installation_LargePiwikInstances'|translate}</h1>
{'Installation_JsTagArchivingHelp'|translate}

{literal}
<style>
code {
	font-size:80%;
}
</style>
<script>
$(document).ready( function(){
	$('code').click( function(){ $(this).select(); });
});
</script>

{/literal}