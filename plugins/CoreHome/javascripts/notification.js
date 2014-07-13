/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    var exports = require('piwik/UI');

    /**
     * Creates a new notifications.
     *
     * Example:
     * var UI = require('piwik/UI');
     * var notification = new UI.Notification();
     * notification.show('My Notification Message', {title: 'Low space', context: 'warning'});
     */
    var Notification = function () {
        this.$node = null;
    };

    /**
     * Makes the notification visible.
     *
     * @param    {string}  message    The actual message that will be displayed. Must be set.
     * @param    {Object}  [options]
     * @param    {string}  [options.id]         Only needed for persistent notifications. The id will be sent to the
     *                                          frontend once the user closes the notifications. The notification has to
     *                                          be registered/notified under this name
     * @param    {string}  [options.title]      The title of the notification. For instance the plugin name.
     * @param    {bool}    [options.animate=true]     If enabled, the notification will be faded in.
     * @param    {string}  [options.context=warning]  Context of the notification: 'info', 'warning', 'success' or
     *                                                'error'
     * @param    {string}  [options.type=transient]   The type of the notification: Either 'toast' or 'transitent'
     * @param    {bool}    [options.noclear=false]    If set, the close icon is not displayed.
     * @param    {object}  [options.style]            Optional style/css dictionary. For instance {'display': 'inline-block'}
     * @param    {string}  [options.placeat]          By default, the notification will be displayed in the "stats bar".
     *                                                You can specify any other CSS selector to place the notifications
     *                                                whereever you want.
     */
    Notification.prototype.show = function (message, options) {
        if (!message) {
            throw new Error('No message given, cannot display notification');
        }
        if (options && !$.isPlainObject(options)) {
            throw new Error('Options has the wrong format, cannot display notification');
        } else if (!options) {
            options = {};
        }

        if ('persistent' == options.type) {
            // otherwise it is never possible to dismiss the notification
            options.noclear = false;
        }

        closeExistingNotificationHavingSameIdIfNeeded(options);

        var template          = generateNotificationHtmlMarkup(options, message);
        var $notificationNode = placeNotification(template, options);
        this.$node = $notificationNode;

        if ('persistent' == options.type) {
            addPersistentEvent($notificationNode);
        } else if ('toast' == options.type) {
            addToastEvent($notificationNode);
        }

        if (!options.noclear) {
            addCloseEvent($notificationNode);
        }
    };

    Notification.prototype.scrollToNotification = function () {
        if (this.$node) {
            piwikHelper.lazyScrollTo(this.$node, 250);
        }
    };

    exports.Notification = Notification;

    function closeExistingNotificationHavingSameIdIfNeeded(options)
    {
        if (!options.id) {
            return;
        }

        var $existingNode = $('.system.notification[data-id=' + options.id + ']');
        if ($existingNode && $existingNode.length) {
            $existingNode.remove();
        }
    }

    function generateNotificationHtmlMarkup(options, message) {
        var template = buildNotificationStart(options);

        if (!options.noclear) {
            template += buildClearButton();
        }

        if (options.title) {
            template += buildTitle(options);
        }

        template += message;
        template += buildNotificationEnd();
        
        return template;
    }

    function buildNotificationStart(options) {
        var template = '<div class="notification system';

        if (options.context) {
            template += ' notification-' + options.context;
        }

        template += '"';

        if (options.id) {
            template += ' data-id="' + options.id + '"';
        }

        template += '>';

        return template;
    }

    function buildNotificationEnd() {
        return '</div>';
    }

    function buildClearButton() {
        return '<button type="button" class="close" data-dismiss="alert">&times;</button>';
    }

    function buildTitle(options) {
        return '<strong>' + options.title + '</strong> ';
    }

    function placeNotification(template, options) {

        var $notificationNode = $(template);

        if (options.style) {
            $notificationNode.css(options.style);
        }

        $notificationNode = $notificationNode.hide();
        $(options.placeat || '#notificationContainer').append($notificationNode);

        if (false === options.animate) {
            $notificationNode.show();
        } else {
            $notificationNode.fadeIn(1000);
        }

        return $notificationNode;
    }

    function addToastEvent($notificationNode)
    {
        setTimeout(function () {
            $notificationNode.fadeOut( 'slow', function() {
                $notificationNode.remove();
                $notificationNode = null;
            });
        }, 12 * 1000);
    }

    function addCloseEvent($notificationNode) {
        $notificationNode.on('click', '.close', function (event) {
            if (event && event.delegateTarget) {
                $(event.delegateTarget).remove();
            }
        });
    };

    function addPersistentEvent($notificationNode) {
        var notificationId = $notificationNode.data('id');

        if (!notificationId) {
            return;
        }

        $notificationNode.on('click', '.close', function (event) {
            var ajaxHandler = new ajaxHelper();
            ajaxHandler.addParams({
                module: 'CoreHome',
                action: 'markNotificationAsRead'
            }, 'GET');
            ajaxHandler.addParams({notificationId: notificationId}, 'POST');
            ajaxHandler.send(true);
        });
    };

})(jQuery, require);