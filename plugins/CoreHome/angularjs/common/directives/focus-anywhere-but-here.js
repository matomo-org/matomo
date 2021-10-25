/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * The given expression will be executed when the user presses either escape or presses something outside
 * of this element
 *
 * Example:
 * <div piwik-focus-anywhere-but-here="closeDialog()">my dialog</div>
 */
(function () {
    angular.module('piwikApp.directive').directive('piwikFocusAnywhereButHere', piwikFocusAnywhereButHere);

    piwikFocusAnywhereButHere.$inject = ['$document'];

    function piwikFocusAnywhereButHere($document){
        return {
            restrict: 'A',
            link: function(scope, element, attr, ctrl) {



            }
        };
    }
})();
