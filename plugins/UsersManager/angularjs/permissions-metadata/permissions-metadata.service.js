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
    angular.module('piwikApp').factory('permissionsMetadataService', PermissionsMetadataService);

    PermissionsMetadataService.$inject = ['piwikApi', '$q'];

    function PermissionsMetadataService(piwikApi, $q) {
        var allCapabilities;

        return {
            getAllCapabilities: function () {
                if (allCapabilities) {
                    return $q.when(allCapabilities);
                }

                return piwikApi.fetch({
                    method: 'UsersManager.getAvailableCapabilities',
                }).then(function (capabilities) {
                    allCapabilities = capabilities;
                    return allCapabilities;
                });
            },
        };
    }
})();
