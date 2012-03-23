/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * jQuery Plugin for Live visitors widget
 */

(function($) {
    $.extend({
        liveWidget: new function() {
            
            /**
             * Default settings for widgetPreview
             */
            var settings = {
                    // Maximum numbers of rows to display in widget
                    maxRows: 10,
                    // minimal time in microseconds to wait between updates
                    interval: 3000,
                    // maximum time to wait between requests
                    maxInterval: 300000,
                    // ajax url to get required data
                    dataUrl: null,
                    // callback triggered on a successfull update (content of widget changed)
                    onUpdate: null,
                    // speed for fade animation
                    fadeInSpeed: 'slow'
            };
            
            var currentInterval, updated, updateInterval, liveWidget;
            var isStarted = true;
            
            /**
             * Update the widget
             */
            function update() {
                
                // is content updated (eg new visits/views)
                updated = false;
                
                // fetch data
                piwikHelper.queueAjaxRequest( $.get(settings.dataUrl, {}, function(r) {
                    parseResponse(r);
                    
                    // add default interval to last interval if not updated or reset to default if so
                    if(!updated) {
                        currentInterval += settings.interval;
                    } else {
                        currentInterval = settings.interval;
                        if(settings.onUpdate) settings.onUpdate();
                    }
                    
                    // check new interval doesn't reach the defined maximum
                    if(settings.maxInterval < currentInterval) {
                        currentInterval = settings.maxInterval;
                    }
                    
                    if(isStarted) {
                        window.clearTimeout(updateInterval);
                        if($(liveWidget).closest('body').length) {
                            updateInterval = window.setTimeout(update, currentInterval);
                        }
                    }
                }));
            };
            
            /**
             * Parses the given response and updates the widget if newer content is available
             */
            function parseResponse(data) {
                if(!data || !data.length) {
                    updated = false;
                    return;
                }
                
                var items = $('li', $(data));
                for(var i=items.length;i--;){
                    parseItem(items[i]);
                }
            };
            
            /**
             * Parses the given item and updates or adds an entry to the list
             * 
             * @param item to parse
             */
            function parseItem(item) {
                var visitId = $(item).attr('id');
                if($('#'+visitId, liveWidget).length) {
                    if($('#'+visitId, liveWidget).html() != $(item).html()) {
                        updated = true;
                    }
                    $('#'+visitId, liveWidget).remove();
                    $(liveWidget).prepend(item);
                } else {
                    updated = true;
                    $(item).hide();
                    $(liveWidget).prepend(item);
                    $(item).fadeIn(settings.fadeInSpeed);
                }
                // remove rows if there are more than the maximum
                $('li:gt('+(settings.maxRows-1)+')', liveWidget).remove();
            };
            
            /**
             * Constructor
             * 
             * @param object userSettings Settings to be used
             * @return void
             */
            this.construct = function(userSettings) {
                settings = jQuery.extend(settings, userSettings);
                
                if(!settings.dataUrl) {
                    console && console.error('liveWidget error: dataUrl needs to be defined in settings.');
                    return;
                }
                
                liveWidget = this;
                
                currentInterval = settings.interval;
                
                updateInterval = window.setTimeout(update, currentInterval);
            };
            
            /**
             * Triggers an update for the widget
             * 
             * @return void
             */
            this.update = function() {
                update();
            };
            
            /**
             * Starts the automatic update cycle
             */
            this.start = function() {
                isStarted = true;
                currentInterval = 0;
                update();
            };
            
            /**
             * Stops the automatic update cycle
             */
            this.stop = function() {
                isStarted = false;
                window.clearTimeout(updateInterval);
            };
            
            /**
             * Set the interval for refresh
             */
            this.setInterval = function(interval) {
                currentInterval = interval;
            };
        }
    });
    
    /**
     * Makes liveWidget available with $().liveWidget()
     */
    $.fn.extend({
        liveWidget: $.liveWidget.construct
    });
})(jQuery);


var pauseImage = "plugins/Live/templates/images/pause.gif";
var pauseDisabledImage = "plugins/Live/templates/images/pause_disabled.gif";
var playImage = "plugins/Live/templates/images/play.gif";
var playDisabledImage = "plugins/Live/templates/images/play_disabled.gif";
function onClickPause()
{
	$('#pauseImage').attr('src', pauseImage);
	$('#playImage').attr('src', playDisabledImage);
	return $.liveWidget.stop();
}
function onClickPlay()
{
	$('#playImage').attr('src', playImage);
	$('#pauseImage').attr('src', pauseDisabledImage);
	return $.liveWidget.start();
}
