/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Directive for an element (such as a link) that creates and/or closes dialogs.
 *
 * Usage:
 * <a piwik-dialogtoggler href="#" ng-click="open(...)" />
 *
 * or:
 *
 * <div piwik-dialogtoggler>
 *     <a href="#" ng-click="open(...)">Open</a>
 *     <a href="#" ng-click="close()">Close</a>
 * </div>
 */
(function () {
    angular.module('piwikApp').directive('piwikDialogtoggler', piwikDialogtoggler);

    function piwikDialogtoggler() {
        return {
            restrict: 'A',
            controller: 'DialogTogglerController'
        };
    }
})();