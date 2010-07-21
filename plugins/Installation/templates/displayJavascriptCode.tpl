

{if isset($displayfirstWebsiteSetupSuccess)}

<span id="toFade" class="success">
	{'Installation_SetupWebsiteSetupSuccess'|translate:$displaySiteName}
	<img src="themes/default/images/success_medium.png" />
</span>
{/if}

{$trackingHelp}
<br/><br/>
<h2>{'Installation_LargePiwikInstances'|translate}</h2>
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