/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    // Keeps the plugin descriptions the same height
    $('.marketplace .plugin .description').dotdotdot({
        after: 'a.more',
        watch: 'window'
    });

    $('.installAllPaidPlugins').click(function (event) {
        event.preventDefault();

        piwikHelper.modalConfirm('#installAllPaidPluginsAtOnce');
    });

    function syncMaxHeight (selector) {

        if (!selector) {
            return;
        }

        var $nodes = $(selector);

        if (!$nodes) {
            return;
        }

        var max = {};
        $nodes.each(function (index, node) {
            var $node = $(node);
            var top   = $node.position().top;

            var height = $node.height();

            if (!max[top]) {
                max[top] = height;
            } else if (max[top] < height) {
                max[top] = height;
            } else {
                $node.height(max[top] + 'px');
            }
        });

        $nodes.each(function (index, node) {
            var $node = $(node);
            var top   = $node.position().top;

            $node.height(max[top] + 'px');
        });
    }

    syncMaxHeight('.marketplace .plugin .metadata')
    syncMaxHeight('.marketplace .plugin .panel-footer');

    $('a.plugin-details[data-pluginName]').on('click', function (event) {
        event.preventDefault();

        var pluginName = $(this).attr('data-pluginName');
        if (!pluginName) {
            return;
        }

        var activeTab = $(this).attr('data-activePluginTab');
        if (activeTab) {
            pluginName += '!' + activeTab;
        }

        broadcast.propagateNewPopoverParameter('browsePluginDetail', pluginName);
    });

    broadcast.addPopoverHandler('browsePluginDetail', function (value) {
        var pluginName = value;
        var activeTab  = null;

        if (-1 !== value.indexOf('!')) {
            activeTab  = value.substr(value.indexOf('!') + 1);
            pluginName = value.substr(0, value.indexOf('!'));
        }

        var url = 'module=Marketplace&action=pluginDetails&pluginName=' + encodeURIComponent(pluginName);

        if (activeTab) {
            url += '&activeTab=' + encodeURIComponent(activeTab);
        }

        Piwik_Popover.createPopupAndLoadUrl(url, 'details');
    });

});
