/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Renders a widget that is a container widget having the layout "ByDimension".
 *
 * The "ByDimension" layout shows a menu on the left letting you choose any widgets within this container. The
 * currently selected widget is shown on the right.
 *
 * @param {Object} piwikWidgetByDimensionContainer a widget object as returned by the WidgetMetadata API.
 *
 * Example:
 * <div piwik-widget-by-dimension-container="containerWidget"></div>
 */
(function () {
    angular.module('piwikApp').directive('piwikWidgetByDimensionContainer', piwikWidgetContainer);

    piwikWidgetContainer.$inject = ['piwik', '$filter'];

    function piwikWidgetContainer(piwik, $filter){
        return {
            restrict: 'A',
            scope: {
                container: '=piwikWidgetByDimensionContainer'
            },
            templateUrl: 'plugins/CoreHome/angularjs/widget-bydimension-container/widget-bydimension-container.directive.html?cb=' + piwik.cacheBuster,
            compile: function (element, attrs) {

                return function (scope, element, attrs, ngModel) {

                    var widgetsSorted = $filter('orderBy')(scope.container.widgets, 'order');
                    var widgetsByCategory = {};

                    angular.forEach(widgetsSorted, function (widget) {
                        var category = widget.subcategory.name;

                        if (!widgetsByCategory[category]) {
                            widgetsByCategory[category] = {name: category, order: widget.order, widgets: []};
                        }

                        widgetsByCategory[category].widgets.push(widget);
                    });

                    // only an array can be sorted
                    var finalWidgetsByCategory = [];
                    angular.forEach(widgetsByCategory, function (category) {
                        finalWidgetsByCategory.push(category);
                    });

                    scope.widgetsByCategory = $filter('orderBy')(finalWidgetsByCategory, 'order');

                    scope.selectWidget = function (widget) {
                        scope.selectedWidget = angular.copy(widget); // we copy to force rerender
                    }

                    if (widgetsSorted && widgetsSorted.length) {
                        scope.selectWidget(widgetsSorted[0]);
                    }
                };
            }
        };
    }
})();