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
            if ($location.path() != '/') {
                changePathToSearch();
            }

            $rootScope.$on('$locationChangeSuccess', function () {
                loadCurrentPage();
            });

            loadCurrentPage();
        }

        // currently, the AJAX content URL is stored in $location.search(), but before it was stored in $location.path().
        // this function makes sure URLs like http://piwik.net/?...#/module=Whatever&action=whatever still work.
        function changePathToSearch() {
            var path = $location.path();
            if (!path
                || path == '/'
            ) {
                return;
            }

            var searchParams = broadcast.getValuesFromUrl('?' + path.substring(1));
            // NOTE: we don't need to decode the parameters since $location.path() will decode the string itself

            $location.search(searchParams);
            $location.path('');
        }

        function loadCurrentPage() {
            var searchObject = $location.search(),
                searchString = [];
            for (var name in searchObject) {
                if (!searchObject.hasOwnProperty(name) || name == '_') {
                    continue;
                }

                // if more than one query parameter of the same name is supplied, angular will return all of them as
                // an array. we only want to use the last one, though.
                if (searchObject[name] instanceof Array) {
                    searchObject[name] = searchObject[name][searchObject[name].length - 1];
                }

                var value = searchObject[name];
                if (name != 'columns') { // the columns query parameter is not urldecoded in PHP code. TODO: this should be fixed in 3.0
                    value = encodeURIComponent(value);
                }

                searchString.push(name + '=' + value);
            }
            searchString = searchString.join('&');

            // the location hash will have a / prefix, which broadcast.pageload doesn't want
            broadcast.pageload(searchString);
        }

        function load(hash) {
            // make sure the hash is just the query parameter values, w/o a starting #, / or ? char. broadcast.pageload & $location.path should get neither
            hash = normalizeHash(hash);

            var currentHash = normalizeHash(location.hash);
            if (currentHash === hash) {
                loadCurrentPage(); // it would not trigger a location change success event as URL is the same, call it manually
            } else if (hash) {
                $location.search(hash);
            } else {
                // NOTE: this works around a bug in angularjs. when unsetting the hash (ie, removing in the URL),
                // angular will enter an infinite loop of digests. this is because $locationWatch will trigger
                // $locationChangeStart if $browser.url() != $location.absUrl(), and $browser.url() will contain
                // the '#' character and $location.absUrl() will not. so the watch continues to trigger the event.
                $location.search('_=');
            }

            setTimeout(function () { $rootScope.$apply(); }, 1);
        }

        function normalizeHash(hash) {
            var chars = ['#', '/', '?'];
            for (var i = 0; i != chars.length; ++i) {
                var charToRemove = chars[i];
                if (hash.charAt(0) == charToRemove) {
                    hash = hash.substring(1);
                }
            }
            return hash;
        }
    }
})(window, jQuery, broadcast);
