/**
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    var exports = require('piwik/UI');

    var Notification = function () {
    };

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

        var template = buildNotificationStart(options);

        if (!options.noclear) {
            template += buildClearButton();
        }

        if (options.title) {
            template += buildTitle(options);
        }

        template += message;
        template += buildNotificationEnd();

        var $notificationNode = $(template).appendTo('#notificationContainer').hide();
        $(options.placeAt || '#notificationContainer').append($notificationNode);
        $notificationNode.fadeIn(1000);

        if ('persistent' == options.type) {
            addPersistentEvent($notificationNode);
        }

        if ('toast' == options.type) {
            addToastEvent($notificationNode);
        }

        if (!options.noclear) {
            addCloseEvent($notificationNode);
        }
    };

    exports.Notification = Notification;


    function buildNotificationStart(options) {
        var template = '<div class="notification';

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

    function addToastEvent($notificationNode)
    {
        setTimeout(function () {
            $notificationNode.fadeOut( 'slow', function() {
                $notificationNode.remove();
                $notificationNode = null;
            });
        }, 15 * 1000);
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