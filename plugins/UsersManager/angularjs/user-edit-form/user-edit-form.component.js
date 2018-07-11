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
            onDoneEditing: '&',
            allowSuperuserEdit: '<'
        },
        controller: UserEditFormController
    });

    UserEditFormController.$inject = ['$element', 'piwikApi'];

    function UserEditFormController($element, piwikApi) {
        var vm = this;
        vm.activeTab = 'basic';
        vm.permissionsForIdSite = 1;
        vm.isSavingUserInfo = false;
        vm.isPasswordChanged = false;

        vm.$onInit = $onInit;
        vm.$onChanges = $onChanges;
        vm.confirmSuperUserChange = confirmSuperUserChange;
        vm.getFormTitle = getFormTitle;
        vm.getSaveButtonLabel = getSaveButtonLabel;
        vm.toggleSuperuserAccess = toggleSuperuserAccess;
        vm.saveUserInfo = saveUserInfo;

        function $onInit() {
            if (vm.user) {
                vm.isAdd = false;
            } else {
                vm.isAdd = true;
                vm.user = {};
            }
        }

        function $onChanges() {
            if (!vm.isAdd) {
                vm.user.password = 'XXXXXXXX'; // make sure password is not stored in the client after update/save
            }
        }

        function getFormTitle() {
            return vm.isAdd ? 'Add New User' : 'Edit User';
        }

        function getSaveButtonLabel() {
            return vm.isAdd ? 'Create User' : 'Save Basic Info';
        }

        function confirmSuperUserChange() {
            $element.find('.superuser-confirm-modal').openModal();
        }

        function toggleSuperuserAccess() {
            alert('toggle superuser access'); // TODO
        }

        function saveUserInfo() {
            if (vm.isAdd) {
                createUser();
            } else {
                updateUser();
            }
        }

        function createUser() {
            vm.isSavingUserInfo = true;
            piwikApi.post({
                method: 'UsersManager.addUser',
                userLogin: vm.user.login,
                password: vm.user.password,
                email: vm.user.email,
                alias: vm.user.alias
            }).catch(function () {
                // ignore (error is still displayed to user)
            }).then(function () {
                vm.isSavingUserInfo = false;
                vm.isAdd = false;
                vm.isPasswordChanged = false;
            });
        }

        function updateUser() {
            vm.isSavingUserInfo = true;
            piwikApi.post({
                method: 'UsersManager.updateUser',
                userLogin: vm.user.login,
                password: vm.isPasswordChanged ? vm.user.password : undefined,
                email: vm.user.email,
                alias: vm.user.alias
            }).catch(function () {
                // ignore (error is still displayed to user)
            }).then(function () {
                vm.isSavingUserInfo = false;
                vm.isPasswordChanged = false;
            });
        }
    }
})();
