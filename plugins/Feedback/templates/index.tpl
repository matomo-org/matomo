{literal}
<script type="text/javascript">
$(function() {
	$('#feedback-contact').click(function() {
		$('#feedback-faq').toggle();
		$('#feedback-form').toggle();
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
    <p><strong>{'Feedback_PleaseUseForum'|translate}</strong></p>
    <p>» {'Feedback_ViewAnswersTo'|translate} <a href="http://piwik.org/faq/">{'Feedback_FrequentlyAskedQuestions'|translate}</a>.</p>
    <ul>
      <li>{'Feedback_WhyAreMyVisitsNoTracked'|translate}</li>
      <li>{'Feedback_WhyNoData'|translate}</li>
      <li>{'Feedback_HowToExclude'|translate}</li>
      <li>{'Feedback_WhyWrongCountry'|translate}</li>
      <li>{'Feedback_HowToAnonymizeIP'|translate}</li>
    </ul>
    <p>» {'Feedback_VisitThe'|translate} <a href="http://forum.piwik.org/">{'Feedback_Forums'|translate}</a>.</p>
    <p>» {'Feedback_LearnWaysTo'|translate} <a href="http://piwik.org/contribute/">{'Feedback_Participate'|translate}</a>.</p>
    <p>» {'Feedback_SpecialRequest'|translate} <a href="#" id="feedback-contact">{'Feedback_ContactUs'|translate}</a>.</p>
  </div>
  <div id="feedback-form" style="display:none;">
    <form method="post" action="index.php?module=Feedback&action=sendFeedback">
      <p><strong>{'Feedback_IWantTo'|translate}</strong>
        <select name="category">
          <option value="share">{'Feedback_CategoryShareStory'|translate}</option>
          <option value="sponsor">{'Feedback_CategorySponsor'|translate}</option>
          <option value="hire">{'Feedback_CategoryHire'|translate}</option>
          <option value="security">{'Feedback_CategorySecurity'|translate}</option>
        </select>
      </p>
      <p><strong>{'Feedback_MyEmailAddress'|translate}</strong><br />
        <input type="text" name="email" size="40" />
        <input type="hidden" name="nonce" value="{$nonce}" /></p>
      <p><strong>{'Feedback_MyMessage'|translate}</strong> {'Feedback_DetailsPlease'|translate}<br />
        <textarea name="body" cols="37" rows="10"></textarea></p>
      <p><input id="feedback-form-submit" type="submit" value="{'Feedback_SendFeedback'|translate}" /></p>
      <p><a href="#" id="feedback-home"><img src="plugins/Feedback/images/go-previous.png" border="0" title="{'General_Previous'|translate}" alt="[{'General_Previous'|translate}]" /></a></p>
    </form>
  </div>
  <div id="feedback-sent" style="display:none;">
  </div>
