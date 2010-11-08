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
	// client-side test for https to handle the case where the server is behind a reverse proxy
	if (document.location.protocol === 'https:') {
		$('p.nextStep a').attr('href', $('p.nextStep a').attr('href') + '&clientProtocol=https');
	}

	// client-side test for broken tracker (e.g., mod_security rule)
	$('p.nextStep').hide();
	$.ajax({
		url: 'piwik.php',
		data: 'url=http://example.com',
		complete: function() {
			$('p.nextStep').show();
		},
		error: function(req) {
			$('p.nextStep a').attr('href', $('p.nextStep a').attr('href') + '&trackerStatus=' + req.status);
		}
	});
});
//-->
</script>
{/literal}

{if !$showNextStep}
<p class="nextStep">
	<a href="{url}">{'General_Refresh'|translate} &raquo;</a>
</p>
{/if}
