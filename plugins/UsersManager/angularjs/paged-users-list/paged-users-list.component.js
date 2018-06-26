/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <piwik-paged-users-list>
 */
(function () {
    angular.module('piwikApp').component('piwikPagedUsersList', {
        templateUrl: 'plugins/UsersManager/angularjs/paged-users-list/paged-users-list.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            // showAllSitesItem: '<'
        },
        controller: PagedUsersListController
    });

    PagedUsersListController.$inject = [];

    function PagedUsersListController() {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var vm = this;
        vm.myProperty  = 'component';
        vm.doSomething = doSomething;

        function doSomething() {

        }
    }
})();
