/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot and behaviour tests for the contextual product promotion banner shown above
 * the dashboard widgets.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('ProductPromotion', function () {
    this.fixture = 'Piwik\\Plugins\\ProfessionalServices\\tests\\Fixtures\\SiteWithFiveSegments';

    const generalParams = 'idSite=1&period=day&date=2026-01-15';
    const dashboardUrl = '?module=CoreHome&action=index&' + generalParams
        + '#?' + generalParams + '&category=Dashboard_Dashboard&subcategory=1';

    const banner = '.productPromotion';
    const ctaButton = '.productPromotion__ctaButton';
    const dismiss = '[data-role=dismiss]';
    const trialConfirm = '[data-role=requestTrialConfirm]';

    const textOf = async function (selector) {
        return (await page.evaluate((sel) => {
            const node = document.querySelector(sel);
            return node ? node.textContent.trim() : null;
        }, selector));
    };

    const attributeOf = async function (selector, attribute) {
        return (await page.evaluate((sel, attr) => {
            const node = document.querySelector(sel);
            return node ? node.getAttribute(attr) : null;
        }, selector, attribute));
    };

    const loadDashboard = async function () {
        await page.goto(dashboardUrl);
        await page.waitForSelector(banner, { timeout: 10000 });
        await page.waitForNetworkIdle();
    };

    before(function () {
        // UI tests switch professional-support ads off by default
        // (config/environment/ui-test.php), and the promotion is one of them.
        testEnvironment.enableProfessionalSupportAdsForUITests = true;
        testEnvironment.save();
    });

    after(function () {
        delete testEnvironment.enableProfessionalSupportAdsForUITests;
        delete testEnvironment.idSitesAdminAccess;
        testEnvironment.save();
    });

    it('shows the Custom Reports promotion above the dashboard widgets', async function () {
        await loadDashboard();

        // Priority 1, so no other promotion can take its place. The reason line is the
        // part of the copy that names the product.
        expect(await textOf('.productPromotion__reason')).to.contain('Custom Reports');
        expect(await textOf('.productPromotion__title')).to.not.be.empty;
    });

    it('looks like the design', async function () {
        await loadDashboard();
        expect(await page.screenshotSelector(banner)).to.matchImage('promotion_superuser');
    });

    it('offers the Marketplace link rather than a trial, since a super user needs no trial', async function () {
        await loadDashboard();

        const cta = await page.$(ctaButton);
        const tagName = await (await cta.getProperty('tagName')).jsonValue();

        expect(tagName).to.equal('A');
        expect(await textOf(ctaButton)).to.contain('Custom Reports');
        expect(await page.$(trialConfirm)).to.equal(null);
    });

    it('links the title and the call to action to the campaign tagged Marketplace page', async function () {
        await loadDashboard();

        const href = await attributeOf(ctaButton, 'href');

        expect(href).to.contain('https://plugins.matomo.org/CustomReports');
        expect(href).to.contain('mtm_campaign=app_premiumplugins');
        expect(href).to.contain('mtm_group=triggered_ad');
        expect(href).to.contain('mtm_content=CustomReports');
        expect(href).to.contain('mtm_placement=top_banner');
        expect(href).to.contain('mtm_kwd=segments');

        // The call to action is the only thing that leaves the app, so it opens in a new
        // tab and withholds the referrer. The headline is plain text.
        expect(await attributeOf(ctaButton, 'target')).to.equal('_blank');
        expect(await attributeOf(ctaButton, 'rel')).to.equal('noreferrer noopener');
        expect(await page.$('.productPromotion__titleLink')).to.equal(null);
    });

    // The trial variant of the call to action - the one a non-super-user sees - is not
    // driven here. `canRequestTrial()` is false for a super user, and the mock-auth vars
    // that would demote the session did not take effect against the dashboard's own
    // request in this environment. That branch is covered instead by the directive's
    // vitest spec, which exercises the confirm modal, the request and its failure path
    // (plugins/ProfessionalServices/vue/src/ProductPromotion/ProductPromotion.spec.ts).

    // Last, deliberately: a dismissal starts an 18 day global cooldown for the logged-in
    // user, which would suppress the promotion for every test that ran after it.
    describe('once dismissed', function () {
        it('stays hidden, and stays hidden after a reload', async function () {
            await loadDashboard();

            await page.click(dismiss);
            await page.waitForNetworkIdle();

            expect(await page.$(banner)).to.equal(null);

            // The dismissal is stored per user, so a reload must not bring it back.
            await page.goto(dashboardUrl);
            await page.waitForNetworkIdle();

            expect(await page.$(banner)).to.equal(null);
        });
    });
});
