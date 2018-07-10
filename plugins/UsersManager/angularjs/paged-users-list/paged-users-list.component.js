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
            initialSiteName: '<'
        },
        controller: PagedUsersListController
    });

    PagedUsersListController.$inject = ['piwikApi', '$element'];

    function PagedUsersListController(piwikApi, $element) {
        var vm = this;

        // options for selects (TODO: should be supplied server side)
        vm.accessLevels = [
            { key: 'noaccess', value: 'No Access' },
            { key: 'view', value: 'View' },
            { key: 'admin', value: 'Admin' },
            { key: 'superuser', value: 'Superuser', disabled: true  }
        ];
        vm.accessLevelFilterOptions = [
            { key: 'noaccess', value: 'No Access' },
            { key: 'some', value: 'At least View' },
            { key: 'view', value: 'View' },
            { key: 'admin', value: 'Admin' },
            { key: 'superuser', value: 'Superuser' }
        ];

        // pagination state
        vm.offset = 0;
        vm.users = [];
        vm.totalEntries = 10000;
        vm.userTextFilter = '';
        vm.accessLevelFilter = '';
        vm.isLoadingUsers = false;

        // selection state
        vm.areAllResultsSelected = false;
        vm.selectedRows = {};
        vm.isAllCheckboxSelected = false;

        // intermediate state
        vm.isBulkActionsDisabled = true;
        vm.userToDelete = null;

        vm.$onInit = $onInit;
        vm.$onChanges = $onChanges;
        vm.onAllCheckboxChange = onAllCheckboxChange;
        vm.setAccessBulk = setAccessBulk;
        vm.removeAccessBulk = removeAccessBulk;
        vm.onAccessChange = onAccessChange;
        vm.onRowSelected = onRowSelected;
        vm.deleteRequestedUsers = deleteRequestedUsers;
        vm.gotoPreviousPage = gotoPreviousPage;
        vm.gotoNextPage = gotoNextPage;
        vm.fetchUsers = fetchUsers;
        vm.getPaginationUpperBound = getPaginationUpperBound;
        vm.showDeleteConfirm = showDeleteConfirm;
        vm.getSelectedCount = getSelectedCount;
        vm.getAffectedUsersCount = getAffectedUsersCount;

        function $onInit() {
            vm.permissionsForSite = {
                id: vm.initialSiteId,
                name: vm.initialSiteName
            };
            vm.limit = vm.limit || 20;

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
                for (var i = 0; i !== vm.limit; ++i) {
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
                // TODO: can response have an error?
                vm.totalEntries = response.total;
                vm.users = response.results;

                vm.isLoadingUsers = false;

                clearSelection();
            }).catch(function () {
                vm.isLoadingUsers = false;

                clearSelection();
            });
        }

        function setAccessBulk(accessLevel) {
            alert('set access ' + accessLevel); // TODO
        }

        function removeAccessBulk() {
            alert('remove access bulk'); // TODO
        }

        function onAccessChange(user, changeTo) {
            alert('on access change ' + user.login + ' - ' + changeTo); // TODO
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
            if (vm.userToDelete) {
                deleteSingleUser();
            } else {
                deleteMultipleUsers();
            }
        }

        function deleteSingleUser() {
            var userToDelete = vm.userToDelete;
            vm.userToDelete = null;

            vm.isLoadingUsers = true;
            piwikApi.post({
                method: 'UsersManager.deleteUser',
                userLogin: userToDelete.login,
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
            })
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
            var usersToDelete = [];
            Object.keys(vm.selectedRows).forEach(function (index) {
                if (vm.selectedRows[index]
                    && vm.users[index] // safety check
                ) {
                    usersToDelete.push(vm.users[index].login);
                }
            });

            return piwikApi.post({
                method: 'UsersManager.deleteUser',
                'userLogin[]': usersToDelete
            });
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
            $element.find('.delete-user-confirm-modal').openModal();
        }
    }
})();
