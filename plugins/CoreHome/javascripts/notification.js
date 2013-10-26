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

        var template = '<div class="notification';

        if (options.context) {
            template += ' notification-' + options.context;
        }

        template += '"';

        if (options.id) {
            template += ' data-id="' + options.id + '"';
        }

        template += '>';

        if (!options.noclear) {

            template += '<button type="button" class="close" data-dismiss="alert">&times;</button>';
        }

        if (options.title) {
            template += '<strong>Warning!</strong> ';
        }

        template += message;
        template += '</div>';

        var notificationNode = $(template).appendTo('#notificationContainer');

        if (!options.noclear) {
            addCloseEvent(notificationNode);
        }

        if ('toast' == options.type) {
            addToastEvent(notificationNode);
        }
    };

    exports.Notification = Notification;

    function addToastEvent(notificationNode)
    {
        setTimeout(function () {
            notificationNode.fadeOut( 'slow', function() {
                notificationNode.remove();
            });
        }, 15 * 1000);
    }

    function addCloseEvent(notificationNode) {
        $(notificationNode).on('click', '.close', function (event) {
            if (event.delegateTarget) {
                $(event.delegateTarget).remove();
            }
        });
    };

})(jQuery, require);