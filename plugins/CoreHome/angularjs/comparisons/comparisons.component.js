/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    angular.module('piwikApp').component('piwikComparisons', {
        templateUrl: 'plugins/CoreHome/angularjs/comparisons/comparisons.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            // TODO
        },
        controller: ComparisonsController
    });

    ComparisonsController.$inject = ['piwikComparisonsService'];

    function ComparisonsController(comparisonsService) {
        var vm = this;

        vm.comparisonsService = comparisonsService;
        vm.$onInit = $onInit;
        vm.comparisonHasSegment = comparisonHasSegment;

        function $onInit() {
            vm.comparisons = comparisonsService.getComparisons(); // TODO: on change need to modify this...
        }

        function comparisonHasSegment(comparison) {
            return typeof comparison.params.segment !== 'undefined';
        }
    }
})();
