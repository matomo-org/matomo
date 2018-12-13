/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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

        // intermediate state
        vm.isAddingCapability = false;
        vm.capabilityToAdd = null;

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
            setCapabilitiesSet(vm.capabilities);
        }

        function isIncludedInRole(capability) {
            return capability.includedInRoles.indexOf(vm.userRole) !== -1;
        }

        function fetchAvailableCapabilities() {
            permissionsMetadataService.getAllCapabilities()
                .then(function (capabilities) {
                    vm.availableCapabilities = capabilities;
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
                setCapabilitiesSet(capabilities);
            })['finally'](function () {
                vm.isBusy = false;
            });
        }

        function setCapabilitiesSet(capabilities) {
            vm.capabilitiesSet = {};
            capabilities.forEach(function (capability) {
                vm.capabilitiesSet[capability] = true;
            });
            vm.availableCapabilities.forEach(function (capability) {
                if (vm.isIncludedInRole(capability)) {
                    vm.capabilitiesSet[capability] = true;
                }
            });
        }

        function onToggleCapability(capability) {
            vm.capabilityToAdd = capability;
            vm.isAddingCapability = vm.capabilitiesSet[capability.id];

            $element.find('.confirmCapabilityToggle').openModal({
                dismissible: false,
                yes: function () {
                },
            });
        }

        function toggleCapability() {
            if (vm.isAddingCapability) {
                addCapability(vm.capabilityToAdd);
            } else {
                removeCapability(vm.capabilityToAdd);
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
            })['finally'](function () {
                vm.isBusy = false;
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
            })['finally'](function () {
                vm.isBusy = false;
            });
        }

        function getCapabilitiesList() {
            var result = [];
            Object.keys(vm.availableCapabilities).forEach(function (capability) {
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
