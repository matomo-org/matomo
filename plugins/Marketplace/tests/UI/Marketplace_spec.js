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

        if (mode === 'superuser') {
          [paidPluginsUrl, '?module=Marketplace&action=manageLicenseKey&idSite=1&period=day&date=yesterday', '?module=CorePluginsAdmin&action=plugins&idSite=1&period=day&date=yesterday&activated=']
            .forEach(function (url, index) {
              it(mode + ' for a user with license key should be able to open paid plugins ' + index, async() => {
                  var indexArray = ['paidPluginsUrl', 'manageLicenseKeyUrl', 'managePluginsUrl'];
                  setEnvironment(mode, validLicense);

                  await page.goto('about:blank');
                  await page.goto(url);

                  await captureSelector('paid_plugins_with_license_' + indexArray[index] + '_' + mode, '.pageWrap');
              });


              it(mode + ' for a user with license key should be able to open install purchased plugins modal for ' + index, async() => {
                  var indexArray = ['paidPluginsUrl', 'manageLicenseKeyUrl', 'managePluginsUrl'];
                  setEnvironment(mode, validLicense);

                  await page.goto('about:blank');
                  await page.goto(url);
                  await page.waitForNetworkIdle();
                  await page.waitForTimeout(500);

                  const elem = await page.jQuery(
                    '.installAllPaidPluginsAtOnceButton.btn'
                  );

                  await elem.click();

                  // give it some time to fetch, animate, and render everything properly
                  await page.waitForNetworkIdle();
                  await page.waitForTimeout(500);

                  const pageElement = await page.$('.modal.open');
                  expect(await pageElement.screenshot()).to.matchImage('install_purchased_plugins_modal_' + indexArray[index] + '_' + mode);
              });
            });
        }

        it(mode + ' should open paid plugins modal for paid plugin 1', async function () {
            setEnvironment(mode, validLicense);
            await page.goto('about:blank');
            await page.goto(paidPluginsUrl);
            await loadPluginDetailPage('Paid Plugin 1', false);

            await captureWithPluginDetails('paid_plugin1_plugin_details_' + mode);
        });

        it(mode + ' should open paid plugins modal for paid plugin 2', async function () {
            setEnvironment(mode, validLicense);
            await page.goto('about:blank');
            await page.goto(paidPluginsUrl);
            await loadPluginDetailPage('Paid Plugin 2', false);

            await captureWithPluginDetails('paid_plugin2_plugin_details_' + mode);
        });

        it(mode + ' should open paid plugins modal for paid plugin 3', async function () {
            setEnvironment(mode, validLicense);
            await loadPluginDetailPage('Paid Plugin 3', false);

            await captureWithPluginDetails('paid_plugin3_plugin_details_' + mode);
        });

        it(mode + ' should open paid plugins modal for paid plugin 4', async function () {
            setEnvironment(mode, validLicense);
            await loadPluginDetailPage('Paid Plugin 4', false);

            await captureWithPluginDetails('paid_plugin4_plugin_details_' + mode);
        });

        it(mode + ' should open paid plugins modal for paid plugin 5', async function () {
            setEnvironment(mode, validLicense);
            await loadPluginDetailPage('Paid Plugin 5', false);

            await captureWithPluginDetails('paid_plugin5_plugin_details_' + mode);
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

         it('should show themes page without install button when enable_plugins_admin=0', async function () {
            setEnvironment(mode, validLicense);
            testEnvironment.overrideConfig('General', 'enable_plugins_admin', '0');
            testEnvironment.save();

            await page.goto('about:blank');
            await page.goto(themesUrl);

            await captureMarketplace('themes_with_valid_license_disabled_' + mode);
            testEnvironment.overrideConfig('General', 'enable_plugins_admin', '1');
            testEnvironment.save();
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

        it('should show an add to cart button with user selector', async function() {
            setEnvironment(mode, noLicense);

            var isFree = false;
            await loadPluginDetailPage('Paid Plugin 1', isFree);

            await captureWithPluginDetails('paid_plugin_details_add_to_cart_' + mode);
        });

        it('should show paid plugin details when having valid license', async function() {
            setEnvironment(mode, exceededLicense);

            assumePaidPluginsActivated();
            var isFree = false;
            await loadPluginDetailPage('Paid Plugin 1', isFree);

            await captureWithPluginDetails('paid_plugin_details_exceeded_license_' + mode);
        });
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
