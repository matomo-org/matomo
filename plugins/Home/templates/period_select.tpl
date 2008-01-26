<div id="periodString" style="display:none">
	<span id="date"><img src='plugins/Home/templates/images/more_date.gif' style="vertical-align:middle"> {$prettyDate}</span> -&nbsp;
	<span id="periods"> 
		<span id="currentPeriod">{$period|ucfirst}</span> 
		<span id="otherPeriods">
			{foreach from=$otherPeriods item=thisPeriod}
			| <a href='{url period=$thisPeriod}'>{$thisPeriod|ucfirst}</a>
			{/foreach}
		</span>
	</span>
	<span id="calendar"></span>
</div>

<div style="clear:both"></div>