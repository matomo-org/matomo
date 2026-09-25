/*!
 * Matomo - free/libre analytics platform
 *
 * The Advertising Conversion Export promotion, and the report its figure leads to.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('ProductPromotionCampaign', function () {
    this.fixture = 'Piwik\\Plugins\\ProfessionalServices\\tests\\Fixtures\\SiteWithAConvertingCampaign';

    const generalParams = 'idSite=1&period=day&date=2026-01-15';
    const dashboardUrl = '?module=CoreHome&action=index&' + generalParams
        + '#?' + generalParams + '&category=Dashboard_Dashboard&subcategory=1';

    const banner = '.productPromotion';
    const metric = '.productPromotion__metric';

    // 120 conversions of one goal plus 90 of another: the campaign's total across both.
    const campaignConversions = 210;

    // Matomo lowercases campaign names as it tracks them.
    const campaign = 'qa-campaign';

    before(function () {
        testEnvironment.enableProfessionalSupportAdsForUITests = true;
        testEnvironment.save();
    });

    after(function () {
        delete testEnvironment.enableProfessionalSupportAdsForUITests;
        testEnvironment.save();
    });

    const loadDashboard = async function () {
        await page.goto(dashboardUrl);
        await page.waitForSelector(banner, { timeout: 10000 });
        await page.waitForNetworkIdle();
    };

    it('quotes the campaign and the conversions it drove across every goal', async function () {
        await loadDashboard();

        const text = await page.evaluate(
            () => document.querySelector('.productPromotion__text').textContent,
        );

        expect(text).to.contain(campaign);
        expect(text).to.contain(String(campaignConversions));

        // Neither goal on its own accounts for the figure, so a reader following the link
        // must not be sent to a single goal's report.
        expect(text).to.not.contain('120');
        expect(text).to.not.contain('90 Goal');
    });

    it('sends the reader to a report that shows those conversions, not visits', async function () {
        await loadDashboard();

        const href = await page.evaluate((sel) => {
            const node = document.querySelector(sel);
            return node ? node.getAttribute('href') : null;
        }, metric);

        expect(href).to.contain('category=Referrers_Referrers');
        expect(href).to.contain('subcategory=Referrers_Campaigns');
        // The default campaigns view lists visits; this one lists goal conversions.
        expect(href).to.contain('viewDataTable=tableGoals');

        // Followed the way a reader follows it, rather than by rebuilding the URL.
        await page.click(metric);
        await page.waitForNetworkIdle();

        await page.waitForFunction(
            (name) => !!Array.from(document.querySelectorAll('.dataTable'))
                .find((t) => t.innerText.includes(name)),
            { timeout: 30000 },
            campaign,
        );

        const table = await page.evaluate((name) => Array.from(document.querySelectorAll('.dataTable'))
            .find((t) => t.innerText.includes(name)).innerText, campaign);

        // The campaign is listed with the figure the banner quoted, not with its visits.
        expect(table).to.contain(campaign);
        expect(table).to.contain(String(campaignConversions));
    });
});
