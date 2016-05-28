/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ExportWidgetController', ExportWidgetController);

    ExportWidgetController.$inject = ['piwik', '$window'];

    function ExportWidgetController(piwik, $window) {

        function getIframeCode(iframeUrl)
        {
            var url = iframeUrl.replace(/"/g, '&quot;');

            return '<iframe src="' + url + '" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="100%"></iframe>';
        }

        // remember to keep controller very simple. Create a service/factory (model) if needed
        var urlPath = $window.location.protocol + '//' + $window.location.hostname + ($window.location.port == '' ? '' : (':' + $window.location.port)) + $window.location.pathname;
        this.dashboardUrl = urlPath + '?module=Widgetize&action=iframe&moduleToWidgetize=Dashboard&actionToWidgetize=index&idSite=' + piwik.idSite + '&period=week&date=yesterday';
        this.dashboardCode = getIframeCode(this.dashboardUrl);

        this.allWebsitesDashboardUrl = urlPath + '?module=Widgetize&action=iframe&moduleToWidgetize=MultiSites&actionToWidgetize=standalone&idSite=' + piwik.idSite + '&period=week&date=yesterday';
        this.allWebsitesDashboardCode = getIframeCode(this.allWebsitesDashboardUrl);
    }
})();