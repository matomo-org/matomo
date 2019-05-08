/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('FeedbackPopupController', FeedbackPopupController);

    FeedbackPopupController.$inject = ['$scope', '$timeout'];

    function FeedbackPopupController($scope, $timeout) {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var saveNextReminder = function(nextReminder) {
            var request = new ajaxHelper();
            request.addParams({
                module: 'Feedback',
                action: 'updateFeedbackReminder',
                nextReminder: nextReminder
            }, 'GET');
            request.send();
        };

        var remindMeLater = function() {
            saveNextReminder(90);
        };

        var dontShowAgain = function() {
            debugger;
            saveNextReminder(-1);
        };

        var init = function() {
            if ($scope.promptForFeedback === 1) {
                $timeout(function() {
                    $scope.feedbackPopup.dialog = {};
                    $scope.feedbackPopup.dialog.show = true;
                    $scope.feedbackPopup.remindMeLater = remindMeLater;
                    $scope.feedbackPopup.dontShowAgain = dontShowAgain;
                });
            }
        };

        init();
    }
})();