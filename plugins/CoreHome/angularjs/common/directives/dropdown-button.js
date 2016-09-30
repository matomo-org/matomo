/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-dropdown-button>
 */
(function () {
    angular.module('piwikApp.directive').directive('dropdownButton', piwikDropdownButton);

    piwikDropdownButton.$inject = ['piwik'];

    function piwikDropdownButton(piwik){

        return {
            restrict: 'C',
            compile: function (element, attrs) {

                $(element).dropdown({
                    inDuration: 300,
                    outDuration: 225,
                    constrain_width: false, // Does not change width of dropdown to that of the activator
                    //  hover: true, // Activate on hover
                    belowOrigin: true // Displays dropdown below the button
                });

                return function (scope, element, attrs) {

                };
            }
        };
    }
})();