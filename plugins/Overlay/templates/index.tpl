
<div id="Overlay_Container">

	<div id="Overlay_Location">&nbsp;</div>
	
	<div id="Overlay_Sidebar"></div>
	
	<div id="Overlay_Error_NotLoading">
		<p>
			<span>{'Overlay_ErrorNotLoading'|translate|escape:'html'}</span>
		</p>
		<p>
			{if $ssl}
				{'Overlay_ErrorNotLoadingDetailsSSL'|translate|escape:'html'}
			{else}
				{'Overlay_ErrorNotLoadingDetails'|translate|escape:'html'}
			{/if}
		</p>
		<p>
			<a href="#">{'Overlay_ErrorNotLoadingLink'|translate|escape:'html'}</a>
		</p>
	</div>
	
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