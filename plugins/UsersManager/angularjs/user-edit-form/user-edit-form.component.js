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
            user: '<',
            isAdd: '<',
            onDoneEditing: '&',
            allowSuperuserEdit: '<'
        },
        controller: UserEditFormController
    });

    UserEditFormController.$inject = ['$element'];

    function UserEditFormController($element) {
        var vm = this;
        vm.activeTab = 'basic';
        vm.confirmSuperUserChange = confirmSuperUserChange;
        vm.getFormTitle = getFormTitle;
        vm.getSaveButtonLabel = getSaveButtonLabel;
        vm.toggleSuperuserAccess = toggleSuperuserAccess;

        function getFormTitle() {
            return vm.isAdd ? 'Add New User' : 'Edit User';
        }

        function getSaveButtonLabel() {
            return vm.isAdd ? 'Create User' : 'Save';
        }

        function confirmSuperUserChange() {
            $element.find('.superuser-confirm-modal').openModal();
        }

        function toggleSuperuserAccess() {
            alert('toggle superuser access');
        }
    }
})();
