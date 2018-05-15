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

    var tokenAuth = "9ad1de7f8b329ab919d854c556f860c1", // md5('superUserLogin' . md5('superUserPass'))
        url = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
            + "actionToWidgetize=getKeywords&viewDataTable=graphVerticalBar&isFooterExpandedInDashboard=1&"
            + "token_auth=" + tokenAuth;

    before(function () {
        // use real auth + token auth to test that auth works when widgetizing reports in an iframe
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();
    });

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
            page.click('.jqplot-seriespicker-popover input:not(:checked):first + label');
        }, done);
    });
});