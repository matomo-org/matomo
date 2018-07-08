/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <piwik-users-list-pagination>
 */
(function () {
    angular.module('piwikApp').component('piwikUsersListPagination', {
        templateUrl: 'plugins/UsersManager/angularjs/users-list-pagination/users-list-pagination.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            // showAllSitesItem: '<'
        },
        controller: UsersListPaginationController
    });

    UsersListPaginationController.$inject = [];

    function UsersListPaginationController() {
        var vm = this;
        // TODO
    }
})();
