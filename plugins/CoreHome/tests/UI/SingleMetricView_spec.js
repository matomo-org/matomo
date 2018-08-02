/*!
 * Piwik - free/libre analytics platform
 *
 * Dashboard screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('SingleMetricView', function () {
    this.timeout(0);

    var url = "?module=Widgetize&action=iframe&idSite=1&period=year&date=2012-08-09&moduleToWidgetize=Dashboard&"
        + "actionToWidgetize=index&idDashboard=5";
    var rangeUrl = "?module=Widgetize&action=iframe&idSite=1&period=range&date=2012-08-07,2012-08-10&moduleToWidgetize=Dashboard&"
        + "actionToWidgetize=index&idDashboard=5";

    it('should load correctly', function (done) {
        expect.screenshot("loaded").to.be.captureSelector('#widgetCoreVisualizationssingleMetricViewcolumn', function (page) {
            page.load(url, 5000);
            page.click('.dashboard-manager a.title');

            page.mouseMove('.widgetpreview-categorylist>li:contains(Live!)'); // have to mouse move twice... otherwise Live! will just be highlighted
            page.mouseMove('.widgetpreview-categorylist > li:contains(Generic)');

            page.mouseMove('.widgetpreview-widgetlist li:contains(Metric)');
            page.click('.widgetpreview-widgetlist li:contains(Metric)');
        }, done);
    });

    it('should handle formatted metrics properly', function (done) {
        expect.screenshot("formatted_metric").to.be.captureSelector('#widgetCoreVisualizationssingleMetricViewcolumn', function (page) {
            page.mouseMove('#widgetCoreVisualizationssingleMetricViewcolumn .single-metric-view-picker');
            page.click('.jqplot-seriespicker-popover label:contains(Revenue)');
        }, done);
    });

    it('should handle range periods correctly', function (done) {
        expect.screenshot("range").to.be.captureSelector('#widgetCoreVisualizationssingleMetricViewcolumn', function (page) {
            page.load(rangeUrl, 8000);
        }, done);
    });
});
