/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("BotTracking", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\BotTracking\\tests\\Fixtures\\BotTraffic";

    var generalParams = 'idSite=1&period=day&date=2025-02-02',
        urlBase = 'module=CoreHome&action=index&' + generalParams;

    it('should render AI Assistants > AI Bots Overview page with evolution and sparkline', async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_AIAssistants&subcategory=BotTracking_AIBotsOverview");
        await page.waitForNetworkIdle();

        await page.hover('.jqplot-seriespicker');

        const availableMetrics = await page.$$('.jqplot-seriespicker input.select');
        expect(availableMetrics.length).to.equal(8);

        await page.mouse.move(0, 0);

        const sparklines = await page.$$('.sparkline-metrics');
        expect(sparklines.length).to.equal(8);

        var elem = await page.$('.pageWrap');
        expect(await elem.screenshot()).to.matchImage('bot_overview');
    });

    it('should not show unique pages and documents metric for higher periods', async function () {
        await page.goto("?" + urlBase + "#?idSite=1&period=week&date=2025-02-02&category=General_AIAssistants&subcategory=BotTracking_AIBotsOverview");
        await page.waitForNetworkIdle();

        await page.hover('.jqplot-seriespicker');

        const availableMetrics = await page.$$('.jqplot-seriespicker input.select');
        expect(availableMetrics.length).to.equal(6);

        const sparklines = await page.$$('.sparkline-metrics');
        expect(sparklines.length).to.equal(6);
    });

    it('should render AI Assistants > AI Bots Overview bot detail report', async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_AIAssistants&subcategory=BotTracking_AIBotsOverview");
        await page.waitForNetworkIdle();

        const row = await page.jQuery('tr.subDataTable:first');
        await row.click();
        await page.mouse.move(-10, -10);

        await page.waitForNetworkIdle();
        await page.waitForTimeout(250); // rendering

        var elem = await page.$('#widgetBotTrackinggetAIAssistantRequests');
        expect(await elem.screenshot()).to.matchImage('bot_requests');
    });

    it('should switch to secondary dimension when clicked', async function () {
        await page.evaluate(() => $('.datatableRelatedReports li span:contains("Document Requests")').click());
        await page.waitForNetworkIdle();

        const row = await page.jQuery('tr.subDataTable:first');
        await row.click();
        await page.mouse.move(-10, -10);

        await page.waitForNetworkIdle();
        await page.waitForTimeout(250); // rendering

        var elem = await page.$('#widgetBotTrackinggetAIAssistantRequests');
        expect(await elem.screenshot()).to.matchImage('bot_requests_documents');
    });
});
