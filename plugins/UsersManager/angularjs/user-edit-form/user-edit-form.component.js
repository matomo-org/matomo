/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <piwik-user-edit-form>
 */
(function () {
    angular.module('piwikApp').component('piwikUserEditForm', {
        templateUrl: 'plugins/UsersManager/angularjs/user-edit-form/user-edit-form.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            onDoneEditing: '&'
        },
        controller: UserEditFormController
    });

    UserEditFormController.$inject = [];

    function UserEditFormController() {
        var vm = this;
        vm.user = {};
        // TODO
    }
})();
