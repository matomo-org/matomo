/*!
 * Matomo - free/libre analytics platform
 *
 * Supported browser screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SupportedBrowser", function () {
    const widgetUrl = "?module=Widgetize&action=iframe&containerId=VisitOverviewWithGraph&disableLink=0&widget=1&moduleToWidgetize=CoreHome&actionToWidgetize=renderWidgetContainer&idSite=1&period=range&date=2012-01-12,2012-01-17&disableLink=1&widget=1";
    const generalParams = 'idSite=1&period=year&date=2009-01-04';
    const pageUrl = '?module=CoreHome&action=index&'+generalParams+'#?'+generalParams+'&category=General_Visitors&subcategory=General_Overview';
    const ie10UserAgent = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)";
    const firefoxUserAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 11.2; rv:85.0) Gecko/20100101 Firefox/85.0";

    before(function () {
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.idSitesViewAccess = [1];
        testEnvironment.save();
    });

    after(async () => {
        await page.setUserAgent(page.originalUserAgent);
    });

    it("should load widget when browser supported", async function () {
        page.setUserAgent(firefoxUserAgent);
        await page.goto(widgetUrl);

        // only check that widgetized report is loaded, no need to take a screenshot
        const widget = await page.waitForSelector('.widget-container');
        await page.waitForNetworkIdle();
        expect(widget).to.be.ok;
    });

    it("should load page when browser supported", async function () {
        page.setUserAgent(firefoxUserAgent);
        await page.goto(pageUrl);

        // only check that reporting page is loaded, no need to take a screenshot
        const reportingPage = await page.waitForSelector('.reporting-page');
        await page.waitForNetworkIdle();
        expect(reportingPage).to.be.ok;
    });

    it("should fail load widget when browser not supported", async function () {
        page.setUserAgent(ie10UserAgent);
        await page.goto(widgetUrl);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('widget_load_fails_when_browser_not_supported');
    });

    it("should fail load page when browser not supported", async function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();
        page.setUserAgent(ie10UserAgent);
        await page.goto(pageUrl);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('page_load_fails_when_browser_not_supported');
    });
});
