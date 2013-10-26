/**
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {
    var $notificationNodes = $('[data-role="notification"]');

    $notificationNodes.each(function (index, notificationNode) {
        $notificationNode = $(notificationNode);
        var attributes = $notificationNode.data();

        // Notification.notify(attributes.title, attributes.message, attributes);
    });

});