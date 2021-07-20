/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-content-block>
 */
(function () {
    angular.module('piwikApp').directive('piwikContentBlock', piwikContentBlock);

    piwikContentBlock.$inject = ['$timeout'];

    function piwikContentBlock($timeout){

        return {
            restrict: 'A',
            transclude: true,
            scope: {
                contentTitle: '@',
                feature: '@',
                helpUrl: '@',
                helpText: '@',
                anchor: '@?'
            },
            template: '<matomo-content-block content-title="{{ contentTitle }}" feature="{{ feature }}" ' +
                'help-url="{{ helpUrl }}" help-text="{{ helpText }}" anchor="{{ anchor }}" ng-transclude></matomo-content-block>',
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
