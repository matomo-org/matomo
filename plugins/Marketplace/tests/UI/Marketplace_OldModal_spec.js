/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot tests for Marketplace.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('Marketplace_OldModal', function () {
    this.timeout(0);

    this.fixture = 'Piwik\\Plugins\\Marketplace\\tests\\Fixtures\\SimpleFixtureTrackFewVisits';

    const urlBase = '?module=CoreAdminHome&action=home&popover=browsePluginDetail%243A';
    const noLicense = 'noLicense';
    const exceededLicense = 'exceededLicense';
    const validLicense = 'validLicense';

    async function loadPluginModal(pluginName)
    {
        await page.goto(urlBase + pluginName);

        await page.waitForNetworkIdle();
        await page.waitForSelector('.ui-dialog .pluginDetails');
        await page.waitForNetworkIdle();
    }

    async function captureModal(screenshotName)
    {
        expect(await page.screenshotSelector('.ui-dialog')).to.matchImage(screenshotName);
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
        it('should show free plugin details', async function() {
            setEnvironment(mode, noLicense);

            await loadPluginModal('TreemapVisualization', true);
            await captureModal('free_plugin_details_' + mode);
        });

        it('should show paid plugin details when having no license', async function() {
            setEnvironment(mode, noLicense);
            assumePaidPluginsActivated();

            await loadPluginModal('PaidPlugin1', false);
            await captureModal('paid_plugin_details_no_license_' + mode);
        });

        it('should show paid plugin details when having valid license', async function() {
            setEnvironment(mode, validLicense);
            assumePaidPluginsActivated();

            await loadPluginModal('PaidPlugin1', false);
            await captureModal('paid_plugin_details_valid_license_' + mode + '_installed');
        });

        it('should show paid plugin details when having valid license', async function() {
            setEnvironment(mode, exceededLicense);
            assumePaidPluginsActivated();

            await loadPluginModal('PaidPlugin1', false);
            await captureModal('paid_plugin_details_exceeded_license_' + mode);
        });
    });
});
