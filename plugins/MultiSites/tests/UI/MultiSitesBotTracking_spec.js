/*!
 * Matomo - free/libre analytics platform
 *
 * MultiSites BotTracking screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("MultiSitesBotTracking", function () {
    this.fixture = "Piwik\\Plugins\\BotTracking\\tests\\Fixtures\\BotTraffic";

    const generalParams = 'idSite=1&period=range&date=2025-02-02,2025-02-06';
    const widgetizeParams = 'module=Widgetize&action=iframe';
    const widgetizedMultiSites = `?${widgetizeParams}&${generalParams}&moduleToWidgetize=MultiSites&actionToWidgetize=standalone`;

    before(function () {
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.pluginsToLoad = ['BotTracking'];
        testEnvironment.save();
    });

    it('should show AI chatbot metrics when bot tracking data exists', async function () {
        await page.goto(widgetizedMultiSites);
        await page.waitForNetworkIdle();
        await page.waitForSelector('.kpiCard');

        expect(await page.screenshot({fullPage: true})).to.matchImage('multisites_bottracking_ai_metrics');
    });

    it('should show segmentation warnings for AI chatbot metrics', async function () {
        const segmentedUrl = `${widgetizedMultiSites}&segment=browserCode%3D%3DFF`;

        await page.goto(segmentedUrl);
        await page.waitForNetworkIdle();
        await page.waitForSelector('.kpiCard');

        const aiKpiData = await page.evaluate(() => {
            const cards = Array.from(document.querySelectorAll('.kpiCard'));
            const card = cards.find((node) => {
                const title = node.querySelector('.kpiCardTitle');
                return title && title.textContent && title.textContent.includes('Total AI Chatbots');
            });

            if (!card) {
                return null;
            }

            return {
                value: card.querySelector('.kpiCardValue')?.textContent?.trim() || null,
                badge: card.querySelector('.kpiCardBadge')?.textContent?.trim() || null,
            };
        });

        expect(aiKpiData).to.not.equal(null);
        expect(aiKpiData.value).to.equal('-');
        expect(aiKpiData.badge).to.equal('Segmentation is not supported');

        expect(await page.screenshot({fullPage: true})).to.matchImage('multisites_bottracking_ai_metrics_segmented');
    });
});
