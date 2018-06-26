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
            // TODO
        },
        controller: UsersManagerController
    });

    UsersManagerController.$inject = [];

    function UsersManagerController() {
        var vm = this;
        // TODO
    }
})();
