/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').factory('segmentGeneratorModel', segmentGeneratorModel);

    segmentGeneratorModel.$inject = ['piwikApi'];

    function segmentGeneratorModel(piwikApi) {

        var initialSegments = null;
        var limitPromise = null;

        var model = {
            isLoading: false,
            segments : [],
            loadSegments: loadSegments
        };

        return model;

        function loadSegments() {

            if (model.isLoading) {
                if (limitPromise) {
                    limitPromise.abort();
                    limitPromise = null;
                }
            }

            model.isLoading = true;

            if (!limitPromise) {
                limitPromise = piwikApi.fetch({method: 'API.getSegmentsMetadata', filter_limit: '-1'});
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