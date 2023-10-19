/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ProfessionalServices_PluginPromo", function () {
    this.timeout(0);

    var generalParams = 'idSite=1&period=day&date=2017-01-02',
        urlBase = '?module=CoreHome&' + generalParams + '&action=index&';

    before(function () {
        testEnvironment.configOverride.General = {piwik_professional_support_ads_enabled: "1"};
        testEnvironment.pluginsToLoad = ['PrivacyManager', 'Marketplace', 'ProfessionalServices'];
        testEnvironment.save();
    });

    it('should load A/B Testing plugin promo detail view', async function() {
        const category = 'ProfessionalServices_PromoAbTesting';
        const subcategory = 'ProfessionalServices_PromoOverview';

        await page.goto(urlBase + 'category=' + category + '&subcategory=' + subcategory);
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.pluginPromo')).to.matchImage('promo_abtesting');
    });

    it('should load Crash Analytics plugin promo detail view', async function() {
        const category = 'ProfessionalServices_PromoCrashAnalytics';
        const subcategory = 'ProfessionalServices_PromoOverview';

        await page.goto(urlBase + 'category=' + category + '&subcategory=' + subcategory);
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.pluginPromo')).to.matchImage('promo_crashanalytics');
    });

    it('should load Custom Reports plugin promo detail view', async function() {
        const category = 'ProfessionalServices_PromoCustomReports';
        const subcategory = 'ProfessionalServices_PromoManage';

        await page.goto(urlBase + 'category=' + category + '&subcategory=' + subcategory);
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.pluginPromo')).to.matchImage('promo_customreports');
    });

    it('should load Form Analytics plugin promo detail view', async function() {
        const category = 'ProfessionalServices_PromoFormAnalytics';
        const subcategory = 'ProfessionalServices_PromoOverview';

        await page.goto(urlBase + 'category=' + category + '&subcategory=' + subcategory);
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.pluginPromo')).to.matchImage('promo_formanalytics');
    });

    it('should load Funnels plugin promo detail view', async function() {
        const category = 'ProfessionalServices_PromoFunnels';
        const subcategory = 'ProfessionalServices_PromoOverview';

        await page.goto(urlBase + 'category=' + category + '&subcategory=' + subcategory);
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.pluginPromo')).to.matchImage('promo_funnels');
    });

    it('should load Heatmaps plugin promo detail view', async function() {
        const category = 'ProfessionalServices_PromoHeatmaps';
        const subcategory = 'ProfessionalServices_PromoManage';

        await page.goto(urlBase + 'category=' + category + '&subcategory=' + subcategory);
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.pluginPromo')).to.matchImage('promo_heatmaps');
    });

    it('should load Media Analytics plugin promo detail view', async function() {
        const category = 'ProfessionalServices_PromoMediaAnalytics';
        const subcategory = 'ProfessionalServices_PromoOverview';

        await page.goto(urlBase + 'category=' + category + '&subcategory=' + subcategory);
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.pluginPromo')).to.matchImage('promo_mediaanalytics');
    });

    it('should load Session Recordings plugin promo detail view', async function() {
        const category = 'ProfessionalServices_PromoSessionRecording';
        const subcategory = 'ProfessionalServices_PromoManage';

        await page.goto(urlBase + 'category=' + category + '&subcategory=' + subcategory);
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.pluginPromo')).to.matchImage('promo_sessionrecordings');
    });

});
