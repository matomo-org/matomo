/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    $('#plugins .uninstall').click(function (event) {
        event.preventDefault();

        var link = $(this).attr('href');
        
        if (!link) {
            return;
        }

        piwikHelper.modalConfirm('#confirmUninstallPlugin', {
            yes: function () {
                window.location = link;
            }
        });
    });

});