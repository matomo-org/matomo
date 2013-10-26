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

    Notification.prototype.notify = function (title, message, options) {
        var template = '<div class="notification notification-' + options.context + ' ">' + options.type + '</div>';
        $(template).appendTo('#notificationContainer');
    };

    exports.Notification = Notification;

})(jQuery, require);