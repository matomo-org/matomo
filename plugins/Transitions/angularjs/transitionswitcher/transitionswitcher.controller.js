/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Controller to save archiving settings
 */
(function () {
    angular.module('piwikApp').controller('TransitionSwitcherController', TransitionSwitcherController);

    TransitionSwitcherController.$inject = ['$scope', 'piwikApi'];

    function TransitionSwitcherController($scope, piwikApi) {

        this.onActionChange = function (actionName) {
            var transitions = new Piwik_Transitions('url', actionName, null, '');
            transitions.reset('url', actionName, '');
            transitions.showPopover(true);
        };
    }
})();