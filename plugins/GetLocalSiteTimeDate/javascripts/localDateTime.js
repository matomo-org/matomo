
/*
* Formats the output date/time *
*/

function form_date(date) {
	var vDate=date.split('/');
	return vDate[2]+'/'+vDate[1]+'/'+vDate[0];
}


/*
*    Get current date/time according to the timezone.         *
*    If no timezone parameter passed outputs local time/date. *
*/

function cur_date(tz) {
const date = new Date()

var options = {
  timeZone: tz,
  year: 'numeric',
  month: '2-digit',
  day: '2-digit',
  hour: '2-digit',
  minute: '2-digit',
  second: '2-digit',
  hourCycle: 'h24',
 hour12: false
};

var ret_date = new Intl.DateTimeFormat('en-NZ', options).format(date);
var spl_date=ret_date.split(", "); 
return form_date(spl_date[0])+' '+spl_date[1];
}

/*
*	Infinite loop with 1s of interval.     *
*/
 
setInterval(function() {
var site_timezone=$(".site_timezone").val(); 

var site_time=cur_date(site_timezone);
var local_time=cur_date();

if (site_time===local_time) {
	$(".LocalWebTime").html("Local: "+local_time);
} else {
	$(".LocalWebTime").html("Local: "+local_time+"<br>Site: "+site_time);
}

}, 1000);

