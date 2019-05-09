/*!
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-feedback-popup>
 */
(function () {
    angular.module('piwikApp').directive('piwikFeedbackPopup', piwikFeedbackPopup);

    piwikFeedbackPopup.$inject = ['piwik', '$timeout'];

    function piwikFeedbackPopup(piwik, $timeout){
        var defaults = {
            // showAllSitesItem: 'true'
        };

        return {
            restrict: 'A',
            scope: {
                promptForFeedback: '<'
            },
            templateUrl: 'plugins/Feedback/angularjs/feedback-popup/feedback-popup.directive.html?cb=' + piwik.cacheBuster,
            controller: 'FeedbackPopupController',
            controllerAs: 'feedbackPopup',
            compile: function (element, attrs) {

                for (var index in defaults) {
                    if (defaults.hasOwnProperty(index) && attrs[index] === undefined) {
                        attrs[index] = defaults[index];
                    }
                }

                return function (scope, element, attrs) {
                    // $timeout(function() {
                    //     scope.feedbackPopup.dialog = {};
                    //     scope.feedbackPopup.dialog.show = true;
                    // });
                };
            }
        };
    }
})();