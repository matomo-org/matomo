/*!
 * Matomo - free/libre analytics platform
 *
 * The Heatmaps promotion: the shape where the figure is read from a report and links back
 * to it, alongside a page title entered by a user of the instance.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('ProductPromotionBounceRate', function () {
    this.fixture = 'Piwik\\Plugins\\ProfessionalServices\\tests\\Fixtures\\SiteWithABouncingEntryPage';

    const generalParams = 'idSite=1&period=day&date=2026-01-15';
    const dashboardUrl = '?module=CoreHome&action=index&' + generalParams
        + '#?' + generalParams + '&category=Dashboard_Dashboard&subcategory=1';

    const banner = '.productPromotion';
    const metric = '.productPromotion__metric';

    before(function () {
        testEnvironment.enableProfessionalSupportAdsForUITests = true;
        testEnvironment.save();
    });

    after(function () {
        delete testEnvironment.enableProfessionalSupportAdsForUITests;
        testEnvironment.save();
    });

    it('renders the figure as a link to the report it was read from', async function () {
        await page.goto(dashboardUrl);
        await page.waitForSelector(banner, { timeout: 10000 });
        await page.waitForNetworkIdle();

        const reason = await page.evaluate(
            () => document.querySelector('.productPromotion__reason').textContent.trim()
        );
        expect(reason).to.contain('Heatmaps');

        const href = await page.evaluate((sel) => {
            const node = document.querySelector(sel);
            return node ? node.getAttribute('href') : null;
        }, metric);

        expect(href).to.contain('category=General_Actions');
        expect(href).to.contain('subcategory=Actions_SubmenuPageTitles');
        // The week the figure was read from, so the report shows the same number.
        expect(href).to.contain('period=week');

        // The page title comes from tracked data, so it must arrive escaped, not as markup.
        const text = await page.evaluate(
            () => document.querySelector('.productPromotion__text').innerHTML
        );
        expect(text).to.contain('Pricing');
        expect(text).to.not.contain('<script');
    });

    it('looks like the design', async function () {
        await page.goto(dashboardUrl);
        await page.waitForSelector(banner, { timeout: 10000 });
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(banner)).to.matchImage('promotion_bounce_rate');
    });
});
