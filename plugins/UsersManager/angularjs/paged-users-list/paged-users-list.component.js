/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <piwik-paged-users-list>
 */
(function () {
    angular.module('piwikApp').component('piwikPagedUsersList', {
        templateUrl: 'plugins/UsersManager/angularjs/paged-users-list/paged-users-list.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            onEditUser: '&',
            limit: '<',
            initialSiteId: '<',
            initialSiteName: '<',
            currentUserRole: '<',
            accessLevels: '<',
            filterAccessLevels: '<'
        },
        controller: PagedUsersListController
    });

    PagedUsersListController.$inject = ['piwikApi', '$element', '$scope'];

    function PagedUsersListController(piwikApi, $element, $scope) {
        var vm = this;

        // options for selects
        vm.bulkActionAccessLevels = null;

        // search state
        vm.offset = 0;
        vm.users = [];
        vm.totalEntries = null;
        vm.userTextFilter = '';
        vm.accessLevelFilter = '';
        vm.isLoadingUsers = false;

        // selection state
        vm.areAllResultsSelected = false;
        vm.selectedRows = {};
        vm.isAllCheckboxSelected = false;

        // intermediate state
        vm.isBulkActionsDisabled = true;
        vm.userToChange = null;
        vm.roleToChangeTo = null;
        vm.previousRole = null;

        // other state
        vm.isRoleHelpToggled = false;

        vm.$onInit = $onInit;
        vm.$onChanges = $onChanges;
        vm.onAllCheckboxChange = onAllCheckboxChange;
        vm.changeUserRole = changeUserRole;
        vm.onRowSelected = onRowSelected;
        vm.deleteRequestedUsers = deleteRequestedUsers;
        vm.gotoPreviousPage = gotoPreviousPage;
        vm.gotoNextPage = gotoNextPage;
        vm.fetchUsers = fetchUsers;
        vm.getPaginationUpperBound = getPaginationUpperBound;
        vm.showDeleteConfirm = showDeleteConfirm;
        vm.getSelectedCount = getSelectedCount;
        vm.getAffectedUsersCount = getAffectedUsersCount;
        vm.showAccessChangeConfirm = showAccessChangeConfirm;
        vm.getRoleDisplay = getRoleDisplay;

        // if another component requests we reload, reload
        $scope.$on('paged-users-list:reload', function () {
            fetchUsers();
        });

        function $onInit() {
            vm.permissionsForSite = {
                id: vm.initialSiteId,
                name: vm.initialSiteName
            };
            vm.limit = vm.limit || 20;

            vm.bulkActionAccessLevels = [];
            vm.accessLevels.forEach(function (entry) {
                if (entry.key !== 'noaccess' && entry.key !== 'superuser') {
                    vm.bulkActionAccessLevels.push(entry);
                }
            });

            fetchUsers();
        }

        function $onChanges(changes) {
            if (changes.limit && vm.permissionsForSite) {
                fetchUsers();
            }
        }

        function onAllCheckboxChange() {
            if (!vm.isAllCheckboxSelected) {
                clearSelection();
            } else {
                for (var i = 0; i !== vm.users.length; ++i) {
                    vm.selectedRows[i] = true;
                }
                vm.isBulkActionsDisabled = false;
            }
        }

        function clearSelection() {
            vm.selectedRows = {};
            vm.areAllResultsSelected = false;
            vm.isBulkActionsDisabled = true;
            vm.isAllCheckboxSelected = false;
            vm.userToChange = null;
        }

        function fetchUsers() {
            vm.isLoadingUsers = true;
            return piwikApi.fetch({
                method: 'UsersManager.getUsersPlusRole',
                limit: vm.limit,
                offset: vm.offset,
                filter_search: vm.userTextFilter,
                filter_access: vm.accessLevelFilter,
                idSite: vm.permissionsForSite.id
            }).then(function (response) {
                vm.totalEntries = response.total;
                vm.users = response.results;

                vm.isLoadingUsers = false;

                clearSelection();
            }).catch(function () {
                vm.isLoadingUsers = false;

                clearSelection();
            });
        }

        function changeUserRole() {
            vm.isLoadingUsers = true;

            var apiPromise;
            if (vm.userToChange) {
                apiPromise = piwikApi.post({
                    method: 'UsersManager.setUserAccess',
                    userLogin: vm.userToChange.login,
                    access: vm.roleToChangeTo,
                    idSites: vm.permissionsForSite.id
                });
            } else {
                apiPromise = changeUserRoleBulk();
            }

            apiPromise.catch(function () {
                // ignore (errors will still be displayed to the user)
            }).then(function () {
                return fetchUsers();
            });
        }

        function changeUserRoleBulk() {
            if (vm.areAllResultsSelected) {
                return piwikApi.post({
                    method: 'UsersManager.setUserAccessMatching',
                    access: vm.roleToChangeTo,
                    filter_search: vm.userTextFilter,
                    filter_access: vm.accessLevelFilter,
                    idSite: vm.permissionsForSite.id
                });
            } else {
                var usersToChange = getSelectedUsers();
                return piwikApi.post({
                    method: 'UsersManager.setUserAccess',
                    'userLogin[]': usersToChange,
                    access: vm.roleToChangeTo,
                    idSites: vm.permissionsForSite.id
                });
            }
        }

        function showAccessChangeConfirm() {
            $element.find('.change-user-role-confirm-modal').openModal({ dismissible: false });
        }

        function getAffectedUsersCount() {
            if (vm.areAllResultsSelected) {
                return vm.totalEntries;
            }

            return getSelectedCount();
        }

        function onRowSelected() {
            var selectedRowKeyCount = getSelectedCount();
            vm.isBulkActionsDisabled = selectedRowKeyCount === 0;
            vm.isAllCheckboxSelected = selectedRowKeyCount === vm.users.length;
        }

        function getSelectedCount() {
            var selectedRowKeyCount = 0;
            Object.keys(vm.selectedRows).forEach(function (key) {
                if (vm.selectedRows[key]) {
                    ++selectedRowKeyCount;
                }
            });
            return selectedRowKeyCount;
        }

        function deleteRequestedUsers() {
            if (vm.userToChange) {
                deleteSingleUser();
            } else {
                deleteMultipleUsers();
            }
        }

        function deleteSingleUser() {
            var userToChange = vm.userToChange;
            vm.userToChange = null;

            vm.isLoadingUsers = true;
            piwikApi.post({
                method: 'UsersManager.deleteUser',
                userLogin: userToChange.login,
            }).catch(function () {
                // ignore (errors will still be displayed to the user)
            }).then(function () {
                return fetchUsers();
            });
        }

        function deleteMultipleUsers() {
            vm.isLoadingUsers = true;

            var apiPromise;
            if (vm.areAllResultsSelected) {
                apiPromise = deleteUsersMatchingSearch();
            } else {
                apiPromise = deleteSelectedUsers();
            }

            apiPromise.catch(function () {
                // ignore (errors will still be displayed to the user)
            }).then(function () {
                return fetchUsers();
            });
        }

        function deleteUsersMatchingSearch() {
            return piwikApi.post({
                method: 'UsersManager.deleteUsersMatching',
                filter_search: vm.userTextFilter,
                filter_access: vm.accessLevelFilter,
                idSite: vm.permissionsForSite.id
            });
        }

        function deleteSelectedUsers() {
            var usersToDelete = getSelectedUsers();
            return piwikApi.post({
                method: 'UsersManager.deleteUser',
                'userLogin[]': usersToDelete
            });
        }

        function getSelectedUsers() {
            var result = [];
            Object.keys(vm.selectedRows).forEach(function (index) {
                if (vm.selectedRows[index]
                    && vm.users[index] // safety check
                ) {
                    result.push(vm.users[index].login);
                }
            });
            return result;
        }

        function gotoPreviousPage() {
            vm.offset = Math.max(0, vm.offset - vm.limit);

            fetchUsers();
        }

        function gotoNextPage() {
            var newOffset = vm.offset + vm.limit;
            if (newOffset >= vm.totalEntries) {
                return;
            }

            vm.offset = newOffset;
            fetchUsers();
        }

        function getPaginationUpperBound() {
            return Math.min(vm.offset + vm.limit, vm.totalEntries);
        }

        function showDeleteConfirm() {
            $element.find('.delete-user-confirm-modal').openModal({ dismissible: false });
        }

        function getRoleDisplay(role) {
            var result = null;
            vm.accessLevels.forEach(function (entry) {
                if (entry.key === role) {
                    result = entry.value;
                }
            });
            return result;
        }
    }
})();
