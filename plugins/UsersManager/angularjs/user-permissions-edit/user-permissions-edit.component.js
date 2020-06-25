/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
            onAccessChange: '&',
            accessLevels: '<',
            filterAccessLevels: '<'
        },
        controller: UserPermissionsEditController
    });

    UserPermissionsEditController.$inject = ['piwikApi', '$element', '$q'];

    function UserPermissionsEditController(piwikApi, $element, $q) {
        var vm = this;

        // search/pagination state
        vm.siteAccess = [];
        vm.offset = 0;
        vm.totalEntries = null;
        vm.accessLevelFilter = '';
        vm.siteNameFilter = '';
        vm.isLoadingAccess = false;
        vm.allWebsitesAccssLevelSet = 'view';

        // row selection state
        vm.isAllCheckboxSelected = false;
        vm.selectedRows = {};
        vm.isBulkActionsDisabled = true;
        vm.areAllResultsSelected = false;
        vm.previousRole = null;

        // other state
        vm.hasAccessToAtLeastOneSite = true;
        vm.isRoleHelpToggled = false;
        vm.isCapabilitiesHelpToggled = false;
        vm.isGivingAccessToAllSites = false;

        // intermediate state
        vm.roleToChangeTo = null;
        vm.siteAccessToChange = null;

        vm.$onInit = $onInit;
        vm.$onChanges = $onChanges;
        vm.onAllCheckboxChange = onAllCheckboxChange;
        vm.onRowSelected = onRowSelected;
        vm.getPaginationLowerBound = getPaginationLowerBound;
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
        vm.showAddExistingUserModal = showAddExistingUserModal;
        vm.giveAccessToAllSites = giveAccessToAllSites;
        vm.showChangeAccessAllSitesModal = showChangeAccessAllSitesModal;

        function giveAccessToAllSites() {
            vm.isGivingAccessToAllSites = true;
            piwikApi.fetch({
                method: 'SitesManager.getSitesWithAdminAccess',
            }).then(function (allSites) {
                var idSites = allSites.map(function (s) { return s.idsite; });
                return piwikApi.post({
                    method: 'UsersManager.setUserAccess'
                }, {
                    userLogin: vm.userLogin,
                    access: vm.allWebsitesAccssLevelSet,
                    'idSites[]': idSites,
                });
            }).then(function () {
                return vm.fetchAccess();
            })['finally'](function () {
                vm.isGivingAccessToAllSites = false;
            });
        }

        function showChangeAccessAllSitesModal() {
            $element.find('.confirm-give-access-all-sites').modal({ dismissible: false }).modal('open');
        }

        function $onInit() {
            vm.limit = vm.limit || 10;

            resetSiteToAdd();
            fetchAccess();
        }

        function $onChanges() {
            vm.accessLevels = vm.accessLevels.filter(shouldShowAccessLevel);
            vm.filterAccessLevels = vm.filterAccessLevels.filter(shouldShowAccessLevel);

            if (vm.limit) {
                fetchAccess();
            }

            function shouldShowAccessLevel(entry) {
                return entry.key !== 'superuser';
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
            }, { includeHeaders: true }).then(function (result) {
                vm.isLoadingAccess = false;
                vm.siteAccess = result.response;
                vm.totalEntries = parseInt(result.headers('x-matomo-total-results')) || 0;
                vm.hasAccessToAtLeastOneSite = !! result.headers('x-matomo-has-some');

                if (vm.onUserHasAccessDetected) {
                    vm.onUserHasAccessDetected({ hasAccess: vm.hasAccessToAtLeastOneSite });
                }

                clearSelection();
            }).catch(function () {
                vm.isLoadingAccess = false;

                clearSelection();
            });
        }

        function getAllSitesInSearch() {
            return piwikApi.fetch({
                method: 'UsersManager.getSitesAccessForUser',
                filter_search: vm.siteNameFilter,
                filter_access: vm.accessLevelFilter,
                userLogin: vm.userLogin,
                filter_limit: '-1'
            }).then(function (access) {
                return access.map(function (a) { return a.idsite; });
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

        function getPaginationLowerBound() {
            return vm.offset + 1;
        }

        function getPaginationUpperBound() {
            return Math.min(vm.offset + vm.limit, vm.totalEntries);
        }

        function resetSiteToAdd() {
            vm.siteToAdd = {
                id: null,
                name: ''
            };
        }

        function changeUserRole() {
            vm.isLoadingAccess = true;

            return $q.resolve().then(function () {
                if (vm.siteAccessToChange) {
                    return [vm.siteAccessToChange.idsite];
                }

                if (vm.areAllResultsSelected) {
                    return getAllSitesInSearch();
                }

                return getSelectedSites();
            }).then(function (idSites) {
                return piwikApi.post({
                    method: 'UsersManager.setUserAccess'
                }, {
                    userLogin: vm.userLogin,
                    access: vm.roleToChangeTo,
                    'idSites[]': idSites
                });
            }).catch(function () {
                // ignore (errors will still be displayed to the user)
            }).then(function () {
                vm.onAccessChange();

                return fetchAccess();
            });
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
            $element.find('.delete-access-confirm-modal').modal({ dismissible: false }).modal('open');
        }

        function showChangeAccessConfirm() {
            $element.find('.change-access-confirm-modal').modal({ dismissible: false }).modal('open');
        }

        function showAddExistingUserModal() {
            $element.find('.add-existing-user-modal').modal({ dismissible: false }).modal('open');
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
