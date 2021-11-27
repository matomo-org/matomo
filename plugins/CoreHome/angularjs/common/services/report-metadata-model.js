/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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

    }
})();
