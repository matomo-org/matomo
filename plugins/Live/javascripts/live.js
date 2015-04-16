/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * jQueryUI widget for Live visitors widget
 */

(function ($) {
    $.widget('piwik.liveWidget', {

        /**
         * Default settings for widgetPreview
         */
        options:{
            // Maximum numbers of rows to display in widget
            maxRows: 10,
            // minimal time in microseconds to wait between updates
            interval: 3000,
            // maximum time to wait between requests
            maxInterval: 300000,
            // url params to use for data request
            dataUrlParams: null,
            // callback triggered on a successful update (content of widget changed)
            onUpdate: null,
            // speed for fade animation
            fadeInSpeed: 'slow'
        },

        /**
         * current updateInterval used
         */
        currentInterval: null,

        /**
         * identifies if content has updated (eg new visits/views)
         */
        updated: false,

        /**
         * window timeout interval
         */
        updateInterval: null,

        /**
         * identifies if the liveWidget ist started or not
         */
        isStarted: true,

        /**
         * Update the widget
         *
         * @return void
         */
        _update: function () {

            this.updated = false;

            var that = this;

            var ajaxRequest = new ajaxHelper();
            ajaxRequest.addParams(this.options.dataUrlParams, 'GET');
            ajaxRequest.setFormat('html');
            ajaxRequest.setCallback(function (r) {
                that._parseResponse(r);

                // add default interval to last interval if not updated or reset to default if so
                if (!that.updated) {
                    that.currentInterval += that.options.interval;
                } else {
                    that.currentInterval = that.options.interval;
                    if (that.options.onUpdate) that.options.onUpdate();
                }

                // check new interval doesn't reach the defined maximum
                if (that.options.maxInterval < that.currentInterval) {
                    that.currentInterval = that.options.maxInterval;
                }

                if (that.isStarted) {
                    window.clearTimeout(that.updateInterval);
                    if (that.element.length && $.contains(document, that.element[0])) {
                        that.updateInterval = window.setTimeout(function() { that._update() }, that.currentInterval);
                    }
                }
            });
            ajaxRequest.send(false);
        },

        /**
         * Parses the given response and updates the widget if newer content is available
         *
         * @return void
         */
        _parseResponse: function (data) {
            if (!data || !data.length) {
                this.updated = false;
                return;
            }

            var items = $('li', $(data));
            for (var i = items.length; i--;) {
                this._parseItem(items[i]);
            }
        },

        /**
         * Parses the given item and updates or adds an entry to the list
         *
         * @param item to parse
         * @return void
         */
        _parseItem: function (item) {
            var visitId = $(item).attr('id');
            if ($('#' + visitId, this.element).length) {
                if ($('#' + visitId, this.element).html() != $(item).html()) {
                    this.updated = true;
                }
                $('#' + visitId, this.element).remove();
                $(this.element).prepend(item);
            } else {
                this.updated = true;
                $(item).hide();
                $(this.element).prepend(item);
                $(item).fadeIn(this.options.fadeInSpeed);
            }
            // remove rows if there are more than the maximum
            $('li:gt(' + (this.options.maxRows - 1) + ')', this.element).remove();
        },

        /**
         * Constructor
         *
         * @return void
         */
        _create: function () {

            if (!this.options.dataUrlParams) {
                console && console.error('liveWidget error: dataUrlParams needs to be defined in settings.');
                return;
            }

            this.currentInterval = this.options.interval;

            var self = this;

            this.updateInterval = window.setTimeout(function() { self._update(); }, this.currentInterval);
        },

        /**
         * Stops requests if widget is destroyed
         */
        _destroy: function () {

            this.stop();
        },

        /**
         * Triggers an update for the widget
         *
         * @return void
         */
        update: function () {
            this._update();
        },

        /**
         * Starts the automatic update cycle
         *
         * @return void
         */
        start: function () {
            this.isStarted = true;
            this.currentInterval = 0;
            this._update();
        },

        /**
         * Stops the automatic update cycle
         *
         * @return void
         */
        stop: function () {
            this.isStarted = false;
            window.clearTimeout(this.updateInterval);
        },

        /**
         * Return true in case widget is started.
         * @returns {boolean}
         */
        started: function() {
            return this.isStarted;
        },

        /**
         * Set the interval for refresh
         *
         * @param {int} interval  new interval for refresh
         * @return void
         */
        setInterval: function (interval) {
            this.currentInterval = interval;
        }
    });
})(jQuery);

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
                var visitorsCountMessage = translations['visitors'].replace('%s', visitors);
            }
            $('.simple-realtime-visitor-counter', element)
              .attr('title', visitorsCountMessage)
              .find('div').text(visitors);

            // set text of individual metrics spans
            var metrics = $('.simple-realtime-metric', element);

            var visitsText = data['visits'] == 1
              ? translations['one_visit'] : translations['visits'].replace('%s', data['visits']);
            $(metrics[0]).text(visitsText);

            var actionsText = data['actions'] == 1
              ? translations['one_action'] : translations['actions'].replace('%s', data['actions']);
            $(metrics[1]).text(actionsText);

            var lastMinutesText = lastMinutes == 1
              ? translations['one_minute'] : translations['minutes'].replace('%s', lastMinutes);
            $(metrics[2]).text(lastMinutesText);

            scheduleAnotherRequest();
        });
        ajaxRequest.send(true);
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

function onClickPause() {
    $('#pauseImage').hide();
    $('#playImage').show();
    return $('#visitsLive').liveWidget('stop');
}
function onClickPlay() {
    $('#playImage').hide();
    $('#pauseImage').show();
    return $('#visitsLive').liveWidget('start');
}

(function () {
    if (!Visibility.isSupported()) {
        return;
    }

    var isStoppedByBlur = false;

    function isStarted()
    {
        return $('#visitsLive').liveWidget('started');
    }

    function onTabBlur() {
        if (isStarted()) {
            isStoppedByBlur = true;
            onClickPause();
        }
    }

    function onTabFocus() {
        if (isStoppedByBlur && !isStarted()) {
            isStoppedByBlur = false;
            onClickPlay();
        }
    }

    Visibility.change(function (event, state) {
        if (Visibility.hidden()) {
            onTabBlur();
        } else {
            onTabFocus();
        }
    });
})();