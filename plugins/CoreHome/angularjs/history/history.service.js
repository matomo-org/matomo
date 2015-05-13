/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * History service. Provides the ability to change the window hash, and makes sure broadcast.pageload
 * is called on every change.
 *
 * This service replaces the previously used jQuery history extension.
 *
 * Should only be used by the broadcast object.
 */
(function (window, $, broadcast) {
    angular.module('piwikApp').service('historyService', historyService);

    historyService.$inject = ['$location', '$rootScope'];

    function historyService($location, $rootScope) {
        var service = {};
        service.load = load;
        service.init = init;
        return service;

        function init() {
            $rootScope.$on('$locationChangeSuccess', function () {
                loadCurrentPage();
            });

            loadCurrentPage();
        }

        function loadCurrentPage() {
            // the location hash will have a / prefix, which broadcast.pageload doesn't want
            broadcast.pageload($location.path().substring(1));
        }

        function load(hash) {
            // make sure the hash is just the query parameter values, w/o a starting #, / or ? char. broadcast.pageload & $location.path should get neither
            var chars = ['#', '/', '?'];
            for (var i = 0; i != chars.length; ++i) {
                var charToRemove = chars[i];
                if (hash.charAt(0) == charToRemove) {
                    hash = hash.substring(1);
                }
            }

            $location.path(hash);

            setTimeout(function () { $rootScope.$apply(); }, 1);
        }
    }
})(window, jQuery, broadcast);