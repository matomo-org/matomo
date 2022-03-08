/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ManageGoalsController', ManageGoalsController);

    ManageGoalsController.$inject = ['piwik', 'piwikApi', '$timeout', '$location', 'reportingMenuModel', '$rootScope'];

    function ManageGoalsController(piwik, piwikApi, $timeout, $location, reportingMenuModel, $rootScope) {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var self = this;

        if (!this.goal) {
            this.goal = {};
        }
        this.showEditGoal = false;
        this.showGoalList = true;



        this.showListOfReports = function (shouldScrollToTop) {
            $rootScope.$emit('Goals.cancelForm');

            this.showGoalList = true;
            this.showEditGoal = false;
            scrollToTop();
        };

        this.showListOfReports(false);
    }
})();
