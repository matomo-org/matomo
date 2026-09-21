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
    const ctaButton = '.productPromotion__ctaButton';

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

    it('stays readable and takes back the space when the artwork fails to load', async function () {
        await page.goto(dashboardUrl);
        await page.waitForSelector(banner, { timeout: 10000 });

        // Point the image at something that cannot load, the way blocking the request in
        // DevTools would.
        await page.evaluate(() => {
            const img = document.querySelector('.productPromotion__image');
            img.src = 'plugins/ProfessionalServices/images/this-file-does-not-exist.png';
        });

        await page.waitForSelector('.productPromotion--noFigure', { timeout: 10000 });
        await page.waitForNetworkIdle();

        // No empty panel and no broken-image icon left behind.
        const figureVisible = await page.evaluate(() => {
            const figure = document.querySelector('.productPromotion__figure');
            return figure ? figure.getBoundingClientRect().width > 0 : false;
        });
        expect(figureVisible).to.equal(false);

        // Everything the reader needs is still there and still usable.
        expect(await page.$('.productPromotion__title')).to.not.equal(null);
        expect(await page.$('.productPromotion__metric')).to.not.equal(null);
        expect(await page.$('.productPromotion__reason')).to.not.equal(null);
        expect(await page.$(ctaButton)).to.not.equal(null);
        expect(await page.$('[data-role=dismiss]')).to.not.equal(null);

        await page.evaluate(() => document.fonts.ready);
        expect(await page.screenshotSelector(banner)).to.matchImage('promotion_without_artwork');
    });

    it('looks like the design', async function () {
        await page.goto(dashboardUrl);
        await page.waitForSelector(banner, { timeout: 10000 });
        await page.waitForNetworkIdle();

        // Wait for webfonts before capturing: the screenshot is otherwise sometimes taken
        // with the fallback face still in use, which changes every glyph and makes the
        // comparison fail for a reason that has nothing to do with the banner.
        await page.evaluate(() => document.fonts.ready);
        expect(await page.screenshotSelector(banner)).to.matchImage('promotion_bounce_rate');
    });
});
