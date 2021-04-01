/*!
 * Matomo - free/libre analytics platform
 *
 * JS tracker UI tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("JSTracker", function () {
    this.fixture = 'Piwik\\Tests\\Fixtures\\JSTrackingUIFixture';

    before(function () {
        testEnvironment.pluginsToLoad = ['CustomJsTracker', 'ExampleTracker'];
        testEnvironment.save();
    });

    const idSiteForTest = 3,
        testWebsiteUrl = 'tests/resources/overlay-test-site-real/index.html',
        generalParams = 'idSite=' + idSiteForTest + '&period=day&date=today',
        widgetizeParams = "module=Widgetize&action=iframe",
        visitorLogUrl = "?" + widgetizeParams + "&" + generalParams + "&moduleToWidgetize=Live&actionToWidgetize=getVisitorLog";

    it("run correctly on a website and correctly track visits in the visitor log", async function () {
        await page.goto(testWebsiteUrl);

        // view another page
        await page.evaluate(() => $('a:contains(Page 3)')[0].click());
        await page.waitForNetworkIdle();
        await page.waitFor(500);
        await page.waitForNetworkIdle();

        // visit visitor log for correct date
        await page.goto(visitorLogUrl);

        await page.evaluate(function () {
            $('.visitor-log-datetime').html('REMOVED');
            var $e = $('.dataTableWrapper>.row>.column>strong');
            $e.text($e.text().replace(/\d+s/, 'Ns'));
        });

        expect(await page.screenshot({ fullPage: true })).to.matchImage('visitor_log');
    });
});