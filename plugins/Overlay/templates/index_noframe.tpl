
<div id="Overlay_NoFrame">

	{capture name="link"}index.php?module=Overlay&action=startOverlaySession&idsite={$idSite}&period={$period}&date={$date}{if $targetUrl}#{$targetUrl}{/if}{/capture}
	{capture name="linkTag"}<a id="Overlay_Link" href="{$smarty.capture.link}" target="_blank">{/capture}
	
	{'Overlay_NoFrameModeText'|translate|escape:'html'|sprintf:'<br />':$smarty.capture.linkTag:'</a>'}
	
	<script type="text/javascript">
		window.open('{$smarty.capture.link}', '_newtab');
	</script>

</div>