{literal}
<script type="text/javascript">
$(function() {
	$('#feedback-contact').click(function() {
		$('#feedback-faq').hide();
		$('#feedback-form').show();
		return false;
	});

	$('#feedback-home').click(function() {
		$('#feedback-form').hide();
		$('#feedback-faq').show();
		return false;
	});

	$('#feedback-form-submit').click(function() {
		var feedback = $('#feedback-form form');
		$('#feedback-form').hide();
		$.post(feedback.attr('action'), feedback.serialize(), function (data) {
			$('#feedback-sent').show().html(data);
		});
		return false;
	});
});
</script>
{/literal}

  <div id="feedback-faq">
    <p><strong>{'Feedback_DoYouHaveBugReportOrFeatureRequest'|translate}</strong></p>
    <p> &bull; {'Feedback_ViewAnswersToFAQ'|translate:"<a target='_blank' href='?module=Proxy&action=redirect&url=http://piwik.org/faq/'>":"</a>"}.</p>
    <ul>
      <li>» {'Feedback_WhyAreMyVisitsNoTracked'|translate}</li>
      <li>» {'Feedback_HowToExclude'|translate}</li>
      <li>» {'Feedback_WhyWrongCountry'|translate}</li>
      <li>» {'Feedback_HowToAnonymizeIP'|translate}</li>
    </ul>
    <p> &bull; {'Feedback_VisitTheForums'|translate:"<a target='_blank' href='?module=Proxy&action=redirect&url=http://forum.piwik.org/'>":"</a>"}.</p>
    <p> &bull; {'Feedback_LearnWaysToParticipate'|translate:"<a target='_blank' href='?module=Proxy&action=redirect&url=http://piwik.org/contribute/'>":"</a>"}.</p>
    <br />
    <p><strong>{'Feedback_SpecialRequest'|translate}</strong></p>
    <p> &bull;  <a target='_blank' href="#" id="feedback-contact">{'Feedback_ContactThePiwikTeam'|translate}</a></p>
  </div>
  <div id="feedback-form" style="display:none;">
    <form method="post" action="index.php?module=Feedback&action=sendFeedback">
     <label>{'Feedback_IWantTo'|translate}</label>
        <select name="category">
          <option value="share">{'Feedback_CategoryShareStory'|translate}</option>
          <option value="sponsor">{'Feedback_CategorySponsor'|translate}</option>
          <option value="hire">{'Feedback_CategoryHire'|translate}</option>
          <option value="security">{'Feedback_CategorySecurity'|translate}</option>
        </select>
     <br />
		<label>{'Feedback_MyEmailAddress'|translate}</label>
        <input type="text" name="email" size="59" />
        <input type="hidden" name="nonce" value="{$nonce}" /><br />
      	<label>{'Feedback_MyMessage'|translate}<br /><i>{'Feedback_DetailsPlease'|translate}</i></label>
        <textarea name="body" cols="57" rows="10">Please write your message in English</textarea><br />
      	<label><a href="#" id="feedback-home"><img src="plugins/Feedback/images/go-previous.png" border="0" title="{'General_Previous'|translate}" alt="[{'General_Previous'|translate}]" /></a></label>
      <input id="feedback-form-submit" type="submit" class='submit' value="{'Feedback_SendFeedback'|translate}" />
    </form>
  </div>
  <div id="feedback-sent" style="display:none;">
  </div>
