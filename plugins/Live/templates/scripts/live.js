/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function check_for_dupe(prev, last)
{
	idVisit = $(prev).attr('id');
	//console.log($('#'+idVisit));
	if(idVisit && $('#'+idVisit)){
	$('#'+idVisit).last().remove();
	}
	if(idVisit) {
		return last.length >= 1 && (prev.html() == last.html());
	}
	return 0;
}


// Pass the most recent timestamp known to the API
var liveMinTimestamp = 0;
function lastMinTimestamp()
{
	minTimestamp = $('#visitsLive > div:lt(1) .serverTimestamp').html();
	if(!isNaN(minTimestamp)
			&& parseInt(minTimestamp)==minTimestamp) 
	{
		updateTotalVisits();
		liveMinTimestamp = minTimestamp;
		return liveMinTimestamp;
	}
	return false;
}
var pauseImage = "plugins/Live/templates/images/pause.gif";
var pauseDisabledImage = "plugins/Live/templates/images/pause_disabled.gif";
var playImage = "plugins/Live/templates/images/play.gif";
var playDisabledImage = "plugins/Live/templates/images/play_disabled.gif";
function onClickPause()
{
	$('#pauseImage').attr('src', pauseImage);
	$('#playImage').attr('src', playDisabledImage);
	return pauseSpy();
}
function onClickPlay()
{
	$('#playImage').attr('src', playImage);
	$('#pauseImage').attr('src', pauseDisabledImage);
	return playSpy();
}

/* TOOLTIP */
$('#visitsLive label').tooltip({
    track: true,
    delay: 0,
    showURL: false,
    showBody: " - ",
    fade: 250
});
