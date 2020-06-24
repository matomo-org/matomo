/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <piwik-capabilities-edit>
 */
(function () {
    angular.module('piwikApp').component('piwikCapabilitiesEdit', {
        templateUrl: 'plugins/UsersManager/angularjs/capabilities-edit/capabilities-edit.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            idsite: '<',
            siteName: '<',
            userLogin: '<',
            userRole: '<',
            capabilities: '<',
            onCapabilitiesChange: '&',
        },
        controller: CapabilitiesEditController
    });

    CapabilitiesEditController.$inject = ['piwikApi', 'permissionsMetadataService', 'piwik', '$element'];

    function CapabilitiesEditController(piwikApi, permissionsMetadataService, piwik, $element) {
        var vm = this;

        vm.isBusy = false;
        vm.availableCapabilities = [];
        vm.availableCapabilitiesGrouped = [];
        vm.capabilitiesSet = {};

        // intermediate state
        vm.isAddingCapability = false;
        vm.capabilityToAddOrRemoveId = null;
        vm.capabilityToAddOrRemove = null;

        vm.$onInit = $onInit;
        vm.$onChanges = $onChanges;
        vm.onToggleCapability = onToggleCapability;
        vm.toggleCapability = toggleCapability;
        vm.isIncludedInRole = isIncludedInRole;

        function $onInit() {
            fetchAvailableCapabilities();

            if (typeof vm.capabilities === 'undefined') {
                fetchCapabilities();
            }
        }

        function $onChanges() {
            setCapabilitiesSet();
        }

        function isIncludedInRole(capability) {
            return capability.includedInRoles.indexOf(vm.userRole) !== -1;
        }

        function fetchAvailableCapabilities() {
            permissionsMetadataService.getAllCapabilities()
                .then(function (capabilities) {
                    vm.availableCapabilities = capabilities;
                    setCapabilitiesSet();
                    setAvailableCapabilitiesDropdown();
                });
        }

        function fetchCapabilities() {
            vm.isBusy = true;
            piwikApi.fetch({
                method: 'UsersManager.getUsersPlusRole',
                limit: '1',
                filter_search: vm.userLogin,
            }).then(function (user) {
                if (!user || !user.capabilities) {
                    return [];
                }

                return user.capabilities;
            }).then(function (capabilities) {
                vm.capabilities = capabilities;
                setCapabilitiesSet();
                setAvailableCapabilitiesDropdown();
            })['finally'](function () {
                vm.isBusy = false;
            });
        }

        function setCapabilitiesSet() {
            vm.capabilitiesSet = {};
            (vm.capabilities || []).forEach(function (capability) {
                vm.capabilitiesSet[capability] = true;
            });
            (vm.availableCapabilities || []).forEach(function (capability) {
                if (vm.isIncludedInRole(capability)) {
                    vm.capabilitiesSet[capability.id] = true;
                }
            });
        }

        function setAvailableCapabilitiesDropdown() {
            var availableCapabilitiesGrouped = [];
            vm.availableCapabilities.forEach(function (capability) {
                if (vm.capabilitiesSet[capability.id]) {
                    return;
                }

                availableCapabilitiesGrouped.push({
                    group: capability.category,
                    key: capability.id,
                    value: capability.name,
                    tooltip: capability.description,
                });
            });
            vm.availableCapabilitiesGrouped = availableCapabilitiesGrouped;
            vm.availableCapabilitiesGrouped.sort(function (lhs, rhs) {
                if (lhs.group === rhs.group) {
                    if (lhs.value === rhs.value) {
                        return 0;
                    }
                    return lhs.value < rhs.value ? -1 : 1;
                }
                return lhs.group < rhs.group ? -1 : 1;
            });
        }

        function onToggleCapability(isAdd) {
            vm.isAddingCapability = isAdd;

            vm.capabilityToAddOrRemove = null;
            vm.availableCapabilities.forEach(function (capability) {
                if (capability.id === vm.capabilityToAddOrRemoveId) {
                    vm.capabilityToAddOrRemove = capability;
                }
            });

            $element.find('.confirmCapabilityToggle').modal({
                dismissible: false,
                yes: function () {
                },
            }).modal('open');
        }

        function toggleCapability() {
            if (vm.isAddingCapability) {
                addCapability(vm.capabilityToAddOrRemove);
            } else {
                removeCapability(vm.capabilityToAddOrRemove);
            }
        }

        function addCapability(capability) {
            vm.isBusy = true;
            piwikApi.post({
                method: 'UsersManager.addCapabilities',
            }, {
                userLogin: vm.userLogin,
                capabilities: capability.id,
                idSites: vm.idsite
            }).then(function () {
                vm.onCapabilitiesChange.call({
                    capabilities: getCapabilitiesList(),
                });

                setCapabilitiesSet();
                setAvailableCapabilitiesDropdown();
            })['finally'](function () {
                vm.isBusy = false;
                vm.capabilityToAddOrRemove = null;
                vm.capabilityToAddOrRemoveId = null;
            });
        }

        function removeCapability(capability) {
            vm.isBusy = true;
            piwikApi.post({
                method: 'UsersManager.removeCapabilities',
            }, {
                userLogin: vm.userLogin,
                capabilities: capability.id,
                idSites: vm.idsite
            }).then(function () {
                vm.onCapabilitiesChange.call({
                    capabilities: getCapabilitiesList(),
                });

                setCapabilitiesSet();
                setAvailableCapabilitiesDropdown();
            })['finally'](function () {
                vm.isBusy = false;
                vm.capabilityToAddOrRemove = null;
                vm.capabilityToAddOrRemoveId = null;
            });
        }

        function getCapabilitiesList() {
            var result = [];
            vm.availableCapabilities.forEach(function (capability) {
                if (vm.isIncludedInRole(capability)) {
                    return;
                }

                if (vm.capabilitiesSet[capability.id]) {
                    result.push(capability.id);
                }
            });
            return result;
        }
    }
})();
