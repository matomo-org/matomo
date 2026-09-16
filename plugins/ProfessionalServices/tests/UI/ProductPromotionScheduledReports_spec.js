/*!
 * Matomo - free/libre analytics platform
 *
 * The second Custom Reports promotion: same product as the segments one, different trigger
 * and different copy.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('ProductPromotionScheduledReports', function () {
    this.fixture = 'Piwik\\Plugins\\ProfessionalServices\\tests\\Fixtures\\SiteWithScheduledReports';

    const generalParams = 'idSite=1&period=day&date=2026-01-15';
    const dashboardUrl = '?module=CoreHome&action=index&' + generalParams
        + '#?' + generalParams + '&category=Dashboard_Dashboard&subcategory=1';

    const banner = '.productPromotion';

    before(function () {
        testEnvironment.enableProfessionalSupportAdsForUITests = true;
        testEnvironment.save();
    });

    after(function () {
        delete testEnvironment.enableProfessionalSupportAdsForUITests;
        testEnvironment.save();
    });

    it('shows the scheduled reports promotion and looks like the design', async function () {
        await page.goto(dashboardUrl);
        await page.waitForSelector(banner, { timeout: 10000 });
        await page.waitForNetworkIdle();

        const reason = await page.evaluate(
            () => document.querySelector('.productPromotion__reason').textContent.trim()
        );
        expect(reason).to.contain('Custom Reports');

        // Instance state rather than a report, so the figure is plain text, not a link.
        expect(await page.$('.productPromotion__metric')).to.equal(null);

        expect(await page.screenshotSelector(banner)).to.matchImage('promotion_scheduled_reports');
    });
});
