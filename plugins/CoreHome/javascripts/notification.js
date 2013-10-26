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
            return;
        }

        var template = '<div class="notification notification-' + options.context + ' ">';

        if (!options.noclear) {

            template += '<button type="button" class="close" data-dismiss="alert">&times;</button>';
        }

        if (options.title) {
            template += '<strong>Warning!</strong> ';
        }

        template += message;

        template += '</div>';
        $(template).appendTo('#notificationContainer');
    };

    exports.Notification = Notification;

})(jQuery, require);