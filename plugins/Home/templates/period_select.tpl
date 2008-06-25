<div id="periodString">
	<span id="date"><img src='plugins/Home/templates/images/more_date.gif' style="vertical-align:middle" alt="" /> {$prettyDate}</span> -&nbsp;
	<span id="periods"> 
		<span id="currentPeriod">{$periodsNames.$period}</span> 
		<span id="otherPeriods">
			{foreach from=$otherPeriods item=thisPeriod}
			| <a href='{url period=$thisPeriod}'>{$periodsNames.$thisPeriod}</a>
			{/foreach}
		</span>
	</span>
	<span id="calendar"></span>
</div>
<div style="clear:both"></div>

