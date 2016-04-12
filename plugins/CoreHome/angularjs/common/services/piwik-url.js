/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.service').service('piwikUrl', piwikUrl);

    piwikUrl.$inject = ['$location', 'piwik'];

    /**
     * Similar to angulars $location but works around some limitation. Use it if you need to access search params
     */
    function piwikUrl($location, piwik) {

        var model = {
            getSearchParam: getSearchParam
        }

        return model;

        function getSearchParam(paramName)
        {
            if (paramName === 'segment') {
                var hash = window.location.href.split('#');
                if (hash && hash[1]) {
                    return piwik.broadcast.getValueFromHash(paramName, hash[1]);
                }

                return broadcast.getValueFromUrl(paramName);
            }

            // available in global scope
            var search = $location.search();

            if (!search[paramName]) {
                // see https://github.com/angular/angular.js/issues/7239 (issue is resolved but problem still exists)
                search[paramName] = piwik.broadcast.getValueFromUrl(paramName);
            }

            if (search[paramName]) {
                var value =  search[paramName];

                if (angular.isArray(search[paramName])) {
                    // use last one. Eg when having period=day&period=year angular would otherwise return ['day', 'year']
                    return value[value.length - 1];
                }

                return value;
            }
        }
    }
})();