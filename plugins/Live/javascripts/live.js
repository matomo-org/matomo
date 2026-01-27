/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(function() {
    var refreshWidget = function (element, refreshAfterXSecs) {
        // if the widget has been removed from the DOM, abort
        if (!element.length || !$.contains(document, element[0])) {
            return;
        }

        function scheduleAnotherRequest()
        {
            setTimeout(function () { refreshWidget(element, refreshAfterXSecs); }, refreshAfterXSecs * 1000);
        }

        if (Visibility.hidden()) {
            scheduleAnotherRequest();
            return;
        }

        var lastMinutes = $(element).attr('data-last-minutes') || 3,
          translations = JSON.parse($(element).attr('data-translations'));

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            module: 'API',
            method: 'Live.getCounters',
            format: 'json',
            lastMinutes: lastMinutes
        }, 'get');
        ajaxRequest.setFormat('json');
        ajaxRequest.setCallback(function (data) {
            data = data[0];

            // set text and tooltip of visitors count metric
            var visitors = data['visitors'];
            if (visitors == 1) {
                var visitorsCountMessage = translations['one_visitor'];
            }
            else {
                var visitorsCountMessage = sprintf(translations['visitors'], visitors);
            }
            $('.simple-realtime-visitor-counter', element)
              .attr('title', visitorsCountMessage)
              .find('div').text(visitors);

            // set text of individual metrics spans
            var metrics = $('.simple-realtime-metric', element);

            var visitsText = data['visits'] == 1
              ? translations['one_visit'] : sprintf(translations['visits'], data['visits']);
            $(metrics[0]).text(visitsText);

            var actionsText = data['actions'] == 1
              ? translations['one_action'] : sprintf(translations['actions'], data['actions']);
            $(metrics[1]).text(actionsText);

            var lastMinutesText = lastMinutes == 1
              ? translations['one_minute'] : sprintf(translations['minutes'], lastMinutes);
            $(metrics[2]).text(lastMinutesText);

            scheduleAnotherRequest();
        });
        ajaxRequest.send();
    };

    var exports = require("piwik/Live");
    exports.initSimpleRealtimeVisitorWidget = function () {
        $('.simple-realtime-visitor-widget').each(function() {
            var $this = $(this),
              refreshAfterXSecs = $this.attr('data-refreshAfterXSecs');
            if ($this.attr('data-inited')) {
                return;
            }

            $this.attr('data-inited', 1);

            setTimeout(function() { refreshWidget($this, refreshAfterXSecs ); }, refreshAfterXSecs * 1000);
        });
    };
});
