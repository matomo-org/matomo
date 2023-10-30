/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    if (!piwik || !location.protocol) {
        return;
    }

    if (!piwik.hasSuperUserAccess) {
        // we show a potential notification only to super users
        return;
    }

    if (piwik.hasServerDetectedHttps) {
        // https was detected, not needed to show a message
        return;
    }

    var isHttpsUsed = 0 === location.protocol.indexOf('https');

    if (!isHttpsUsed) {
        // not using https anyway, we do not show a message
        return;
    }

    var params  = [
        '"config/config.ini.php"',
        '"assume_secure_protocol=1"',
        '"[General]"',
        '<a target="_blank" href="' + _pk_externalRawLink('https://matomo.org/faq/how-to-install/faq_98/') + '">',
        '</a>'
    ];
    var message = _pk_translate('CoreAdminHome_ProtocolNotDetectedCorrectly') + " " + _pk_translate('CoreAdminHome_ProtocolNotDetectedCorrectlySolution', params);

    var UI = require('piwik/UI');
    var notification = new UI.Notification();
    notification.show(message, {context: 'warning'});
});
