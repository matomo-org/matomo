{loadJavascriptTranslations plugins='CoreHome'}

<div id="periodString">
	<div id="date">{'General_DateRange'|translate} <b>{$prettyDate}</b> <img src='themes/default/images/icon-calendar.gif' alt="" /></div>
	<div id="periodMore">
		<div class="period-date">
			<h6>{'General_Date'|translate}</h6>
			<div id="datepicker"></div>
		</div>
		<div class="period-range" style="display:none;">
			<div id="calendarRangeFrom">
				<h6>{'General_DateRangeFrom_js'|translate}<input tabindex="1" type="text" id="inputCalendarFrom" name="inputCalendarFrom"/></h6>
				<div id="calendarFrom"></div>
			</div>
			<div id="calendarRangeTo">
				<h6>{'General_DateRangeTo_js'|translate}<input tabindex="2" type="text" id="inputCalendarTo" name="inputCalendarTo"/></h6>
				<div id="calendarTo"></div>
			</div>
		</div>
		<div class="period-type">
			<h6>{'General_Period'|translate}</h6>            
			<span id="otherPeriods">
			{foreach from=$periodsNames  key=label item=thisPeriod}
				<input type="radio" name="period" id="period_id_{$label}" value="{url period=$label}"{if $label==$period} checked="checked"{/if} />
				<label for="period_id_{$label}" >{$thisPeriod.singular}</label><br />
			{/foreach}
			</span>
			<input tabindex="3" type="submit" value="{'General_ApplyDateRange'|translate}" id="calendarRangeApply" />
			{ajaxLoadingDiv id=ajaxLoadingCalendar}
		</div>
	</div>
</div>
