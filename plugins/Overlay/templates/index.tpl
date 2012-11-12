
<div id="Overlay_Container">

	<div id="Overlay_Location">&nbsp;</div>
	
	<div id="Overlay_Sidebar"></div>
	
	<div id="Overlay_Loading">{'General_Loading_js'|translate|escape:'html'}</div>
	
	<div id="Overlay_Main">
		<iframe 
				id="Overlay_Iframe" 
				src="index.php?module=Overlay&action=startOverlaySession&idsite={$idSite}&period={$period}&date={$date}{if $targetUrl}#{$targetUrl}{/if}">
		</iframe>
	</div>
	
</div>


<script type="text/javascript">
	Piwik_Overlay.init();
	
	Piwik_Overlay_Translations = {literal}{{/literal}
		domain: "{'Overlay_Domain'|translate|escape:'html'}"
	{literal}}{/literal};
</script>