/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
                // BC for materializecss 0.97 => 1.0
                if (!element.attr('data-target')
                    && element.attr('data-activates')
                ) {
                    element.attr('data-target', element.attr('data-activates'));
                }

                if (element.attr('data-target') && $('#' + element.attr('data-target')).length) {
                    $(element).dropdown({
                        inDuration: 300,
                        outDuration: 225,
                        constrainWidth: false, // Does not change width of dropdown to that of the activator
                        //  hover: true, // Activate on hover
                        belowOrigin: true // Displays dropdown below the button
                    });
                }

                return function (scope, element, attrs) {

                };
            }
        };
    }
})();