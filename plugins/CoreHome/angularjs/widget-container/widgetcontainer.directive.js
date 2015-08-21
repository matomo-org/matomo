/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Renders a widget that is a container widget having no specific layout (which is the default).
 *
 * It shows all widgets vertically aligned one widget after another.
 *
 * @param {Object} piwikWidgetContainer a widget object as returned by the WidgetMetadata API.
 *
 * Example:
 * <div piwik-widget-container="containerWidget"></div>
 */
(function () {
    angular.module('piwikApp').directive('piwikWidgetContainer', piwikWidgetContainer);

    piwikWidgetContainer.$inject = ['piwik'];

    function piwikWidgetContainer(piwik){
        return {
            restrict: 'A',
            scope: {
                container: '=piwikWidgetContainer'
            },
            templateUrl: 'plugins/CoreHome/angularjs/widget-container/widgetcontainer.directive.html?cb=' + piwik.cacheBuster
        };
    }
})();