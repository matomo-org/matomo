
<div id="Insight_NoFrame">

	{capture name="link"}index.php?module=Insight&action=startInsightSession&idsite={$idSite}&period={$period}&date={$date}{if $targetUrl}#{$targetUrl}{/if}{/capture}
	{capture name="linkTag"}<a id="Insight_Link" href="{$smarty.capture.link}" target="_blank">{/capture}
	
	{'Insight_NoFrameModeText'|translate|escape:'html'|sprintf:'<br />':$smarty.capture.linkTag:'</a>'}
	
	<script type="text/javascript">
		window.open('{$smarty.capture.link}', '_newtab');
	</script>

</div>