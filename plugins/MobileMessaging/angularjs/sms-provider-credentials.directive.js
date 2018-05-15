/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div sms-provider-credentials provider="providername">
 */
(function () {
    angular.module('piwikApp').directive('smsProviderCredentials', smsProviderCredentials);

    function smsProviderCredentials() {

        return {
            restrict: 'A',
            require:"^ngModel",
            transclude: true,
            scope: {
                provider: '=',
                credentials: '=value'
            },
            template: '<ng-include src="getTemplateUrl()"/>',
            controllerAs: 'ProviderCredentials',
            controller: function($scope) {
                $scope.getTemplateUrl = function() {
                    return '?module=MobileMessaging&action=getCredentialFields&provider=' + $scope.provider;
                };
            },
            link: function(scope, elm, attrs, ctrl) {
                if (!ctrl) {
                    return;
                }

                // view -> model
                scope.$watch('credentials', function (val, oldVal) {
                    ctrl.$setViewValue(JSON.stringify(val));
                }, true);

                // unset credentials when new provider is shoosen
                scope.$watch('provider', function (val, oldVal) {
                    if(val != oldVal) {
                        scope.credentials = {};
                    }
                }, true);
            }
        };
    }
})();