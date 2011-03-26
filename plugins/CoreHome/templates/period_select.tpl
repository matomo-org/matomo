{loadJavascriptTranslations plugins='CoreHome'}

<div id="periodString">
	<div id="date">{'General_DateRange'|translate}: <b>{$prettyDate}</b> <img src='themes/default/images/icon-calendar.gif' alt="" /></div>
	<div id="periodMore">
		<div class="period-date">
			<h6>{'General_Date'|translate}</h6>
			<div id="datepicker"></div>
		</div>
		<div class="period-type">
			<h6>{'General_Period'|translate}</h6>            
			<span id="otherPeriods">
			{foreach from=$periodsNames  key=label item=thisPeriod}
				<input type="radio" name="period" id="period_id_{$label}" value="{url period=$label}"{if $label==$period} checked="checked"{/if} />
				<label for="period_id_{$label}" >{$thisPeriod.singular}</label><br />
			{/foreach}
			</span>
			<input tabindex="3" type="submit" value="Apply Date Range" id="calendarRangeApply">
			{ajaxLoadingDiv id=ajaxLoadingCalendar}
		</div>
	</div>
</div>

<!-- TODO: MOVE THIS OUT OF THE TEMPLATE -->
{literal}<script type="text/javascript">
$(document).ready(function() {
    // this will trigger to change only the period value on search query and hash string.
    $("#otherPeriods input").bind('click',function(e) {
        var request_URL = $(e.target).attr("value");
        var period = broadcast.getValueFromUrl('period',request_URL);
        if(period == 'range') return true;
        broadcast.propagateNewPage('period='+period+'&date='+piwik.currentDateString);
        piwikHelper.showAjaxLoading('ajaxLoadingCalendar');
		return true;
    });
    
	function onDateRangeSelect(dateText, inst)
	{
		var toOrFrom = inst.id == 'calendarFrom' ? 'From' : 'To';
		//alert(dateText + toOrFrom);
		$('#inputCalendar'+toOrFrom).val(dateText);
	}
     
     // this will trigger to change only the period value on search query and hash string.
     $("#period_id_range").bind('click', function(e) {
			$('.period-date').html('<div id="calendarRangeFrom"><h6>From<input tabindex="1" type="text" id="inputCalendarFrom" name="inputCalendarFrom"/></h6><div id="calendarFrom"></div></div>'+
			 			 				'<div id="calendarRangeTo"><h6>To<input tabindex="2" type="text" id="inputCalendarTo" name="inputCalendarTo"/></h6><div id="calendarTo"></div></div>');
			var options = getDatePickerOptions();
				 
			// Custom Date range callback
			options.onSelect = onDateRangeSelect;
			// Do not highlight the period
			options.beforeShowDay = '';
			// Create both calendars
			options.defaultDate = piwik.startDateString;
			$('#calendarFrom').datepicker(options).datepicker("setDate", new Date(piwik.startDateString));
			// Technically we should trigger the onSelect event on the calendar, but I couldn't find how to do that
			// So calling the onSelect bind function manually...
			//$('#calendarFrom').trigger('dateSelected'); // or onSelect
			onDateRangeSelect(piwik.startDateString, { "id": "calendarFrom" } );
			
			// Same code for the other calendar
			options.defaultDate = piwik.endDateString;
			$('#calendarTo').datepicker(options).datepicker("setDate", new Date(piwik.endDateString));
			onDateRangeSelect(piwik.endDateString, { "id": "calendarTo" });
			
			
			// Bind the input fields to update the calendar's date when date is manually changed
			$('#inputCalendarFrom, #inputCalendarTo').keyup( function () {
				var fromOrTo = this.id == 'inputCalendarFrom' ? 'From' : 'To';
				var dateInput = $(this).val();
				$("#calendar"+fromOrTo).datepicker("setDate", new Date(dateInput));
			});

			// If not called, the first date appears light brown instead of dark brown
			$('.ui-state-hover').removeClass('ui-state-hover');
			
			// Apply date range button will reload the page with the selected range
    	 	$('#calendarRangeApply')
    	 		.bind('click', function() {
    	 	        var request_URL = $(e.target).attr("value");
    	 	        var dateFrom = $('#inputCalendarFrom').val(), 
    	 	        	dateTo = $('#inputCalendarTo').val(),
    	 	        	oDateFrom = new Date(dateFrom),
    	 	        	oDateTo = new Date(dateTo);
    	 	        
    	 	        if( !isValidDate(oDateFrom )
    	 	        	|| !isValidDate(oDateTo )
    	 	        	|| oDateFrom > oDateTo )
    	 	        {
    	 	        	alert('Invalid Date Range, Please Try Again');
    	 	        	return false;
    	 	        }
    	         	piwikHelper.showAjaxLoading('ajaxLoadingCalendar');
    	 	        broadcast.propagateNewPage('period=range&date='+dateFrom+','+dateTo);
    	 		})
    	 		.show();
			return true;
    });
     function isValidDate(d) {
    	  if ( Object.prototype.toString.call(d) !== "[object Date]" )
    	    return false;
    	  return !isNaN(d.getTime());
    	}

     var period = broadcast.getValueFromHash('period');
     if(period == 'range') { 
     	 $("#period_id_range").click();
     }
});</script>
<style>
#calendarRangeTo { float:right;     margin-left: 20px;}
#calendarRangeFrom { float:left; }
#inputCalendarFrom, #inputCalendarTo {
	margin-left: 10px;
    width: 90px;
}
#calendarRangeApply {
display:none;
margin-top:10px;
margin-left:10px; 
}
#invalidDateRange {
	display:none;
}
</style>
{/literal}
