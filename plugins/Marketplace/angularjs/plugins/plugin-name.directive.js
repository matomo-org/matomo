/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-plugin-name="MyPluginName" [data-activeplugintab="changelog"]>
 */
(function () {

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

    angular.module('piwikApp').directive('piwikPluginName', piwikPluginName);

    piwikPluginName.$inject = ['piwik'];

    function piwikPluginName(piwik){

        return {
            restrict: 'A',
            compile: function (element, attrs) {

                return function (scope, element, attrs) {

                    var pluginName = attrs.piwikPluginName;
                    var activeTab = attrs.activeplugintab;

                    if (!pluginName) {
                        return;
                    }

                    element.on('click', function (event) {
                        event.preventDefault();

                        if (activeTab) {
                            pluginName += '!' + activeTab;
                        }

                        broadcast.propagateNewPopoverParameter('browsePluginDetail', pluginName);
                    });

                };
            }
        };
    }
})();