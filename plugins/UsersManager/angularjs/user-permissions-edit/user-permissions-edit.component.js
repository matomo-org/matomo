/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <piwik-user-permissions-edit>
 */
(function () {
    angular.module('piwikApp').component('piwikUserPermissionsEdit', {
        templateUrl: 'plugins/UsersManager/angularjs/user-permissions-edit/user-permissions-edit.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            userLogin: '<',
            limit: '<'
        },
        controller: UserPermissionsEditController
    });

    UserPermissionsEditController.$inject = ['piwikApi'];

    function UserPermissionsEditController(piwikApi) {
        var vm = this;

        // TODO: code redundancy w/ paged-users-list
        // access level select options
        vm.accessLevels = [
            { key: 'view', value: 'View' },
            { key: 'admin', value: 'Admin' }
        ];
        vm.accessLevelFilterOptions = [
            { key: 'some', value: 'At least View access' },
            { key: 'view', value: 'View' },
            { key: 'admin', value: 'Admin' }
        ];

        // search/pagination state
        vm.siteAccess = [];
        vm.offset = 0;
        vm.totalEntries = null;
        vm.accessLevelFilter = 'some';
        vm.siteNameFilter = '';
        vm.isLoadingAccess = false;

        // row selection state
        vm.isAllCheckboxSelected = false;
        vm.selectedRows = {};
        vm.isBulkActionsDisabled = true;
        vm.areAllResultsSelected = false;

        // TODO: how to know which site is the first this user doesn't have access to?
        vm.siteToAdd = {
            id: 1,
            siteName: 'TODO'
        };

        vm.$onInit = $onInit;
        vm.$onChanges = $onChanges;
        vm.onAccessChange = onAccessChange;
        vm.onRemoveAllAccess = onRemoveAllAccess;
        vm.onAllCheckboxChange = onAllCheckboxChange;
        vm.setAccessBulk = setAccessBulk;
        vm.onRowSelected = onRowSelected;
        vm.getPaginationUpperBound = getPaginationUpperBound;
        vm.addAccessToSite = addAccessToSite;

        function $onInit() {
            vm.limit = vm.limit || 10;
        }

        function $onChanges() {
            fetchAccess();
        }

        function fetchAccess() {
            vm.isLoadingAccess = true;
            piwikApi.fetch({
                method: 'UsersManager.getSitesAccessForUser',
                limit: vm.limit,
                offset: vm.offset,
                filter_search: vm.siteNameFilter,
                filter_access: vm.accessLevelFilter,
                userLogin: vm.userLogin
            }).then(function (response) {
                vm.isLoadingAccess = false;
                vm.siteAccess = response.results;
                vm.totalEntries = response.total;
            }).catch(function () {
                vm.isLoadingAccess = false;
            });
        }

        function onAccessChange(entry) {
            alert('access change');
            // TODO
        }

        function onRemoveAllAccess(entry) {
            alert('remove all access');
            // TODO
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

        function setAccessBulk(access) {
            alert('set access ' + access);
            // TODO
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

            vm.isAllCheckboxSelected = selectedRowKeyCount === vm.siteAccess.length;
        }

        function getPaginationUpperBound() {
            return Math.min(vm.offset + vm.limit, vm.totalEntries);
        }

        function addAccessToSite(idSite) {
            alert('add access ' + idSite);
            // TODO
        }
    }

})();
