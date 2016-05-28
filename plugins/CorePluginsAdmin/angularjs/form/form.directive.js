/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-form>
 */
(function () {
    angular.module('piwikApp').directive('piwikForm', piwikForm);

    piwikForm.$inject = ['piwik', '$timeout'];

    function piwikForm(piwik, $timeout){

        return {
            restrict: 'A',
            priority: '10',
            compile: function (element, attrs) {

                return function (scope, element, attrs) {

                    $timeout(function () {

                        element.find('input[type=text]').keypress(function (e) {
                            var key = e.keyCode || e.which;
                            if (key == 13) {
                                element.find('[piwik-save-button] input').triggerHandler('click');
                            }
                        });
                    });
                };
            }
        };
    }
})();