<div class="piwik-donate-call">
	<div class="piwik-donate-message">
		{if isset($msg)}
			{$msg}
		{else}
		<p>{'CoreHome_DonateCall1'|translate}</p>
		<p><strong><em>{'CoreHome_DonateCall2'|translate}</em></strong></p>
		<p>{'CoreHome_DonateCall3'|translate:'<em><strong>':'</strong></em>'}</p>
		{/if}
	</div>
	
	<span id="piwik-worth">{'CoreHome_HowMuchIsPiwikWorth'|translate}</span>
	<div class="donate-form-instructions">({'CoreHome_DonateFormInstructions'|translate})</div>
	
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
		<input type="hidden" name="cmd" value="_s-xclick"/>
		<input type="hidden" name="hosted_button_id" value="DVKLY73RS7JTE"/>
		<input type="hidden" name="currency_code" value="USD"/>
		<input type="hidden" name="on0" value="Piwik Supporter"/>
		
		<div class="piwik-donate-slider">
			 <div class="slider-range">
			 	<div class="slider-position"></div>
			 </div>
			 <div style="display:inline-block">
				 <div class="slider-donate-amount">$30/{'CoreHome_YearShort_js'|translate}</div>
			 
				 <img class="slider-smiley-face" width="40" height="40" src="themes/default/images/smileyprog_1.png"/>
			 </div>
			 
			 <input type="hidden" name="os0" value="Option 1"/>
		</div>
		
		<div class="donate-submit">
			<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=RPL23NJURMTFA&bb2_screener_=1357583494+83.233.186.82" target="_blank">{'CoreHome_MakeOneTimeDonation'|translate}</a>
			<input type="image" src="themes/default/images/paypal_subscribe.gif" border="0" name="submit" title="{'CoreHome_SubscribeAndBecomePiwikSupporter'|translate}"/>
		</div>
		
		<!-- to cache images -->
		<img style="display:none" src="themes/default/images/smileyprog_0.png"/>
		<img style="display:none" src="themes/default/images/smileyprog_1.png"/>
		<img style="display:none" src="themes/default/images/smileyprog_2.png"/>
		<img style="display:none" src="themes/default/images/smileyprog_3.png"/>
		<img style="display:none" src="themes/default/images/smileyprog_4.png"/>
	</form>
	{if isset($footerMessage)}
	<div class="form-description">
		{$footerMessage}
	</div>
	{/if}
</div>
{literal}
<script type="text/javascript">
$(document).ready(function() {
	// Note: this will cause problems if more than one donate form is on the page
	$('.piwik-donate-slider').each(function() {
		$(this).trigger('piwik:changePosition', {position: 1});
	});
});
</script>
{/literal}
