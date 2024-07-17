/*!
 * Matomo - free/libre analytics platform
 *
 * Seo widget screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SeoWidgetTest", function () {

    this.fixture = 'Piwik\\Tests\\Fixtures\\OneVisitorTwoVisits';

    this.timeout(5*60*1000); // timeout of 5 minutes per test

    var url = 'index.php?module=Widgetize&action=iframe&disableLink=0&widget=1&moduleToWidgetize=SEO&actionToWidgetize=getRank&idSite=1&period=day&date=yesterday&disableLink=1&widget=1&url=matomo.org';

    it("should load correctly", async function() {
        await page.webpage.setViewport({
            width: 500,
            height: 500
        });
        await page.goto(url);
        await page.evaluate(function () {
            $('td:last div').text('3 years 78 days');
        });
        expect(await page.screenshotSelector('.widget')).to.matchImage('widget');
    });
});
