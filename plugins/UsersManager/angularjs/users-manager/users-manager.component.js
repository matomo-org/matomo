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

    UsersManagerController.$inject = ['$element'];

    function UsersManagerController($element) {
        var vm = this;
        vm.isEditing = false;
        vm.user = {
            login: 'testuser',
            password: 'abcdefghijkl',
            alias: 'testalias',
            email: 'somewhere@something.com',
            token_auth: 'alsjfdlsdakjflsakdjf',
            superuser_access: 1,
            date_registered: '2018-01-23 03:45:45',
        };
        vm.$onInit = $onInit;
        vm.$onDestroy = $onDestroy;

        function $onInit() {
            // TODO: maybe this should go in another directive...
            $element.tooltip({
                track: true,
                content: function() {
                    var title = $(this).attr('title');
                    return piwikHelper.escape(title.replace(/\n/g, '<br />'));
                },
                show: false,
                hide: false
            });
        }

        function $onDestroy() {
            try {
                $element.tooltip('destroy');
            } catch (e) {
                // empty
            }
        }
    }
})();
