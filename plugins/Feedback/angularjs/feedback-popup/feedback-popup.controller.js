/*!
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('FeedbackPopupController', FeedbackPopupController);

    FeedbackPopupController.$inject = ['$scope', '$timeout', 'piwikApi'];

    function FeedbackPopupController($scope, $timeout, piwikApi) {

        var saveNextReminder = function(nextReminder) {
            var ajaxHandler = new ajaxHelper();
            ajaxHandler.addParams({'module': 'Feedback', 'action': 'updateFeedbackReminderDate'}, 'GET');
            ajaxHandler.addParams({'nextReminder': nextReminder}, 'POST');
            ajaxHandler.send();
        };

        var remindMeLater = function() {
            saveNextReminder(90);
        };

        var dontShowAgain = function() {
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