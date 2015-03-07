/*!
 * Piwik - free/libre analytics platform
 *
 * Bar graph screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("BarGraph", function () {
    this.timeout(0);

    var url = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
            + "actionToWidgetize=getKeywords&viewDataTable=graphVerticalBar&isFooterExpandedInDashboard=1";

    it("should load correctly", function (done) {
        expect.screenshot("load").to.be.capture(function (page) {
            page.load(url);
        }, done);
    });

    it("should display the metric picker on hover of metric picker icon", function (done) {
        expect.screenshot('metric_picker_shown').to.be.capture(function (page) {
            page.mouseMove('.jqplot-seriespicker');
        }, done);
    });

    it("should display multiple metrics when another metric picked", function (done) {
        expect.screenshot('other_metric').to.be.capture(function (page) {
            page.click('.jqplot-seriespicker-popover input:not(:checked)');
        }, done);
    });
});