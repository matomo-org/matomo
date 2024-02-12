var Matomo_Overlay_Client = (function () {

    var DOMAIN_PARSE_REGEX = /^http(s)?:\/\/(www\.)?([^\/]*)/i;

    /** jQuery */
    var $;

    /** Url of the Piwik root */
    var piwikRoot;

    /** protocol and domain of Piwik root */
    var piwikOrigin;

    /** Piwik idsite */
    var idSite;

    /** The current period and date */
    var period, date, segment;

    /** Reference to the status bar DOM element */
    var statusBar;

    /** Counter for request IDs for postMessage based API requests. */
    var lastRequestId = 0;

    /** Map of callbacks for postMessage based API requests. */
    var requestCallbacks = {};

    /** Load the client CSS */
    function loadCss() {
        var css = c('link').attr({
            rel: 'stylesheet',
            type: 'text/css',
            href: piwikRoot + '/plugins/Overlay/client/client.css'
        });
        $('head').append(css);
    }

    /**
     * This method loads jQuery, if it is not there yet.
     * The callback is triggered after jQuery is loaded.
     */
    function loadJQuery(callback) {
        if (typeof jQuery != 'undefined') {
            $ = jQuery;
            callback();
        }
        else {
            Matomo_Overlay_Client.loadScript('node_modules/jquery/dist/jquery.min.js', function () {
                $ = jQuery;
                jQuery.noConflict();
                callback();
            });
        }
    }

    /**
     * Notify Piwik of the current iframe location.
     * This way, we can display additional metrics on the side of the iframe.
     */
    function notifyPiwikOfLocation() {
        // check whether the session has been opened in a new tab (instead of an iframe)
        if (window != window.top) {
            var iframe = c('iframe', false, {
                src: piwikRoot + '/index.php?module=Overlay&action=notifyParentIframe#' + window.location.href
            }).css({width: 0, height: 0, border: 0});

            $('body').append(iframe);
        }
    }

    /** Create a jqueryfied DOM element */
    function c(tagName, className, attributes) {
        var el = $(document.createElement(tagName));

        if (className) {
            if (className.substring(0, 1) == '#') {
                var id = className.substring(1, className.length);
                id = 'PIS_' + id;
                el.attr('id', id);
            }
            else {
                className = 'PIS_' + className;
                el.addClass(className);
            }
        }

        if (attributes) {
            el.attr(attributes);
        }

        return el;
    }

    function nextRequestId() {
        var nextId = lastRequestId + 1;
        lastRequestId = nextId;
        return nextId;
    }

    function handlePostMessages() {
        window.addEventListener("message", function (event) {
            if (event.origin !== piwikOrigin) {
                return;
            }

            var strData = event.data.split(':', 3);
            if (strData[0] !== 'overlay.response') {
                return;
            }

            var requestId = strData[1];
            if (!requestCallbacks[requestId]) {
                return;
            }

            var callback = requestCallbacks[requestId];
            delete requestCallbacks[requestId];

            var data = JSON.parse(decodeURIComponent(strData[2]));
            if (typeof data.result !== 'undefined'
                && data.result === 'error'
            ) {
                alert('Error: ' + data.message);
            } else {
                callback(data);
            }
        }, false);
    }

    return {

        /** Initialize in-site analytics */
        initialize: function (pPiwikRoot, pIdSite, pPeriod, pDate, pSegment) {
            piwikRoot = pPiwikRoot;
            piwikOrigin = piwikRoot.match(DOMAIN_PARSE_REGEX)[0];
            idSite = pIdSite;
            period = pPeriod;
            date = pDate;
            segment = pSegment;

            var load = this.loadScript;
            var loading = this.loadingNotification;

            loadJQuery(function () {
                handlePostMessages();
                notifyPiwikOfLocation();
                loadCss();

                // translations
                load('plugins/Overlay/client/translations.js', function () {
                    Piwik_Overlay_Translations.initialize(function () {
                        // following pages
                        var finishPages = loading('Loading following pages');
                        load('plugins/Overlay/client/followingpages.js', function () {
                            Piwik_Overlay_FollowingPages.initialize(finishPages);
                        });

                    });
                });
            });
        },

        /** Create a jqueryfied DOM element */
        createElement: function (tagName, className, attributes) {
            return c(tagName, className, attributes);
        },

        /** Load a script and wait for it to be loaded */
        loadScript: function (relativePath, callback) {
            var loaded = false;
            var onLoad = function () {
                if (!loaded) {
                    loaded = true;
                    callback();
                }
            };

            var head = document.getElementsByTagName('head')[0];
            var script = document.createElement('script');
            script.type = 'text/javascript';

            script.onreadystatechange = function () {
                if (this.readyState == 'loaded' || this.readyState == 'complete') {
                    onLoad();
                }
            };
            script.onload = onLoad;

            script.src = piwikRoot + '/' + relativePath + '?v=1';
            head.appendChild(script);
        },

        /** Piwik Overlay API Request */
        api: function (method, callback, additionalParams) {
            var url = piwikRoot + '/index.php?module=API&method=' + method
                + '&idSite=' + idSite + '&period=' + period + '&date=' + date + '&format=JSON&filter_limit=-1';

            if (segment) {
                url += '&segment=' + segment;
            }

            if (additionalParams) {
                url += '&' + additionalParams;
            }

            var requestId = nextRequestId();
            requestCallbacks[requestId] = callback;

            var matomoFrame = window.parent;
            matomoFrame.postMessage('overlay.call:' + requestId + ':' + encodeURIComponent(url), piwikOrigin);
        },

        /**
         * Initialize a notification
         * To hide the notification use the returned callback
         */
        notification: function (message, addClass) {
            if (!statusBar) {
                statusBar = c('div', '#StatusBar').css('opacity', .8);
                $('body').prepend(statusBar);
            }

            var item = c('div', 'Item').html(message);

            if (addClass) {
                item.addClass('PIS_' + addClass);
            }

            statusBar.show().append(item);

            return function () {
                item.remove();
                if (!statusBar.children().length) {
                    statusBar.hide();
                }
            };
        },

        /** Hide all notifications with a certain class */
        hideNotifications: function (className) {
            statusBar.find('.PIS_' + className).remove();
            if (!statusBar.children().length) {
                statusBar.hide();
            }
        },

        /**
         * Initialize a loading notification
         * To hide the notification use the returned callback
         */
        loadingNotification: function (message) {
            return Matomo_Overlay_Client.notification(message, 'Loading');
        }

    };

})();
