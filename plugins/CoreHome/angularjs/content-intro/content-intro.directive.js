/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-content-block>
 */
(function () {
    angular.module('piwikApp').directive('piwikContentIntro', piwikContentIntro);

    piwikContentIntro.$inject = ['piwik'];

    function piwikContentIntro(piwik){

        return {
            restrict: 'A',
            compile: function (element, attrs) {
                element.addClass('piwik-content-intro');
            }
        };
    }
})();