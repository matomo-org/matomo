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
        var fetchedSiteId = null;

        var model = {
            isLoading: false,
            segments : [],
            loadSegments: loadSegments
        };

        return model;

        function loadSegments(siteId) {
            if (model.isLoading) {
                if (limitPromise) {
                    limitPromise.abort();
                    limitPromise = null;
                }
            }

            model.isLoading = true;

            // we need to clear last limit result because we now fetch different data
            if (fetchedSiteId != siteId) {
                limitPromise = null;
                fetchedSiteId = siteId;
            }

            if (!limitPromise) {
                var params = {method: 'API.getSegmentsMetadata',filter_limit: '-1'};

                if (siteId === 'all' || !siteId) {
                    params.idSites = 'all';
                    params.idSite = 'all';
                } else if (siteId) {
                    params.idSites = siteId;
                    params.idSite = siteId;
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