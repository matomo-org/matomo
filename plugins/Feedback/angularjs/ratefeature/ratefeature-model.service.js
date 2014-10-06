/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    angular.module('piwikApp').factory('rateFeatureModel', rateFeatureModel);

    rateFeatureModel.$inject = ['piwikApi'];

    function rateFeatureModel(piwikApi) {

        return {
            sendFeedbackForFeature: sendFeedbackForFeature
        };

        function sendFeedbackForFeature (featureName, like, message) {
            return piwikApi.fetch({
                method: 'Feedback.sendFeedbackForFeature',
                featureName: featureName,
                like: like ? '1' : '0',
                message: message + ''
            });
        }

    }
})();
