/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    function syncMaxHeight (selector) {

        if (!selector) {
            return;
        }

        var $nodes = $(selector);

        if (!$nodes) {
            return;
        }

        var max = {};
        $nodes.each(function (index, node) {
            var $node = $(node);
            var top   = $node.position().top;

            var height = $node.height();

            if (!max[top]) {
                max[top] = height;
            } else if (max[top] < height) {
                max[top] = height;
            }
        });

        $nodes.each(function (index, node) {
            var $node = $(node);
            var top   = $node.position().top;

            $node.height(max[top] + 'px');
        });
    }

    syncMaxHeight('.pluginslist .plugin');
    syncMaxHeight('.themeslist .plugin');

    $('.pluginslist').on('click', '.more', function (event) {
        var pluginName = $( this ).attr('data-pluginName');

        if (!pluginName) {
            return;
        }

        broadcast.propagateNewPopoverParameter('browsePluginDetail', pluginName);
    });

    $('.themeslist').on('click', '.more', function (event) {
        var themeName = $( this ).attr('data-pluginName');

        if (!themeName) {
            return;
        }

        broadcast.propagateNewPopoverParameter('browsePluginDetail', themeName);
    });

    var showPopover = function (pluginName) {
        var url = 'module=CorePluginsAdmin&action=pluginDetails&pluginName=' + encodeURIComponent(pluginName);
        Piwik_Popover.createPopupAndLoadUrl(url, 'details');
    };

    broadcast.addPopoverHandler('browsePluginDetail', showPopover);

});