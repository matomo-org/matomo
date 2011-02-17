/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
// first I'm ensuring that 'last' has been initialised (with last.constructor == Object),
// then prev.html() == last.html() will return true if the HTML is the same, or false,
// if I have a different entry.
function check_for_dupe(prev, last)
{
//console.log(prev, last);//  idVisit = $(prev).attr('id');//

//  if(idVisit && $('#'+idVisit)){ $('#'+idVisit).last().remove(); }
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
	updateTotalVisits();
	updateVisitBox();
	minTimestamp = $('#visitsLive > div:lt(1) .serverTimestamp').html();
	if(!isNaN(minTimestamp)
			&& parseInt(minTimestamp)==minTimestamp) 
	{
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
