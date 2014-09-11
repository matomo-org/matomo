/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp').factory('rateFeatureModel', function (piwikApi) {

    var model = {};

    model.sendFeedbackForFeature = function (featureName, like, message) {
        return piwikApi.fetch({
            method: 'Feedback.sendFeedbackForFeature',
            featureName: featureName,
            like: like ? '1' : '0',
            message: message + ''
        });
    };

    return model;
});
