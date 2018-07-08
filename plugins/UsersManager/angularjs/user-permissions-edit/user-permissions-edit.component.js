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

    UserPermissionsEditController.$inject = ['$element', '$timeout'];

    function UserPermissionsEditController($element, $timeout) {
        var vm = this;
        vm.siteAccess = [
            {
                name: 'Some website',
                access: 'none',
            },
            {
                name: 'Some other website',
                access: 'admin',
            },
            {
                name: 'A third website',
                access: 'view',
            },
        ];
        vm.offset = 50;
        vm.totalEntries = 1000;
        vm.isAllCheckboxSelected = false;
        vm.selectedRows = {};
        vm.isBulkActionsDisabled = true;
        vm.areAllResultsSelected = false;
        vm.accessLevels = [
            { key: 'view', value: 'View' },
            { key: 'admin', value: 'Admin' }
        ];
        vm.$onInit = $onInit;
        vm.onAccessChange = onAccessChange;
        vm.onRemoveAllAccess = onRemoveAllAccess;
        vm.onAllCheckboxChange = onAllCheckboxChange;
        vm.setAccessBulk = setAccessBulk;
        vm.onRowSelected = onRowSelected;

        function $onInit() {
            vm.limit = vm.limit || 10;

            // if this isn't in a $timeout, angular will throw an exception
            $timeout(function () {
                $element.find('.bulk-actions > .dropdown-trigger').dropdown();
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
    }

})();
