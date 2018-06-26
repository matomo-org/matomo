/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <piwik-user-list-filters>
 */
(function () {
    angular.module('piwikApp').component('piwikUserListFilters', {
        templateUrl: 'plugins/UsersManager/angularjs/user-list-filters/user-list-filters.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            // showAllSitesItem: '<'
        },
        controller: UserListFiltersController
    });

    UserListFiltersController.$inject = [];

    function UserListFiltersController() {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var vm = this;
        vm.myProperty  = 'component';
        vm.doSomething = doSomething;

        function doSomething() {

        }
    }
})();
