/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('RateFeatureController', RateFeatureController);

    RateFeatureController.$inject = ['$scope', 'rateFeatureModel'];

    function RateFeatureController($scope, model){

        var vm = this;
        vm.title          = $scope.title;
        vm.dislikeFeature = dislikeFeature;
        vm.likeFeature    = likeFeature;
        vm.sendFeedback   = sendFeedback;

        function dislikeFeature () {
            vm.like = false;
        }

        function likeFeature () {
            vm.like = true;
        }

        function sendFeedback (message) {
            model.sendFeedbackForFeature(vm.title, vm.like, message);
            vm.ratingDone = true;
        }
    }
})();
