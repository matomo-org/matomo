/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("DeactivatedFeatures", function () {

    afterEach(async function () {
        await setFeatures(1, 1, 1);
    });

    async function setFeatures(idSite, vLog, vProfile) {
        await testEnvironment.callApi("SitesManager.updateSite", {
            idSite: idSite, settingValues: {
                Live: [
                    {name: 'activate_visitor_log', value: vLog},
                    {name: 'activate_visitor_profile', value: vProfile},
                ]
            }
        });
    }

    it('menu should contain visits log when enabled', async function () {
        await setFeatures(1, 1, 0);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2009-01-04#?idSite=1&period=year&date=2009-01-04&category=General_Visitors&subcategory=General_Overview");
        await page.waitFor('#secondNavBar', {visible: true});

        const element = await page.$('#secondNavBar .navbar a[href*="Live_VisitorLog"]');
        expect(element).to.be.ok;
    });

    it('menu should not contain visits log when deactivated', async function () {
        await setFeatures(1, 0, 0);
        await page.reload();
        await page.waitFor('#secondNavBar', {visible: true});

        const element = await page.$('#secondNavBar .navbar a[href*="Live_VisitorLog"]');
        expect(element).to.be.not.ok;
    });

    it('it should not show visits log, when opened directly but disabled', async function () {
        await setFeatures(1, 0, 0);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2009-08-09#?idSite=1&period=year&date=2009-08-09&category=General_Visitors&subcategory=Live_VisitorLog");
        await page.waitForNetworkIdle();

        expect(await page.getWholeCurrentUrl()).to.not.match(/Live_VisitorLog/); // page should be redirected to next subcategory
    });

    it('menu should contain ecommerce log when visits log enabled', async function () {
        await setFeatures(1, 1, 0);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2009-01-04#?idSite=1&period=year&date=2009-01-04&category=Goals_Ecommerce&subcategory=Goals_EcommerceLog");
        await page.waitFor('#secondNavBar', {visible: true});

        const element = await page.$('#secondNavBar .navbar a[href*="Goals_EcommerceLog"]');
        expect(element).to.be.ok;
    });

    it('menu should not contain ecommerce log when visits log deactivated', async function () {
        await setFeatures(1, 0, 0);
        await page.reload();
        await page.waitFor('#secondNavBar', {visible: true});

        const element = await page.$('#secondNavBar .navbar a[href*="Goals_EcommerceLog"]');
        expect(element).to.be.not.ok;
    });

    it('it should not show ecommerce log, when opened directly but disabled', async function () {
        await setFeatures(1, 0, 0);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2009-08-09#?idSite=1&period=year&date=2009-08-09&category=Goals_Ecommerce&subcategory=Goals_EcommerceLog");
        await page.waitForNetworkIdle();

        expect(await page.getWholeCurrentUrl()).to.not.match(/Goals_EcommerceLog/); // page should be redirected to next subcategory
    });

    it('it should show profile link in visits log when enabled', async function () {
        await setFeatures(1, 1, 1);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09#?idSite=1&period=year&date=2012-08-09&category=General_Visitors&subcategory=Live_VisitorLog");
        await page.waitFor('.dataTableVizVisitorLog');

        const element = await page.$('.dataTableVizVisitorLog .card .visitor-log-visitor-profile-link');
        expect(element).to.be.ok;
    });

    it('it should not show profile link in visits log when disabled', async function () {
        await setFeatures(1, 1, 0);
        await page.reload();
        await page.waitFor('.dataTableVizVisitorLog');

        const element = await page.$('.dataTableVizVisitorLog .card .visitor-log-visitor-profile-link');
        expect(element).to.be.not.ok;
    });

    it('it should show segmented visits log row action when enabled', async function () {
        await setFeatures(1, 1, 0);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-08#?idSite=1&period=year&date=2012-08-08&category=General_Visitors&subcategory=DevicesDetection_Software");
        await page.waitForNetworkIdle();

        await (await page.jQuery('#widgetDevicesDetectiongetOsVersions td.label:first')).hover();
        await page.waitFor('#widgetDevicesDetectiongetOsVersions .dataTableRowActions', {visible: true});

        const element = await page.$('#widgetDevicesDetectiongetOsVersions .dataTableRowActions .actionSegmentVisitorLog');
        expect(element).to.be.ok;
    });

    it('it should not show segmented visits log row action when disabled', async function () {
        await setFeatures(1, 0, 0);
        await page.reload();
        await page.waitForNetworkIdle();

        await (await page.jQuery('#widgetDevicesDetectiongetOsVersions td.label:first')).hover();
        await page.waitFor('#widgetDevicesDetectiongetOsVersions .dataTableRowActions', {visible: true});

        const element = await page.$('#widgetDevicesDetectiongetOsVersions .dataTableRowActions .actionSegmentVisitorLog');
        expect(element).to.be.not.ok;
    });

    it('widget list should contain log and profile when enabled', async function () {
        await setFeatures(1, 1, 1);
        await page.goto("?module=Widgetize&action=index&idSite=1&period=day&date=yesterday");
        await page.waitForNetworkIdle();

        await (await page.jQuery('.widgetpreview-categorylist li:contains("Visitors"):first')).hover();
        await page.waitFor('.widgetpreview-widgetlist', {visible: true});

        const profile = await page.$('.widgetpreview-widgetlist [uniqueid=widgetLivegetVisitorProfilePopup]');
        expect(profile).to.be.ok;

        const log = await page.$('.widgetpreview-widgetlist [uniqueid=widgetLivegetLastVisitsDetailsforceView1viewDataTableVisitorLogsmall1]');
        expect(log).to.be.ok;
    });

    it('widget list should not contain log and profile when disabled', async function () {
        await setFeatures(1, 0, 0);
        await page.goto("?module=Widgetize&action=index&idSite=1&period=day&date=yesterday");
        await page.waitForNetworkIdle();

        await (await page.jQuery('.widgetpreview-categorylist li:contains("Visitors"):first')).hover();
        await page.waitFor('.widgetpreview-widgetlist', {visible: true});

        const profile = await page.$('.widgetpreview-widgetlist [uniqueid=widgetLivegetVisitorProfilePopup]');
        expect(profile).to.be.not.ok;

        const log = await page.$('.widgetpreview-widgetlist [uniqueid=widgetLivegetLastVisitsDetailsforceView1viewDataTableVisitorLogsmall1]');
        expect(log).to.be.not.ok;
    });

});