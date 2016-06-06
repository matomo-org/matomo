/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    $('.uploadPlugin').click(function (event) {
        event.preventDefault();

        piwikHelper.modalConfirm('#installPluginByUpload', {
            yes: function () {
                window.location = link;
            }
        });
    });

    $('#uploadPluginForm').submit(function (event) {

        var $zipFile = $('[name=pluginZip]');
        var fileName = $zipFile.val();

        if (!fileName || '.zip' != fileName.slice(-4)) {
            event.preventDefault();
            alert(_pk_translate('CorePluginsAdmin_NoZipFileSelected'));
        }
    });

});