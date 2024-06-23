/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("DeactivatedFeatures", function () {

    afterEach(async function () {
        await setFeatures(1, 0, 0);
        if (testEnvironment.configOverride.Live) {
            delete testEnvironment.configOverride.Live;
            await testEnvironment.save();
        }
    });

    after(async function () {
        await setFeatures(1, 0, 0);
        if (testEnvironment.configOverride.Live) {
            delete testEnvironment.configOverride.Live;
            await testEnvironment.save();
        }
    });


    async function setFeatures(idSite, vLog, vProfile) {
        await testEnvironment.callApi("SitesManager.updateSite", {
            idSite: idSite, settingValues: {
                Live: [
                    {name: 'disable_visitor_log', value: vLog},
                    {name: 'disable_visitor_profile', value: vProfile},
                ]
            }
        });
    }

    async function setConfig(vLog, vProfile) {
        testEnvironment.overrideConfig('Live', 'disable_visitor_log', vLog);
        testEnvironment.overrideConfig('Live', 'disable_visitor_profile', vProfile);
        await testEnvironment.save();
    }

    // test measurable setting

    it('menu should contain visits log & realtime when enabled', async function () {
        await setFeatures(1, 0, 1);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2009-01-04#?idSite=1&period=year&date=2009-01-04&category=General_Visitors&subcategory=General_Overview");
        await page.waitForSelector('#secondNavBar', {visible: true});

        const vlog = await page.$('#secondNavBar .navbar a[href*="Live_VisitorLog"]');
        expect(vlog).to.be.ok;

        const realtime = await page.$('#secondNavBar .navbar a[href*="General_RealTime"]');
        expect(realtime).to.be.ok;

        const realtimemap = await page.$('#secondNavBar .navbar a[href*="UserCountryMap_RealTimeMap"]');
        expect(realtimemap).to.be.ok;
    });

    it('menu should not contain visits log & realtime when deactivated', async function () {
        await setFeatures(1, 1, 1);
        await page.reload();
        await page.waitForSelector('#secondNavBar', {visible: true});

        const vlog = await page.$('#secondNavBar .navbar a[href*="Live_VisitorLog"]');
        expect(vlog).to.be.not.ok;

        const realtime = await page.$('#secondNavBar .navbar a[href*="General_RealTime"]');
        expect(realtime).to.be.not.ok;

        const realtimemap = await page.$('#secondNavBar .navbar a[href*="UserCountryMap_RealTimeMap"]');
        expect(realtimemap).to.be.not.ok;
    });

    it('it should not show visits log, when opened directly but disabled', async function () {
        await setFeatures(1, 1, 1);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2009-08-09#?idSite=1&period=year&date=2009-08-09&category=General_Visitors&subcategory=Live_VisitorLog");
        await page.waitForNetworkIdle();

        expect(await page.getWholeCurrentUrl()).to.not.match(/Live_VisitorLog/); // page should be redirected to next subcategory
    });

    it('it should not show realtime, when opened directly but disabled', async function () {
        await setFeatures(1, 1, 1);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2009-08-09#?idSite=1&period=year&date=2009-08-09&category=General_Visitors&subcategory=General_RealTime");
        await page.waitForNetworkIdle();

        expect(await page.getWholeCurrentUrl()).to.not.match(/General_RealTime/); // page should be redirected to next subcategory
    });

    it('it should not show realtime map, when opened directly but disabled', async function () {
        await setFeatures(1, 1, 1);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2009-08-09#?idSite=1&period=year&date=2009-08-09&category=General_Visitors&subcategory=UserCountryMap_RealTimeMap");
        await page.waitForNetworkIdle();

        expect(await page.getWholeCurrentUrl()).to.not.match(/UserCountryMap_RealTimeMap/); // page should be redirected to next subcategory
    });

    it('menu should contain ecommerce log when visits log enabled', async function () {
        await setFeatures(1, 0, 1);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2009-01-04#?idSite=1&period=year&date=2009-01-04&category=Goals_Ecommerce&subcategory=Goals_EcommerceLog");
        await page.waitForSelector('#secondNavBar', {visible: true});

        const element = await page.$('#secondNavBar .navbar a[href*="Goals_EcommerceLog"]');
        expect(element).to.be.ok;
    });

    it('menu should not contain ecommerce log when visits log deactivated', async function () {
        await setFeatures(1, 1, 1);
        await page.reload();
        await page.waitForSelector('#secondNavBar', {visible: true});

        const element = await page.$('#secondNavBar .navbar a[href*="Goals_EcommerceLog"]');
        expect(element).to.be.not.ok;
    });

    it('it should not show ecommerce log, when opened directly but disabled', async function () {
        await setFeatures(1, 1, 1);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2009-08-09#?idSite=1&period=year&date=2009-08-09&category=Goals_Ecommerce&subcategory=Goals_EcommerceLog");
        await page.waitForNetworkIdle();

        expect(await page.getWholeCurrentUrl()).to.not.match(/Goals_EcommerceLog/); // page should be redirected to next subcategory
    });

    it('it should show profile link in visits log when enabled', async function () {
        await setFeatures(1, 0, 0);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09#?idSite=1&period=year&date=2012-08-09&category=General_Visitors&subcategory=Live_VisitorLog");
        await page.waitForSelector('.dataTableVizVisitorLog');

        const element = await page.$('.dataTableVizVisitorLog .card .visitor-log-visitor-profile-link');
        expect(element).to.be.ok;
    });

    it('it should not show profile link in visits log when disabled', async function () {
        await setFeatures(1, 0, 1);
        await page.reload();
        await page.waitForSelector('.dataTableVizVisitorLog');

        const element = await page.$('.dataTableVizVisitorLog .card .visitor-log-visitor-profile-link');
        expect(element).to.be.not.ok;
    });

    it('it should show button to test new segments with enabled visits log', async function () {
        await setFeatures(1, 0, 1);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2012-07-09#?idSite=1&period=year&date=2012-07-09&category=General_Visitors&subcategory=General_Overview");
        await page.waitForSelector('.segmentationContainer');
        await page.click('.segmentationContainer');
        await page.click('.add_new_segment');

        const element = await page.$('.segmentEditorPanel .testSegment');
        expect(element).to.be.ok;
    });

    it('it should not show button to test new segments with disabled visits log', async function () {
        await setFeatures(1, 1, 1);
        await page.reload();
        await page.waitForSelector('.segmentationContainer');
        await page.click('.segmentationContainer');
        await page.click('.add_new_segment');

        const element = await page.$('.segmentEditorPanel .testSegment');
        expect(element).to.be.not.ok;
    });

    it('it should show segmented visits log row action when enabled', async function () {
        await setFeatures(1, 0, 1);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-08#?idSite=1&period=year&date=2012-08-08&category=General_Visitors&subcategory=DevicesDetection_Software");
        await page.waitForNetworkIdle();

        await (await page.jQuery('#widgetDevicesDetectiongetOsVersions td.label:first')).hover();
        await page.waitForSelector('#widgetDevicesDetectiongetOsVersions .dataTableRowActions', {visible: true});

        const element = await page.$('#widgetDevicesDetectiongetOsVersions .dataTableRowActions .actionSegmentVisitorLog');
        expect(element).to.be.ok;
    });

    it('it should not show segmented visits log row action when disabled', async function () {
        await setFeatures(1, 1, 1);
        await page.reload();
        await page.waitForNetworkIdle();

        await (await page.jQuery('#widgetDevicesDetectiongetOsVersions td.label:first')).hover();
        await page.waitForSelector('#widgetDevicesDetectiongetOsVersions .dataTableRowActions', {visible: true});

        const element = await page.$('#widgetDevicesDetectiongetOsVersions .dataTableRowActions .actionSegmentVisitorLog');
        expect(element).to.be.not.ok;
    });

    it('widget list should contain log and profile when enabled', async function () {
        await setFeatures(1, 0, 0);
        await page.goto("?module=Widgetize&action=index&idSite=1&period=day&date=yesterday");
        await page.waitForNetworkIdle();

        await (await page.jQuery('.widgetpreview-categorylist li:contains("Visitors"):first')).hover();
        await page.waitForSelector('.widgetpreview-widgetlist', {visible: true});

        const profile = await page.$('.widgetpreview-widgetlist [uniqueid=widgetLivegetVisitorProfilePopup]');
        expect(profile).to.be.ok;

        const log = await page.$('.widgetpreview-widgetlist [uniqueid=widgetLivegetLastVisitsDetailsforceView1viewDataTableVisitorLogsmall1]');
        expect(log).to.be.ok;

        const realtime = await page.$('.widgetpreview-widgetlist [uniqueid=widgetLivewidget]');
        expect(realtime).to.be.ok;

        const realtimemap = await page.$('.widgetpreview-widgetlist [uniqueid=widgetUserCountryMaprealtimeMap]');
        expect(realtimemap).to.be.ok;
    });

    it('widget list should not contain log and profile when disabled', async function () {
        await setFeatures(1, 1, 1);
        await page.goto("?module=Widgetize&action=index&idSite=1&period=day&date=yesterday");
        await page.waitForNetworkIdle();

        await (await page.jQuery('.widgetpreview-categorylist li:contains("Visitors"):first')).hover();
        await page.waitForSelector('.widgetpreview-widgetlist', {visible: true});

        const profile = await page.$('.widgetpreview-widgetlist [uniqueid=widgetLivegetVisitorProfilePopup]');
        expect(profile).to.be.not.ok;

        const log = await page.$('.widgetpreview-widgetlist [uniqueid=widgetLivegetLastVisitsDetailsforceView1viewDataTableVisitorLogsmall1]');
        expect(log).to.be.not.ok;

        const realtime = await page.$('.widgetpreview-widgetlist [uniqueid=widgetLivewidget]');
        expect(realtime).to.be.not.ok;

        const realtimemap = await page.$('.widgetpreview-widgetlist [uniqueid=widgetUserCountryMaprealtimeMap]');
        expect(realtimemap).to.be.not.ok;
    });

    it('Goal overview contains segmented visitor log link when activated', async function () {
        await setFeatures(1, 0, 1);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-08#?idSite=1&period=year&date=2012-08-08&category=Goals_Goals&subcategory=1");
        await page.waitForSelector('#widgetGoalsgoalConversionsOverviewidGoal1', {visible: true});

        const profile = await page.$('#widgetGoalsgoalConversionsOverviewidGoal1 a.segmentedlog');
        expect(profile).to.be.ok;
    });

    it('Goal overview does not contain segmented visitor log link when disabled', async function () {
        await setFeatures(1, 1, 1);
        await page.reload();
        await page.waitForSelector('#widgetGoalsgoalConversionsOverviewidGoal1', {visible: true});

        const profile = await page.$('#widgetGoalsgoalConversionsOverviewidGoal1 a.segmentedlog');
        expect(profile).to.be.not.ok;
    });


    // test system setting

    it('system settings for live plugin should be available by default', async function () {
        await page.goto("?module=CoreAdminHome&action=generalSettings");
        await page.waitForNetworkIdle();

        const log = await page.$('#LivePluginSettings #disable_visitor_log');
        expect(log).to.be.ok;

        const profile = await page.$('#LivePluginSettings #disable_visitor_profile');
        expect(profile).to.be.ok;
    });

    it('system settings for live plugin should be hidden if disabled in config', async function () {
        await setConfig(1, 1);
        await page.reload();
        await page.waitForNetworkIdle();

        const log = await page.$('#LivePluginSettings #disable_visitor_log');
        expect(log).to.be.not.ok;

        const profile = await page.$('#LivePluginSettings #disable_visitor_profile');
        expect(profile).to.be.not.ok;
    });

    it('measurable settings for live plugin should be available by default', async function () {
        await page.goto("?module=SitesManager&action=index&idSite=1");
        await page.waitForNetworkIdle();
        await page.click('[idsite="1"] .icon-edit');
        await page.waitForNetworkIdle();

        const log = await page.$('[idsite="1"] #disable_visitor_log');
        expect(log).to.be.ok;

        const profile = await page.$('[idsite="1"] #disable_visitor_profile');
        expect(profile).to.be.ok;
    });

    it('measurable settings for live plugin should be available by default', async function () {
        await setConfig(1, 1);
        await page.reload();
        await page.waitForNetworkIdle();
        await page.click('[idsite="1"] .icon-edit');
        await page.waitForNetworkIdle();

        const log = await page.$('[idsite="1"] #disable_visitor_log');
        expect(log).to.be.not.ok;

        const profile = await page.$('[idsite="1"] #disable_visitor_profile');
        expect(profile).to.be.not.ok;
    });

    it('menu should not contain visits log when deactivated globally', async function () {
        await setConfig(1, 1);
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2009-01-04#?idSite=1&period=year&date=2009-01-04&category=General_Visitors&subcategory=General_Overview");
        await page.waitForSelector('#secondNavBar', {visible: true});

        const element = await page.$('#secondNavBar .navbar a[href*="Live_VisitorLog"]');
        expect(element).to.be.not.ok;
    });

});
