/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    $('.pluginslist').on('click', '.header', function (event) {
        var pluginName = $( this ).text();
        var url = 'module=CorePluginsAdmin&action=pluginDetails&pluginName=' + pluginName;
        Piwik_Popover.createPopupAndLoadUrl(url, 'plugin details');
    });

    $('.themeslist').on('click', '.header', function (event) {
        var themeName = $( this ).text();
        var url = 'module=CorePluginsAdmin&action=themeDetails&pluginName=' + themeName;
        Piwik_Popover.createPopupAndLoadUrl(url, 'plugin details');
    });

});