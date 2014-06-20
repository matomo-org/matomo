/*!
 * Piwik - free/libre analytics platform
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
            } else {
                $node.height(max[top] + 'px');
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

    $('.pluginslist, #plugins, .themeslist').on('click', '[data-pluginName]', function (event) {
        if ($(event.target).hasClass('install') || $(event.target).hasClass('uninstall')) {
            return;
        }

        var pluginName = $(this).attr('data-pluginName');

        if (!pluginName) {
            return;
        }

        var activeTab = $(event.target).attr('data-activePluginTab');
        if (activeTab) {
            pluginName += '!' + activeTab;
        }

        broadcast.propagateNewPopoverParameter('browsePluginDetail', pluginName);
    });

    var showPopover = function (value) {
        var pluginName = value;
        var activeTab  = null;

        if (-1 !== value.indexOf('!')) {
            activeTab  = value.substr(value.indexOf('!') + 1);
            pluginName = value.substr(0, value.indexOf('!'));
        }

        var url = 'module=CorePluginsAdmin&action=pluginDetails&pluginName=' + encodeURIComponent(pluginName);

        if (activeTab) {
            url += '&activeTab=' + encodeURIComponent(activeTab);
        }

        Piwik_Popover.createPopupAndLoadUrl(url, 'details');
    };

    broadcast.addPopoverHandler('browsePluginDetail', showPopover);

});