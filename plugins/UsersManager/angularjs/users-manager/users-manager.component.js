/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
            currentUserRole: '<',
            initialSiteName: '@',
            initialSiteId: '@',
            accessLevels: '<',
            filterAccessLevels: '<'
        },
        controller: UsersManagerController
    });

    UsersManagerController.$inject = ['$element', 'piwik', 'piwikApi', '$q', '$timeout'];

    function UsersManagerController($element, piwik, piwikApi, $q, $timeout) {
        var vm = this;

        var search = String(window.location.search);
        vm.isEditing = !!piwik.helper.getArrayFromQueryString(search).showadduser;

        vm.isCurrentUserSuperUser = true;

        // search state
        vm.users = [];
        vm.totalEntries = null;
        vm.searchParams = {};
        vm.isLoadingUsers = false;

        vm.$onInit = $onInit;
        vm.$onChanges = $onChanges;
        vm.$onDestroy = $onDestroy;
        vm.onEditUser = onEditUser;
        vm.onDoneEditing = onDoneEditing;
        vm.showAddExistingUserModal = showAddExistingUserModal;
        vm.onChangeUserRole = onChangeUserRole;
        vm.onDeleteUser = onDeleteUser;
        vm.fetchUsers = fetchUsers;
        vm.addExistingUser = addExistingUser;

        function onChangeUserRole(users, role) {
            vm.isLoadingUsers = true;

            $q.resolve().then(function () {
                if (users === 'all') {
                    return getAllUsersInSearch();
                }
                return users;
            }).then(function (users) {
                return users.filter(function (user) {
                    return user.role !== 'superuser';
                }).map(function (user) {
                    return user.login;
                });
            }).then(function (userLogins) {
                var requests = userLogins.map(function (login) {
                    return {
                        method: 'UsersManager.setUserAccess',
                        userLogin: login,
                        access: role,
                        idSites: vm.searchParams.idSite,
                        ignoreSuperusers: 1
                    };
                });
                return piwikApi.bulkFetch(requests, { createErrorNotification: true });
            }).catch(function (e) {
                // ignore (errors will still be displayed to the user)
            }).then(function () {
                return fetchUsers();
            });
        }

        function onDeleteUser(users) {
            vm.isLoadingUsers = true;

            $q.resolve().then(function () {
                if (users === 'all') {
                    return getAllUsersInSearch();
                }
                return users;
            }).then(function (users) {
                return users.map(function (user) { return user.login; });
            }).then(function (userLogins) {
                var requests = userLogins.map(function (login) {
                    return {
                        method: 'UsersManager.deleteUser',
                        userLogin: login
                    };
                });
                return piwikApi.bulkFetch(requests, { createErrorNotification: true });
            }).catch(function () {
                // ignore (errors will still be displayed to the user)
            }).then(function () {
                return fetchUsers();
            });
        }

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

            if (vm.currentUserRole === 'superuser') {
                vm.filterAccessLevels.push({ key: 'superuser', value: 'Superuser' });
            }

            vm.searchParams = {
                offset: 0,
                limit: 20,
                filter_search: '',
                filter_access: '',
                idSite: vm.initialSiteId
            };

            fetchUsers();
        }

        function $onChanges(changes) {
            if (changes.limit) {
                fetchUsers();
            }
        }

        function $onDestroy() {
            try {
                $element.tooltip('destroy');
            } catch (e) {
                // empty
            }
        }

        function fetchUsers() {
            vm.isLoadingUsers = true;
            return piwikApi.fetch($.extend({}, vm.searchParams, {
                method: 'UsersManager.getUsersPlusRole'
            }), { includeHeaders: true }).then(function (result) {
                vm.totalEntries = parseInt(result.headers('x-matomo-total-results')) || 0;
                vm.users = result.response;

                vm.isLoadingUsers = false;
            }).catch(function () {
                vm.isLoadingUsers = false;
            });
        }

        function getAllUsersInSearch() {
            return piwikApi.fetch({
                method: 'UsersManager.getUsersPlusRole',
                filter_search: vm.searchParams.filter_search,
                filter_access: vm.searchParams.filter_access,
                idSite: vm.searchParams.idSite,
                filter_limit: '-1'
            });
        }

        function onEditUser(user) {
            piwik.helper.lazyScrollToContent();
            vm.isEditing = true;
            vm.userBeingEdited = user;
        }

        function onDoneEditing(isUserModified) {
            vm.isEditing = false;
            if (isUserModified) { // if a user was modified, we must reload the users list
                fetchUsers();
            }
        }

        function showAddExistingUserModal() {
            $element.find('.add-existing-user-modal').modal({ dismissible: false }).modal('open');
        }

        function addExistingUser() {
            vm.isLoadingUsers = true;
            return piwikApi.fetch({
                method: 'UsersManager.userExists',
                userLogin: vm.addNewUserLoginEmail
            }).then(function (response) {
                if (response && response.value) {
                    return vm.addNewUserLoginEmail;
                }

                return piwikApi.fetch({
                    method: 'UsersManager.getUserLoginFromUserEmail',
                    userEmail: vm.addNewUserLoginEmail
                }).then(function (response) {
                    return response.value;
                });
            }).then(function (login) {
                return piwikApi.post({
                    method: 'UsersManager.setUserAccess'
                }, {
                    userLogin: login,
                    access: 'view',
                    idSites: vm.searchParams.idSite
                });
            }).catch(function (error) {
                vm.isLoadingUsers = false;
                throw error;
            }).then(function () {
                return fetchUsers();
            });
        }
    }
})();
