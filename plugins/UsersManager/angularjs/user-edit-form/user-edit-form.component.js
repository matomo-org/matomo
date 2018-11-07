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
            currentUserRole: '<',
            accessLevels: '<',
            filterAccessLevels: '<',
            initialSiteId: '<',
            initialSiteName: '<'
        },
        controller: UserEditFormController
    });

    UserEditFormController.$inject = ['$element', 'piwikApi', '$q'];

    function UserEditFormController($element, piwikApi, $q) {
        var vm = this;
        vm.activeTab = 'basic';
        vm.permissionsForIdSite = 1;
        vm.isSavingUserInfo = false;
        vm.userHasAccess = true;
        vm.firstSiteAccess = null;
        vm.isUserModified = false;
        vm.passwordConfirmation = '';

        vm.$onInit = $onInit;
        vm.$onChanges = $onChanges;
        vm.confirmSuperUserChange = confirmSuperUserChange;
        vm.getFormTitle = getFormTitle;
        vm.getSaveButtonLabel = getSaveButtonLabel;
        vm.toggleSuperuserAccess = toggleSuperuserAccess;
        vm.saveUserInfo = saveUserInfo;
        vm.updateUser = updateUser;

        function $onInit() {
            vm.firstSiteAccess = {
                id: vm.initialSiteId,
                name: vm.initialSiteName
            };
        }

        function $onChanges() {
            if (vm.user) {
                vm.isAdd = false;
            } else {
                vm.isAdd = true;
                vm.user = {};
            }

            if (!vm.isAdd) {
                vm.user.password = 'XXXXXXXX'; // make sure password is not stored in the client after update/save
            }
        }

        function getFormTitle() {
            return vm.isAdd ? _pk_translate('UsersManager_AddNewUser') : _pk_translate('UsersManager_EditUser');
        }

        function getSaveButtonLabel() {
            return vm.isAdd ? _pk_translate('UsersManager_CreateUser') : _pk_translate('UsersManager_SaveBasicInfo');
        }

        function confirmSuperUserChange() {
            $element.find('.superuser-confirm-modal').openModal({ dismissible: false });
        }

        function confirmUserChange() {
            vm.passwordConfirmation = '';
            $element.find('.change-password-modal').openModal({ dismissible: false, ready: function () {
                $('.modal.open #currentUserPassword').focus();
            }});
        }

        function toggleSuperuserAccess() {
            vm.isSavingUserInfo = true;
            piwikApi.post({
                method: 'UsersManager.setSuperUserAccess'
            }, {
                userLogin: vm.user.login,
                hasSuperUserAccess: vm.user.superuser_access ? '1' : '0'
            }).catch(function () {
                // ignore error (still displayed to user)
            }).then(function () {
                vm.isSavingUserInfo = false;
                vm.isUserModified = true;
            });
        }

        function saveUserInfo() {
            if (vm.isAdd) {
                createUser();
            } else {
                confirmUserChange();
            }
        }

        function showUserSavedNotification() {
            var UI = require('piwik/UI');
            var notification = new UI.Notification();
            notification.show(_pk_translate('General_YourChangesHaveBeenSaved'), { context: 'success', type: 'toast' });
        }

        function createUser() {
            vm.isSavingUserInfo = true;
            return piwikApi.post({
                method: 'UsersManager.addUser'
            }, {
                userLogin: vm.user.login,
                password: vm.user.password,
                email: vm.user.email,
                alias: vm.user.alias,
                initialIdSite: vm.firstSiteAccess ? vm.firstSiteAccess.id : undefined
            }).catch(function (e) {
                vm.isSavingUserInfo = false;
                throw e;
            }).then(function () {
                vm.firstSiteAccess = null;
                vm.isSavingUserInfo = false;
                vm.isAdd = false;
                vm.isEmailChanged = false;
                vm.isUserModified = true;

                showUserSavedNotification();
            });
        }

        function updateUser() {
            vm.isSavingUserInfo = true;
            return piwikApi.post({
                method: 'UsersManager.updateUser'
            }, {
                userLogin: vm.user.login,
                password: vm.user.password ? vm.user.password : undefined,
                passwordConfirmation: vm.passwordConfirmation ? vm.passwordConfirmation : undefined,
                email: vm.user.email,
                alias: vm.user.alias
            }).catch(function (e) {
                vm.isSavingUserInfo = false;
                vm.passwordConfirmation = false;
                throw e;
            }).then(function () {
                vm.isSavingUserInfo = false;
                vm.passwordConfirmation = false;
                vm.isUserModified = true;

                showUserSavedNotification();
            });
        }
    }
})();
