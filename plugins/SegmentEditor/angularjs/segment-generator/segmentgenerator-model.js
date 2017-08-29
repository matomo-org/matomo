/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').factory('segmentGeneratorModel', segmentGeneratorModel);

    segmentGeneratorModel.$inject = ['piwikApi', 'piwik'];

    function segmentGeneratorModel(piwikApi, piwik) {

        var initialSegments = null;
        var limitPromise = null;
        var fetchedSiteSpecific = null;

        var model = {
            isLoading: false,
            segments : [],
            loadSegments: loadSegments
        };

        return model;

        function loadSegments(siteSpecific) {
            if (model.isLoading) {
                if (limitPromise) {
                    limitPromise.abort();
                    limitPromise = null;
                }
            }

            model.isLoading = true;

            // we need to clear last limit result because we now fetch different data
            if (fetchedSiteSpecific != siteSpecific) {
                limitPromise = null;
                fetchedSiteSpecific = siteSpecific;
            }

            if (!limitPromise) {
                var params = {method: 'API.getSegmentsMetadata',filter_limit: '-1'};

                if (siteSpecific) {
                    params.idSites = (piwik.idSite || piwik.broadcast.getValueFromUrl('idSite'));
                }

                limitPromise = piwikApi.fetch(params);
            }

            return limitPromise.then(function (response) {
                model.isLoading = false;

                if (angular.isDefined(response)) {
                    model.segments = response;
                }

                return response;
            }).finally(function () {
                model.isLoading = false;
            });
        }
    }
})();