/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-marketplace>
 */
(function () {

    angular.module('piwikApp').directive('piwikMarketplace', piwikMarketplace);

    piwikMarketplace.$inject = ['piwik', '$timeout'];

    function piwikMarketplace(piwik, $timeout){

        return {
            restrict: 'A',
            compile: function (element, attrs) {

                return function (scope, element, attrs) {


                    });
                };
            }
        };
    }
})();
