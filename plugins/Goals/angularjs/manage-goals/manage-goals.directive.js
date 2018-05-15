/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/**
 * Usage:
 * <div piwik-manage-goals>
 */
(function () {
    angular.module('piwikApp').directive('piwikManageGoals', piwikManageGoals);

    piwikManageGoals.$inject = ['piwik'];

    function piwikManageGoals(piwik){

        return {
            restrict: 'A',
            priority: 10,
            controller: 'ManageGoalsController',
            controllerAs: 'manageGoals',
            compile: function (element, attrs) {

                return function (scope, element, attrs, controller) {
                    if (attrs.showAddGoal) {
                        controller.createGoal();
                    } else if (attrs.showGoal) {
                        controller.editGoal(attrs.showGoal);
                    }

                };
            }
        };
    }
})();