/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * TODO
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
            // the location hash will have a #? prefix, which broadcast.pageload doesn't want
            broadcast.pageload(window.location.hash.substring(2));
        }

        function load(hash) {
            // make sure the hash is just the query parameter values, w/o a starting #, / or ? char. broadcast.pageload & $location.search should get neither
            ['#', '/', '?'].forEach(function (char) {
                if (hash.charAt(0) == char) {
                    hash = hash.substring(1);
                }
            });

            $location.search(hash);
            broadcast.pageload(hash);
        }
    }
})(window, jQuery, broadcast);