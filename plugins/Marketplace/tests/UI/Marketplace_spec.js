/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot tests for Marketplace.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Marketplace", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\Marketplace\\tests\\Fixtures\\SimpleFixtureTrackFewVisits";

    var urlBase = '?module=Marketplace&action=overview';
    var paidPluginsUrl = urlBase + '&show=premium';
    var themesUrl = urlBase + '&show=themes';
    var pluginsUrl = urlBase;

    var noLicense = 'noLicense';
    var expiredLicense = 'expiredLicense';
    var exceededLicense = 'exceededLicense';
    var validLicense = 'validLicense';

    async function loadPluginDetailPage(pluginName, isFreePlugin)
    {
        await page.goto(isFreePlugin ? pluginsUrl : paidPluginsUrl);
        const elem = await page.waitForSelector('.card-title [vue-directive="CorePluginsAdmin.PluginName"][vue-directive-value*="' + pluginName + '"]');
        await elem.click();
        await page.waitForNetworkIdle();
        await page.waitForSelector('.ui-dialog .pluginDetails');
    }

    async function captureSelector(screenshotName, selector)
    {
        await page.waitForFunction("$('" + selector + "').length > 0");
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

    async function captureWithDialog(screenshotName)
    {
        await captureSelector(screenshotName, '.ui-dialog');
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

                await captureSelector('updates_' + mode, '#content .card:first');
            });
        }

        it(mode + ' for a user without license key should be able to open paid plugins', async function() {
            setEnvironment(mode, noLicense);

            await page.goto(paidPluginsUrl);

            await captureMarketplace('paid_plugins_no_license_' + mode);
        });

        it(mode + ' for a user with license key should be able to open paid plugins', async function() {
            setEnvironment(mode, validLicense);

            await page.goto(paidPluginsUrl);

            await captureMarketplace('paid_plugins_with_license_' + mode);
        });

        it(mode + ' for a user with exceeded license key should be able to open paid plugins', async function() {
            setEnvironment(mode, exceededLicense);
            assumePaidPluginsActivated();

            await page.goto(paidPluginsUrl);

            await captureMarketplace('paid_plugins_with_exceeded_license_' + mode);
        });

        it('should show themes page', async function() {
            setEnvironment(mode, validLicense);

            await page.goto(themesUrl);

            await captureMarketplace('themes_with_valid_license_' + mode);
        });

        it('should show free plugin details', async function() {
            setEnvironment(mode, noLicense);

            var isFree = true;
            await loadPluginDetailPage('TreemapVisualization', isFree);

            await captureWithDialog('free_plugin_details_' + mode);
        });

        it('should show paid plugin details when having no license', async function() {
            setEnvironment(mode, noLicense);

            assumePaidPluginsActivated();
            var isFree = false;
            await loadPluginDetailPage('PaidPlugin1', isFree);

            await captureWithDialog('paid_plugin_details_no_license_' + mode);
        });

        it('should show paid plugin details when having valid license', async function() {
            setEnvironment(mode, validLicense);

            assumePaidPluginsActivated();
            var isFree = false;
            await loadPluginDetailPage('PaidPlugin1', isFree);

            await captureWithDialog('paid_plugin_details_valid_license_' + mode + '_installed');
        });

        it('should show paid plugin details when having valid license', async function() {
            setEnvironment(mode, exceededLicense);

            assumePaidPluginsActivated();
            var isFree = false;
            await loadPluginDetailPage('PaidPlugin1', isFree);

            await captureWithDialog('paid_plugin_details_exceeded_license_' + mode);
        });
    });

    var mode = 'superuser';

    it('should show a dialog showing a list of all possible plugins to install', async function() {
        setEnvironment(mode, validLicense);

        await page.goto(pluginsUrl);
        await page.click('.installAllPaidPlugins');
        await page.mouse.move(-10, -10);

        await captureModal(mode + '_install_all_paid_plugins_at_once', '.modal.open');
    });

    it('should show an error message when invalid license key entered', async function() {
        setEnvironment(mode, noLicense);

        await page.goto(pluginsUrl);
        await page.type('#license_key', 'invalid');
        await page.waitForTimeout(200);
        await page.click('.marketplace-paid-intro'); // click outside so change event is triggered
        await page.click('#submit_license_key input');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(200);

        await captureWithNotification(mode + '_invalid_license_key_entered');
    });

    it('should show a confirmation before removing a license key', async function() {
        setEnvironment(mode, validLicense);

        await page.goto(pluginsUrl);
        await page.click('#remove_license_key input');

        await captureModal(mode + '_remove_license_key_confirmation', '.modal.open');
    });

    it('should show a confirmation before removing a license key', async function() {
        setEnvironment(mode, noLicense);

        elem = await page.jQuery('.modal.open .modal-footer a:contains(Yes)');
        await elem.click();

        await captureMarketplace(mode + '_remove_license_key_confirmed');
    });

    it('should show a success message when valid license key entered', async function() {
        setEnvironment(mode, noLicense);

        await page.goto(pluginsUrl);
        await page.type('#license_key', 'valid');
        await page.waitForTimeout(200);

        setEnvironment(mode, validLicense);
        await page.click('#submit_license_key input');

        await captureMarketplace(mode + '_valid_license_key_entered');
    });

    it('should hide activate / deactivate buttons if plugins admin is disabled', async function() {
        setEnvironment(mode, noLicense);
        testEnvironment.overrideConfig('General', 'enable_plugins_admin', '0');
        testEnvironment.save();

        await page.goto(pluginsUrl);

        await captureMarketplace( mode + '_enable_plugins_admin');
    });

    it('should hide activate / deactivate buttons if plugins admin is disabled when also multi server environment is enabled', async function() {
        setEnvironment('multiUserEnvironment', noLicense);
        testEnvironment.overrideConfig('General', 'enable_plugins_admin', '0');
        testEnvironment.save();

        await page.goto(pluginsUrl);

        await captureMarketplace(mode + '_enable_plugins_admin_with_multiserver_enabled');
    });

    [expiredLicense, exceededLicense, validLicense, noLicense].forEach(function (consumer) {
        it('should show a subscription overview for ' + consumer, async function() {
            setEnvironment('superuser', consumer);

            await page.goto('?module=Marketplace&action=subscriptionOverview');

            await captureSelector('subscription_overview_' + consumer, '#content');
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
