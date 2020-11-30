/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.service').service('piwikUrl', piwikUrl);

    piwikUrl.$inject = ['$location', '$window'];

    /**
     * Similar to angulars $location but works around some limitation. Use it if you need to access search params
     */
    function piwikUrl($location, $window) {

        var model = {
            getSearchParam: getSearchParam
        };

        return model;

        function getSearchParam(paramName)
        {
            var hash = $window.location.href.split('#');
            if (hash && hash[1] && (new RegExp(paramName + '(\\[]|=)')).test(decodeURIComponent(hash[1]))) {
                var valueFromHash = broadcast.getValueFromHash(paramName, $window.location.href);

                // for date, period and idsite fall back to parameter from url, if non in hash was provided
                if (valueFromHash || (paramName !== 'date' && paramName !== 'period' && paramName !== 'idSite')) {
                    return valueFromHash;
                }

            }
            return broadcast.getValueFromUrl(paramName, $window.location.search);
        }
    }
})();
