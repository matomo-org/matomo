/**
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready((function ($, require) {
    return function () {

        var UI = require('piwik/UI');

        var $notificationNodes = $('[data-role="notification"]');

        $notificationNodes.each(function (index, notificationNode) {
            $notificationNode = $(notificationNode);
            var attributes = $notificationNode.data();

            var notification = new UI.Notification();
            notification.show(attributes.message, attributes);
        });

    }

})(jQuery, require));