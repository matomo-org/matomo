var Piwik_Overlay_Client = (function () {

    /** jQuery */
    var $;

    /** Url of the Piwik root */
    var piwikRoot;

    /** Piwik idsite */
    var idSite;

    /** The current period and date */
    var period, date;

    /** Reference to the status bar DOM element */
    var statusBar;

    /** Load the client CSS */
    function loadCss() {
        var css = c('link').attr({
            rel: 'stylesheet',
            type: 'text/css',
            href: piwikRoot + 'plugins/Overlay/client/client.css'
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
            Piwik_Overlay_Client.loadScript('libs/bower_components/jquery/dist/jquery.min.js', function () {
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
                src: piwikRoot + 'index.php?module=Overlay&action=notifyParentIframe#' + window.location.href
            }).css({width: 0, height: 0, border: 0});

            // in some cases, calling append right away doesn't work in IE8
            $(document).ready(function () {
                $('body').append(iframe);
            });
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

    /** Special treatment for some internet explorers */
    var ieStatusBarEventsBound = false;

    function handleIEStatusBar() {
        if (navigator.appVersion.indexOf("MSIE 7.") == -1
            && navigator.appVersion.indexOf("MSIE 8.") == -1) {
            // this is not IE8 or lower
            return;
        }

        // IE7/8 can't handle position:fixed so we need to do it by hand
        statusBar.css({
            position: 'absolute',
            right: 'auto',
            bottom: 'auto',
            left: 0,
            top: 0
        });

        var position = function () {
            var scrollY = document.body.parentElement.scrollTop;
            var scrollX = document.body.parentElement.scrollLeft;
            statusBar.css({
                top: (scrollY + $(window).height() - statusBar.outerHeight()) + 'px',
                left: (scrollX + $(window).width() - statusBar.outerWidth()) + 'px'
            });
        };

        position();

        statusBar.css({width: 'auto'});
        if (statusBar.width() < 350) {
            statusBar.width(350);
        } else {
            statusBar.width(statusBar.width());
        }

        if (!ieStatusBarEventsBound) {
            ieStatusBarEventsBound = true;
            $(window).resize(position);
            $(window).scroll(position);
        }
    }

    return {

        /** Initialize in-site analytics */
        initialize: function (pPiwikRoot, pIdSite, pPeriod, pDate) {
            piwikRoot = pPiwikRoot;
            idSite = pIdSite;
            period = pPeriod;
            date = pDate;

            var load = this.loadScript;
            var loading = this.loadingNotification;

            loadJQuery(function () {
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

            script.src = piwikRoot + relativePath + '?v=1';
            head.appendChild(script);
        },

        /** Piwik Overlay API Request */
        api: function (method, callback, additionalParams) {
            var url = piwikRoot + 'index.php?module=API&method=Overlay.' + method
                + '&idSite=' + idSite + '&period=' + period + '&date=' + date + '&format=JSON&filter_limit=-1';

            if (additionalParams) {
                url += '&' + additionalParams;
            }

            $.getJSON(url + "&jsoncallback=?", function (data) {
                if (typeof data.result != 'undefined' && data.result == 'error') {
                    alert('Error: ' + data.message);
                }
                else {
                    callback(data);
                }
            });
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

            handleIEStatusBar();
            window.setTimeout(handleIEStatusBar, 100);

            return function () {
                item.remove();
                if (statusBar.children().size() == 0) {
                    statusBar.hide();
                } else {
                    handleIEStatusBar();
                }
            };
        },

        /** Hide all notifications with a certain class */
        hideNotifications: function (className) {
            statusBar.find('.PIS_' + className).remove();
            if (statusBar.children().size() == 0) {
                statusBar.hide();
            } else {
                handleIEStatusBar();
            }
        },

        /**
         * Initialize a loading notification
         * To hide the notification use the returned callback
         */
        loadingNotification: function (message) {
            return Piwik_Overlay_Client.notification(message, 'Loading');
        }

    };

})();
