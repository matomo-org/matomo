/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
        vm.isPasswordModified = false;

        vm.$onInit = $onInit;
        vm.$onChanges = $onChanges;
        vm.confirmSuperUserChange = confirmSuperUserChange;
        vm.confirmReset2FA = confirmReset2FA;
        vm.getFormTitle = getFormTitle;
        vm.getSaveButtonLabel = getSaveButtonLabel;
        vm.toggleSuperuserAccess = toggleSuperuserAccess;
        vm.saveUserInfo = saveUserInfo;
        vm.reset2FA = reset2FA;
        vm.updateUser = updateUser;
        vm.setSuperUserAccessChecked = setSuperUserAccessChecked;

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

            setSuperUserAccessChecked();
        }

        function getFormTitle() {
            return vm.isAdd ? _pk_translate('UsersManager_AddNewUser') : _pk_translate('UsersManager_EditUser');
        }

        function getSaveButtonLabel() {
            return vm.isAdd ? _pk_translate('UsersManager_CreateUser') : _pk_translate('UsersManager_SaveBasicInfo');
        }

        function confirmSuperUserChange() {
            $element.find('.superuser-confirm-modal').modal({ dismissible: false }).modal('open');
        }

        function confirmReset2FA() {
            $element.find('.twofa-confirm-modal').modal({ dismissible: false }).modal('open');
        }

        function confirmUserChange() {
            vm.passwordConfirmation = '';
            function onEnter(event){
                var keycode = (event.keyCode ? event.keyCode : event.which);
                if (keycode == '13'){
                    $element.find('.change-password-modal').modal('close');
                    vm.updateUser();
                }
            }

            $element.find('.change-password-modal').modal({ dismissible: false, ready: function () {
                $('.modal.open #currentUserPassword').focus();
                $('.modal.open #currentUserPassword').off('keypress').keypress(onEnter);
            }}).modal('open');
        }

        function toggleSuperuserAccess() {
            vm.isSavingUserInfo = true;
            piwikApi.post({
                method: 'UsersManager.setSuperUserAccess'
            }, {
                userLogin: vm.user.login,
                hasSuperUserAccess: vm.user.superuser_access ? '0' : '1',
                passwordConfirmation: vm.passwordConfirmationForSuperUser,
            }).then(function () {
                vm.user.superuser_access = !vm.user.superuser_access;
            }).catch(function () {
                // ignore error (still displayed to user)
            }).then(function () {
                vm.isSavingUserInfo = false;
                vm.isUserModified = true;
                vm.passwordConfirmationForSuperUser = null;
                setSuperUserAccessChecked();
            });
        }

        function setSuperUserAccessChecked() {
            vm.superUserAccessChecked = !! vm.user.superuser_access;
        }

        function saveUserInfo() {
            if (vm.isAdd) {
                createUser();
            } else {
                confirmUserChange();
            }
        }

        function reset2FA() {
            vm.isResetting2FA = true;
            return piwikApi.post({
                method: 'TwoFactorAuth.resetTwoFactorAuth',
                userLogin: vm.user.login
            }).catch(function (e) {
                vm.isResetting2FA = false;
                throw e;
            }).then(function () {
                vm.isResetting2FA = false;
                vm.user.uses_2fa = false;
                vm.activeTab = 'basic';

                showUserSavedNotification();
            });
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
                password: (vm.isPasswordModified && vm.user.password) ? vm.user.password : undefined,
                passwordConfirmation: vm.passwordConfirmation ? vm.passwordConfirmation : undefined,
                email: vm.user.email
            }).catch(function (e) {
                vm.isSavingUserInfo = false;
                vm.passwordConfirmation = false;
                throw e;
            }).then(function () {
                vm.isSavingUserInfo = false;
                vm.passwordConfirmation = false;
                vm.isUserModified = true;
                vm.isPasswordModified = false;

                showUserSavedNotification();
            });
        }
    }
})();
