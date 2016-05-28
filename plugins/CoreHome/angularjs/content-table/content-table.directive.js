/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-content-table>
 */
(function () {
    angular.module('piwikApp').directive('piwikContentTable', piwikContentTable);

    piwikContentTable.$inject = ['piwik'];

    function piwikContentTable(piwik){

        return {
            restrict: 'A',
            compile: function (element, attrs) {
                element.addClass('card card-table entityTable');

                return function (scope, element, attrs) {

                };
            }
        };
    }
})();