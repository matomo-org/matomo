<h2>{'Installation_Welcome'|translate}</h2>

{if $newInstall}
{'Installation_WelcomeHelp'|translate:$totalNumberOfSteps}
{else}
<p>{'Installation_ConfigurationHelp'|translate}</p>
<br />
<div class="error">
{$errorMessage}
</div>
{/if}

{literal}
<script type="text/javascript">
<!--
$(function() {
if (document.location.protocol === 'https:') {
	$('p.nextStep a').attr('href', $('p.nextStep a').attr('href') + '&clientProtocol=https');
}
});
//-->
</script>
{/literal}

{if !$showNextStep}
<p class="nextStep">
	<a href="{url}">{'General_Refresh'|translate} &raquo;</a>
</p>
{/if}
