{loadJavascriptTranslations plugins='CoreHome'}

<div id="periodString">
	<div id="date">{'General_DateRange'|translate} <b>{$prettyDate}</b> <img src='themes/default/images/icon-calendar.gif' alt="" /></div>
	<div id="periodMore">
		<div class="period-date">
			<h6>{'General_Date'|translate}</h6>
			<div id="datepicker"></div>
		</div>
		<div class="period-type">
			<h6>{'General_Period'|translate}</h6>            
			<span id="otherPeriods">{foreach from=$periodsNames  key=label item=thisPeriod}<input type="radio" name="period" autocomplete="off" id="period_id_{$label}" value="{url period=$label}"{if $label==$period} checked="checked"{/if} /><label for="period_id_{$label}" >{$thisPeriod.singular}</label><br />{/foreach}</span>
		</div>
	</div>
</div>

{literal}<script type="text/javascript">
$(document).ready(function() {
     // this will trigger to change only the period value on search query and hash string.
     $("#otherPeriods input").bind('click',function(e) {
        var request_URL = $(e.target).attr("value");
        var new_period = broadcast.getValueFromUrl('period',request_URL);
        broadcast.propagateNewPage('period='+new_period);
		return true;
    });
});</script>
{/literal}
