/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    // TODO make it customizable
    var NUM_LAST_MINUTES = 30;
    var METRIC_TO_SHOW   = 'visits';
    var REFRESH_INTERVAL_SECONDS = 60;

    LiveTabApi.getSettings(function (settings) {
        NUM_LAST_MINUTES = settings.lastMinutes;
        METRIC_TO_SHOW = settings.metric;
        REFRESH_INTERVAL_SECONDS = settings.refreshInterval;
        updateTitle();
    });

    var originalTitle = $('title').text();

    function makeSeconds(seconds)
    {
        return seconds * 1000;
    }

    function shortenNumber (value) {
        if (!value) {
            return 0;
        }

        var suffix = ['', 'K', 'M', 'B', 'T'];

        var index = 0;
        while (value > 1000 && index < suffix.length - 1) {
            index++;
            value = (value / 1000).toFixed(2)
        }

        return (value) + suffix[index];
    }

    function updateTitle()
    {
        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            module: 'API',
            method: 'Live.getCounters',
            lastMinutes: NUM_LAST_MINUTES,
            format: 'JSON'
        }, 'get');
        ajaxRequest.setCallback(
            function (response) {
                if (!response || !response[0]) {

                    return;
                }

                var value = shortenNumber(response[0][METRIC_TO_SHOW]);

                $('title').text(value + ' - ' + originalTitle);

                setTimeout(updateTitle, makeSeconds(REFRESH_INTERVAL_SECONDS));
            }
        );
        ajaxRequest.send(false);
    }
});