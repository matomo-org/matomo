/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <piwik-users-manager>
 */
(function () {
    angular.module('piwikApp').component('piwikUsersManager', {
        templateUrl: 'plugins/UsersManager/angularjs/users-manager/users-manager.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            // showAllSitesItem: '<'
        },
        controller: UsersManagerController
    });

    UsersManagerController.$inject = [];

    function UsersManagerController() {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var vm = this;
        vm.myProperty  = 'component';
        vm.doSomething = doSomething;

        function doSomething() {

        }
    }
})();
