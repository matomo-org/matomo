/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp').controller('RateFeatureController', function($scope, rateFeatureModel, $filter){

    $scope.dislikeFeature = function () {
        $scope.like = false;
    };

    $scope.likeFeature = function () {
        $scope.like = true;
    };

    $scope.sendFeedback = function (message) {
        rateFeatureModel.sendFeedbackForFeature($scope.title, $scope.like, message);
        $scope.ratingDone = true;
        // alert($filter('translate')('Feedback_ThankYou'));
    };
});
