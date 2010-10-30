{literal}
<script type="text/javascript">
	$('#feedback-retry').click(function() {
		$('#feedback-sent').hide().empty();
		$('#feedback-form').show();
		return false;
	});
</script>
{/literal}

{if isset($ErrorString)}
	<div id="feedback-error"><strong>{'General_Error'|translate}:</strong> {$ErrorString}</div>
	<p>{'Feedback_ManuallySendEmailTo'|translate} <a href='mailto:{$feedbackEmailAddress}?subject={'[Feedback form - Piwik]'|escape:"hex"}&body={$message|stripeol|escape:"hex"}'>{$feedbackEmailAddress}</a></p>
	<textarea cols="53" rows="10" readonly="readonly">{$message}</textarea>
    <p><a href="#" id="feedback-retry"><img src="plugins/Feedback/images/go-previous.png" border="0" title="{'General_Previous'|translate}" alt="[{'General_Previous'|translate}]" /></a></p>
{else}
	<div id="feedback-success">{'Feedback_MessageSent'|translate}</div>
	<p><strong>{'Feedback_ThankYou'|translate}</strong></p>
	<p>-- {'Feedback_ThePiwikTeam'|translate}</p>
{/if}
