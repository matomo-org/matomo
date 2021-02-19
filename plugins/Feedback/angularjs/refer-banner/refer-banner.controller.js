/*!
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ReferBannerController', ReferBannerController);

    ReferBannerController.$inject = ['$scope', '$timeout', 'piwikApi'];

    function ReferBannerController($scope, $timeout, piwikApi) {

        // var saveNextReminder = function(nextReminder) {
        //     var ajaxHandler = new ajaxHelper();
        //     ajaxHandler.addParams({'module': 'Feedback', 'action': 'updateFeedbackReminderDate'}, 'GET');
        //     ajaxHandler.addParams({'nextReminder': nextReminder}, 'POST');
        //     ajaxHandler.send();
        // };

        // var remindMeLater = function() {
        //     saveNextReminder(90);
        // };

        // var dontShowAgain = function() {
        //     saveNextReminder(-1);
        // };

        var init = function() {
            $scope.referBanner.show = false;

            if ($scope.promptForRefer === 1) {
                $scope.referBanner.show = true;
                // $scope.feedbackPopup.remindMeLater = remindMeLater;
                // $scope.feedbackPopup.dontShowAgain = dontShowAgain;
            };
        };

        init();
    }
})();