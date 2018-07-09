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
            onDeleteUser: '&',
            limit: '<',
            initialSiteId: '<',
            initialSiteName: '<'
        },
        controller: PagedUsersListController
    });

    PagedUsersListController.$inject = ['piwikApi'];

    function PagedUsersListController(piwikApi) {
        var vm = this;

        // options for selects (TODO: should be supplied server side)
        vm.accessLevels = [
            { key: 'noaccess', value: 'No Access' },
            { key: 'view', value: 'View' },
            { key: 'admin', value: 'Admin' }
        ];
        vm.accessLevelFilterOptions = [
            { key: 'noaccess', value: 'No Access' },
            { key: 'some', value: 'At least View access' },
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

        vm.$onInit = $onInit;
        vm.onAllCheckboxChange = onAllCheckboxChange;
        vm.setAccessBulk = setAccessBulk;
        vm.removeAccessBulk = removeAccessBulk;
        vm.deleteUsersBulk = deleteUsersBulk;
        vm.onAccessChange = onAccessChange;
        vm.onRowSelected = onRowSelected;
        vm.deleteRequestedUsers = deleteRequestedUsers;
        vm.gotoPreviousPage = gotoPreviousPage;
        vm.gotoNextPage = gotoNextPage;
        vm.fetchUsers = fetchUsers;
        vm.getPaginationUpperBound = getPaginationUpperBound;

        function $onInit() {
            vm.limit = vm.limit || 20;
            vm.permissionsForSite = {
                id: vm.initialSiteId,
                name: vm.initialSiteName
            };

            fetchUsers();
        }

        function onAllCheckboxChange() {
            if (!vm.isAllCheckboxSelected) {
                vm.selectedRows = {};
                vm.areAllResultsSelected = false;
                vm.isBulkActionsDisabled = true;
            } else {
                for (var i = 0; i !== vm.limit; ++i) {
                    vm.selectedRows[i] = true;
                }
                vm.isBulkActionsDisabled = false;
            }
        }

        function fetchUsers() {
            vm.isLoadingUsers = true;
            piwikApi.fetch({
                method: 'UsersManager.getUsersPlusAccessLevel',
                limit: vm.limit,
                offset: vm.offset,
                filter_search: vm.userTextFilter,
                filter_access: vm.accessLevelFilter,
                idSite: vm.permissionsForSite ? vm.permissionsForSite.id : null
            }).then(function (response) {
                // TODO: can response have an error?
                vm.totalEntries = response.total;
                vm.users = response.results;
                vm.isLoadingUsers = false;
            }).catch(function () {
                vm.isLoadingUsers = false;
            });
        }

        function setAccessBulk(accessLevel) {
            alert('set access ' + accessLevel); // TODO
        }

        function removeAccessBulk() {
            alert('remove access bulk'); // TODO
        }

        function deleteUsersBulk() {
            alert('delete users bulk'); // TODO
        }

        function onAccessChange(user, changeTo) {
            alert('on access change ' + user.login + ' - ' + changeTo); // TODO
        }

        function onRowSelected() {
            vm.isBulkActionsDisabled = true;

            var selectedRowKeyCount = 0;
            Object.keys(vm.selectedRows).forEach(function (key) {
                if (vm.selectedRows[key]) {
                    ++selectedRowKeyCount;
                    vm.isBulkActionsDisabled = false;
                }
            });

            vm.isAllCheckboxSelected = selectedRowKeyCount === vm.users.length;
        }

        function deleteRequestedUsers() {
            alert('delete requested users'); // TODO
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
    }
})();
