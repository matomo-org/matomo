/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.service').factory('reportMetadataModel', reportMetadataModel);

    reportMetadataModel.$inject = ['piwik', 'piwikApi'];

    function reportMetadataModel (piwik, piwikApi) {

        var reportsPromise = null;

        var model = {
            reports: [],
            fetchReportMetadata: fetchReportMetadata,
            findReport: findReport
        };

        return model;

        function findReport(module, action)
        {
            var found = [];

            angular.forEach(model.reports, function (report) {
                if (report.module === module && report.action === action) {
                    found = report;
                }
            });

            return found;
        }

        function fetchReportMetadata()
        {
            if (!reportsPromise) {
                reportsPromise = piwikApi.fetch({
                    method: 'API.getReportMetadata',
                    filter_limit: '-1',
                    idSite: piwik.idSite || piwik.broadcast.getValueFromUrl('idSite')
                }).then(function (response) {
                    model.reports = response;
                    return response;
                });
            }

            return reportsPromise;
        }
    }
})();