/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot tests for Marketplace.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Marketplace", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\Marketplace\\tests\\Fixtures\\SimpleFixtureTrackFewVisits";

    var urlBase = '?module=Marketplace&action=overview';
    var paidPluginsUrl = urlBase + '#?pluginType=premium';
    var themesUrl = urlBase + '#?pluginType=themes';
    var pluginsUrl = urlBase;

    var noLicense = 'noLicense';
    var expiredLicense = 'expiredLicense';
    var exceededLicense = 'exceededLicense';
    var validLicense = 'validLicense';

    async function loadPluginDetailPage(pluginTitle, isFreePlugin)
    {
        await page.goto('about:blank');
        await page.goto(isFreePlugin ? pluginsUrl : paidPluginsUrl);

        const elem = await page.jQuery(
          '.card-content .card-title:contains("' + pluginTitle + '")',
          { waitFor: true }
        );

        await elem.click();
        await page.waitForSelector('#pluginDetailsModal .modal-content__main', { visible: true });

        // give it some time to fetch, animate, and render everything properly
        await page.waitForNetworkIdle();
        await page.waitForTimeout(100);
    }

    async function captureSelector(screenshotName, selector)
    {
        await page.waitForSelector(selector, { visible: true });
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector(selector)).to.matchImage(screenshotName);
    }

    async function captureModal(screenshotName, selector)
    {
        await page.waitForFunction("$('" + selector + "').length > 0");
        await page.waitForNetworkIdle();

        const elem = await page.$(selector);
        expect(await elem.screenshot()).to.matchImage(screenshotName);
    }

    async function captureMarketplace(screenshotName, selector)
    {
        if (!selector) {
            await page.waitForNetworkIdle();

            const element = await page.$('.marketplace');
            expect(await element.screenshot()).to.matchImage(screenshotName);
            return;
        }

        await captureSelector(screenshotName, '.marketplace' + selector);
    }

    async function captureWithNotification(screenshotName)
    {
        await captureMarketplace(screenshotName, ',#notificationContainer');
    }

    async function captureWithPluginDetails(screenshotName)
    {
        const selector = '#pluginDetailsModal .modal-content';

        // screenshotting the Materialize modal consistently
        // clips wrong and captures nothing,
        // unless the screenshot is attempted twice
        await page.screenshotSelector(selector);

        expect(await page.screenshotSelector(selector)).to.matchImage(screenshotName);
    }

    function assumePaidPluginsActivated()
    {
        testEnvironment.mockMarketplaceAssumePluginNamesActivated = ['CustomPlugin1','CustomPlugin2','PaidPlugin1','PaidPlugin2'];
        testEnvironment.save();
    }

    function setEnvironment(mode, consumer)
    {
        if (mode === 'user') {
            testEnvironment.idSitesViewAccess = [1];
        } else {
            // superuser
            testEnvironment.idSitesViewAccess = [];
        }

        if (mode === 'multiUserEnvironment') {
            testEnvironment.overrideConfig('General', 'multi_server_environment', '1');
        } else {
            testEnvironment.overrideConfig('General', 'multi_server_environment', '0');
        }

        testEnvironment.overrideConfig('General', 'enable_plugins_admin', '1');

        delete testEnvironment.mockMarketplaceAssumePluginNamesActivated;

        testEnvironment.consumer = consumer;
        testEnvironment.mockMarketplaceApiService = 1;
        testEnvironment.forceEnablePluginUpdateChecks = 1;
        testEnvironment.save();
    }

    ['superuser', 'user', 'multiUserEnvironment'].forEach(function (mode) {

        if (mode !== 'user') {
            it('should show available updates in plugins page', async function() {
                setEnvironment(mode, noLicense);

                await page.goto('?module=CorePluginsAdmin&action=plugins&idSite=1&period=day&date=yesterday&activated=');

                await captureSelector('updates_' + mode, '#content div[vue-entry="CorePluginsAdmin.PluginsTableWithUpdates"]');
            });
        }

        it(mode + ' for a user without license key should be able to open paid plugins', async function() {
            setEnvironment(mode, noLicense);

            await page.goto('about:blank');
            await page.goto(paidPluginsUrl);

            await captureMarketplace('paid_plugins_no_license_' + mode);
        });

        it(mode + ' for a user with license key should be able to open paid plugins', async function() {
            setEnvironment(mode, validLicense);

            await page.goto('about:blank');
            await page.goto(paidPluginsUrl);

            await captureMarketplace('paid_plugins_with_license_' + mode);
        });

        it(mode + ' for a user with exceeded license key should be able to open paid plugins', async function() {
            setEnvironment(mode, exceededLicense);
            assumePaidPluginsActivated();

            await page.goto('about:blank');
            await page.goto(paidPluginsUrl);

            await captureMarketplace('paid_plugins_with_exceeded_license_' + mode);
        });

        it('should show themes page', async function() {
            setEnvironment(mode, validLicense);

            await page.goto('about:blank');
            await page.goto(themesUrl);

            await captureMarketplace('themes_with_valid_license_' + mode);
        });

        it('should show free plugin details', async function() {
            setEnvironment(mode, noLicense);

            var isFree = true;
            await loadPluginDetailPage('Treemap Visualization', isFree);

            await captureWithPluginDetails('free_plugin_details_' + mode);
        });

        it('should show paid plugin details when having no license', async function() {
            setEnvironment(mode, noLicense);

            assumePaidPluginsActivated();
            var isFree = false;
            await loadPluginDetailPage('Paid Plugin 1', isFree);

            await captureWithPluginDetails('paid_plugin_details_no_license_' + mode);
        });

        it('should show paid plugin details when having valid license', async function() {
            setEnvironment(mode, validLicense);

            assumePaidPluginsActivated();
            var isFree = false;
            await loadPluginDetailPage('Paid Plugin 1', isFree);

            await captureWithPluginDetails('paid_plugin_details_valid_license_' + mode + '_installed');
        });

        it('should show paid plugin details when having valid license', async function() {
            setEnvironment(mode, exceededLicense);

            assumePaidPluginsActivated();
            var isFree = false;
            await loadPluginDetailPage('Paid Plugin 1', isFree);

            await captureWithPluginDetails('paid_plugin_details_exceeded_license_' + mode);
        });
    });

    [noLicense, expiredLicense, exceededLicense].forEach(function (consumer) {
        // when there is no license it should not show a warning! as it could be due to network problems etc
        it('should show a warning if license is ' + consumer, async function() {
            setEnvironment('superuser', consumer);

            assumePaidPluginsActivated();

            await page.goto('?module=UsersManager&action=index');

            await captureSelector('notification_plugincheck_' + consumer, '#notificationContainer');
        });
    });

});
