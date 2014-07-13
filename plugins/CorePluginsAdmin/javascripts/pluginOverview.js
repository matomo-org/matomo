/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    var uninstallConfirmMessage = '';

    $('#plugins .uninstall').click(function (event) {
        event.preventDefault();

        var link = $(this).attr('href');
        var pluginName = $(this).attr('data-pluginName');

        if (!link || !pluginName) {
            return;
        }

        if (!uninstallConfirmMessage) {
            uninstallConfirmMessage = $('#uninstallPluginConfirm').text();
        }

        var messageToDisplay = uninstallConfirmMessage.replace('%s', pluginName);

        $('#uninstallPluginConfirm').text(messageToDisplay);

        piwikHelper.modalConfirm('#confirmUninstallPlugin', {
            yes: function () {
                window.location = link;
            }
        });
    });

});