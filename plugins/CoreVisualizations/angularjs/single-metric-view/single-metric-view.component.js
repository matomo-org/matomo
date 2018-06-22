/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <piwik-single-metric-view>
 */
(function () {
    angular.module('piwikApp').component('piwikSingleMetricView', {
        templateUrl: 'plugins/CoreVisualizations/angularjs/single-metric-view/single-metric-view.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            // showAllSitesItem: '<'
        },
        controller: SingleMetricViewController
    });

    SingleMetricViewController.$inject = [];

    function SingleMetricViewController() {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var vm = this;
        vm.myProperty  = 'component';
        vm.doSomething = doSomething;

        function doSomething() {

        }
    }
})();
