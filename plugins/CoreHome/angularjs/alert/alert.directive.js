/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-alert>
 */
(function () {
    angular.module('piwikApp').directive('piwikAlert', piwikAlert);

    piwikAlert.$inject = ['$timeout'];

    function piwikAlert($timeout){

        return {
            restrict: 'A',
            transclude: true,
            scope: {severity: '@piwikAlert'},
            template: '<alert severity="{{ severity }}" ng-transclude></alert>',
            compile: function(element) {
                return {
                    post: function postLink( scope, element, attrs ) {
                        $timeout(function(){
                            matomo.createVue(element[0])
                        });
                    }
                }
            },
        };
    }
})();