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

    PagedUsersListController.$inject = ['piwik'];

    function PagedUsersListController(piwik) {
        var vm = this;
        vm.userAccess = {
            testuser: 'admin',
            testuser2: 'view'
        };
        vm.users = [
            {
                login: 'testuser',
                alias: 'testalias',
                email: 'somewhere@something.com',
                token_auth: 'alsjfdlsdakjflsakdjf',
                superuser_access: 1,
                date_registered: '2018-01-23 03:45:45',
                last_seen: '00:00:35'
            },
            {
                login: 'testuser2',
                alias: 'testalias2',
                email: 'email2@something.com',
                token_auth: 'alsjfdlabasdakjflsakdjf',
                superuser_access: 0,
                date_registered: '2018-01-23 03:45:45',
                last_seen: '00:00:05'
            },
        ];
        vm.areAllResultsSelected = false;
        vm.totalEntries = 10000;
        vm.selectedRows = {};
        vm.isAllCheckboxSelected = false;
        vm.userTextFilter = '';
        vm.accessLevelFilter = '';
        vm.accessLevels = [
            { key: 'view', value: 'View' },
            { key: 'admin', value: 'Admin' }
        ];
        vm.accessLevelFilterOptions = [
            { key: 'none', value: 'None' },
            { key: 'some', value: 'At least View access' },
            { key: 'view', value: 'View' },
            { key: 'admin', value: 'Admin' },
            { key: 'superuser', value: 'Superuser' }
        ];
        vm.isBulkActionsDisabled = true;
        vm.$onInit = $onInit;
        vm.onAllCheckboxChange = onAllCheckboxChange;
        vm.setAccessBulk = setAccessBulk;
        vm.removeAccessBulk = removeAccessBulk;
        vm.deleteUsersBulk = deleteUsersBulk;
        vm.onAccessChange = onAccessChange;
        vm.onRowSelected = onRowSelected;

        function $onInit() {
            vm.limit = vm.limit || 20;
            vm.permissionsForSite = {
                id: vm.initialSiteId,
                name: vm.initialSiteName
            };
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
console.log(selectedRowKeyCount, vm.users.length);
            vm.isAllCheckboxSelected = selectedRowKeyCount === vm.users.length;
        }
    }
})();
