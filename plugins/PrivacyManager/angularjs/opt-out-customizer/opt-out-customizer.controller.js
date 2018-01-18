/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('OptOutCustomizerController', OptOutCustomizerController);

    OptOutCustomizerController.$inject = ["$scope"];

    function OptOutCustomizerController($scope) {
        var vm = this;
        vm.piwikurl = $scope.piwikurl;
        vm.language = $scope.language;
        vm.updateFontSize = function () {
            if (vm.fontSize) {
                vm.fontSizeWithUnit = vm.fontSize + "px";
            } else {
                vm.fontSizeWithUnit = "";
            }
        };
    }
})();