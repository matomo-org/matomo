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
            limit: '<',
            onUserHasAccessDetected: '&',
            onAccessChange: '&'
        },
        controller: UserPermissionsEditController
    });

    UserPermissionsEditController.$inject = ['piwikApi', '$element'];

    function UserPermissionsEditController(piwikApi, $element) {
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
        vm.previousRole = null;

        // other state
        vm.hasAccessToAtLeastOneSite = true;

        // intermediate state
        vm.roleToChangeTo = null;
        vm.siteAccessToChange = null;
        // TODO: need to display in site selector only sites user has admin access to.
        // TODO: how to know which site is the first this user doesn't have access to?
        vm.siteToAdd = {
            id: null,
            name: ''
        };

        vm.$onInit = $onInit;
        vm.$onChanges = $onChanges;
        vm.onAllCheckboxChange = onAllCheckboxChange;
        vm.onRowSelected = onRowSelected;
        vm.getPaginationUpperBound = getPaginationUpperBound;
        vm.fetchAccess = fetchAccess;
        vm.gotoPreviousPage = gotoPreviousPage;
        vm.gotoNextPage = gotoNextPage;
        vm.showRemoveAccessConfirm = showRemoveAccessConfirm;
        vm.getSelectedRowsCount = getSelectedRowsCount;
        vm.getAffectedSitesCount = getAffectedSitesCount;
        vm.changeUserRole = changeUserRole;
        vm.showChangeAccessConfirm = showChangeAccessConfirm;
        vm.getRoleDisplay = getRoleDisplay;
        vm.addUserRole = addUserRole;

        function $onInit() {
            vm.limit = vm.limit || 10;
            fetchAccess();
        }

        function $onChanges() {
            if (vm.limit) {
                fetchAccess();
            }
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
                vm.hasAccessToAtLeastOneSite = !! response.has_access_to_any;

                if (vm.onUserHasAccessDetected) {
                    vm.onUserHasAccessDetected({ hasAccess: vm.hasAccessToAtLeastOneSite });
                }

                clearSelection();
            }).catch(function () {
                vm.isLoadingAccess = false;

                clearSelection();
            });
        }

        function clearSelection() {
            vm.selectedRows = {};
            vm.areAllResultsSelected = false;
            vm.isBulkActionsDisabled = true;
            vm.isAllCheckboxSelected = false;
            vm.siteAccessToChange = null;
        }

        function onAllCheckboxChange() {
            if (!vm.isAllCheckboxSelected) {
                clearSelection();
            } else {
                for (var i = 0; i !== vm.siteAccess.length; ++i) {
                    vm.selectedRows[i] = true;
                }
                vm.isBulkActionsDisabled = false;
            }
        }

        function onRowSelected() {
            var selectedRowKeyCount = getSelectedRowsCount();
            vm.isBulkActionsDisabled = selectedRowKeyCount === 0;
            vm.isAllCheckboxSelected = selectedRowKeyCount === vm.siteAccess.length;
        }

        function getPaginationUpperBound() {
            return Math.min(vm.offset + vm.limit, vm.totalEntries);
        }

        function addUserRole() {
            vm.isLoadingAccess = true;
            piwikApi.post({
                method: 'UsersManager.setUserAccess',
                userLogin: vm.userLogin,
                access: vm.roleToChangeTo,
                idSites: vm.siteAccessToChange.idsite,
                ignoreExisting: 1
            }).catch(function () {
                // ignore (errors will still be displayed to the user)
            }).then(function () {
                vm.onAccessChange();

                return fetchAccess();
            }).then(function () {
                setTimeout(function () { // timeout to let angularjs finish rendering
                    var UI = require('piwik/UI');
                    var notification = new UI.Notification();
                    notification.toast(_pk_translate('General_Done'), {
                        placeat: '.userPermissionsEdit .add-site .title',
                        context: 'success',
                        noclear: true,
                        type: 'toast',
                        class: 'user-permission-toast',
                        toastLength: 3000
                    });
                }, 500);
            });
        }

        function changeUserRole() {
            vm.isLoadingAccess = true;

            var apiPromise;
            if (vm.siteAccessToChange) {
                apiPromise = piwikApi.post({
                    method: 'UsersManager.setUserAccess',
                    userLogin: vm.userLogin,
                    access: vm.roleToChangeTo,
                    idSites: vm.siteAccessToChange.idsite
                });
            } else {
                apiPromise = bulkChangeUserRole();
            }

            apiPromise.catch(function () {
                // ignore (errors will still be displayed to the user)
            }).then(function () {
                vm.onAccessChange();

                return fetchAccess();
            });
        }

        function bulkChangeUserRole() {
            if (vm.areAllResultsSelected) {
                return piwikApi.post({
                    method: 'UsersManager.setSiteAccessMatching',
                    userLogin: vm.userLogin,
                    access: vm.roleToChangeTo,
                    filter_search: vm.siteNameFilter,
                    filter_access: vm.accessLevelFilter,
                });
            } else {
                var idSites = getSelectedSites();
                return piwikApi.post({
                    method: 'UsersManager.setUserAccess',
                    userLogin: vm.userLogin,
                    access: vm.roleToChangeTo,
                    'idSites[]': idSites
                });
            }
        }

        function getSelectedSites() {
            var result = [];
            Object.keys(vm.selectedRows).forEach(function (index) {
                if (vm.selectedRows[index]
                    && vm.siteAccess[index] // safety check
                ) {
                    result.push(vm.siteAccess[index].idsite);
                }
            });
            return result;
        }

        function gotoPreviousPage() {
            vm.offset = Math.max(0, vm.offset - vm.limit);

            fetchAccess();
        }

        function gotoNextPage() {
            var newOffset = vm.offset + vm.limit;
            if (newOffset >= vm.totalEntries) {
                return;
            }

            vm.offset = newOffset;
            fetchAccess();
        }

        function showRemoveAccessConfirm() {
            $element.find('.delete-access-confirm-modal').openModal({ dismissible: false });
        }

        function showChangeAccessConfirm() {
            $element.find('.change-access-confirm-modal').openModal({ dismissible: false });
        }

        function getSelectedRowsCount() {
            var selectedRowKeyCount = 0;
            Object.keys(vm.selectedRows).forEach(function (key) {
                if (vm.selectedRows[key]) {
                    ++selectedRowKeyCount;
                }
            });
            return selectedRowKeyCount;
        }

        function getAffectedSitesCount() {
            if (vm.areAllResultsSelected) {
                return vm.totalEntries;
            }

            return getSelectedRowsCount();
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
