{loadJavascriptTranslations plugins='CoreHome'}
<script type="text/javascript" src="plugins/CoreHome/templates/calendar.js"></script>
<script type="text/javascript" src="plugins/CoreHome/templates/date.js"></script>

<span id="periodString">
	<span id="date"><img src='themes/default/images/icon-calendar.gif' style="vertical-align:middle" alt="" /> {$prettyDate}</span> -&nbsp;
	<span id="periods"> 
		<span id="currentPeriod">{$periodsNames.$period.singular}</span> 
		<span id="otherPeriods">
			{foreach from=$otherPeriods item=thisPeriod} | <a href='{url period=$thisPeriod}'>{$periodsNames.$thisPeriod.singular}</a>{/foreach}
		</span>
	</span>
	<br />
	<span id="datepicker"></span>
</span>

{literal}<script language="javascript">
$(document).ready(function() {
     // this will trigger to change only the period value on search query and hash string.
     $("#otherPeriods a").bind('click',function(e) {
        e.preventDefault();                            
        var request_URL = $(e.target).attr("href");
        var new_period = broadcast.getValueFromUrl('period',request_URL);
        broadcast.propagateNewPage('period='+new_period);
    });
});</script>
{/literal}

<div style="clear:both;"></div>
