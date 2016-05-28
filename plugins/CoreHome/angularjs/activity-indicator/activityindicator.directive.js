/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Shows a general loading message while [loading] is set to true.
 *
 * @param {Boolean} loading  If true, the activity indicator is shown, otherwise the indicator is hidden.
 *
 * Example:
 * <div piwik-activity-indicator loading-message="'My custom message'" loading="true|false"></div>
 */
(function () {
    angular.module('piwikApp').directive('piwikActivityIndicator', piwikActivityIndicator);

    piwikActivityIndicator.$inject = ['piwik'];

    function piwikActivityIndicator(piwik){

        return {
            restrict: 'A',
            transclude: true,
            scope: {
                loading: '=',
                loadingMessage: '=?'
            },
            templateUrl: 'plugins/CoreHome/angularjs/activity-indicator/activityindicator.html?cb=' + piwik.cacheBuster,
            compile: function (element, attrs) {

                return function (scope, element, attrs) {
                    if (!scope.loadingMessage) {
                        scope.loadingMessage = _pk_translate('General_LoadingData');
                    }

                };
            }
        };
    }
})();