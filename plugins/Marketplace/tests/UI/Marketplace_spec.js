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

    var urlBase = '?module=Marketplace&action=overview&';
    var paidPluginsUrl = urlBase + 'show=premium';
    var themesUrl = urlBase + 'show=themes';
    var pluginsUrl = urlBase;

    var noLicense = 'noLicense';
    var expiredLicense = 'expiredLicense';
    var exceededLicense = 'exceededLicense';
    var validLicense = 'validLicense';

    function loadPluginDetailPage(page, pluginName, isFreePlugin)
    {
        page.load(isFreePlugin ? pluginsUrl : paidPluginsUrl);
        page.click('.card-title [piwik-plugin-name="' + pluginName + '"]');
    }

    function captureSelector(done, screenshotName, test, selector)
    {
        expect.screenshot(screenshotName).to.be.captureSelector(selector, test, done);
    }

    function captureMarketplace(done, screenshotName, test, selector)
    {
        if (!selector) {
            selector = '';
        }

        captureSelector(done, screenshotName, test, '.marketplace' + selector);
    }

    function captureWithNotification(done, screenshotName, test)
    {
        captureMarketplace(done, screenshotName, test, ',#notificationContainer');
    }

    function captureWithDialog(done, screenshotName, test)
    {
        captureSelector(done, screenshotName, test, '.ui-dialog:visible');
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
        testEnvironment.save();
    }

    ['superuser', 'user', 'multiUserEnvironment'].forEach(function (mode) {

        if (mode !== 'user') {
            it('should show available updates in plugins page', async function() {
                setEnvironment(mode, noLicense);

                captureSelector(done, 'updates_' + mode, function (page) {
                    page.load('?module=CorePluginsAdmin&action=plugins&idSite=1&period=day&date=yesterday&activated=');
                }, '#content .card:first');
            });
        }

        it(mode + ' for a user without license key should be able to open paid plugins', async function() {
            setEnvironment(mode, noLicense);

            captureMarketplace(done, 'paid_plugins_no_license_' + mode, function (page) {
                page.load(paidPluginsUrl);
            });
        });

        it(mode + ' for a user with license key should be able to open paid plugins', async function() {
            setEnvironment(mode, validLicense);

            captureMarketplace(done, 'paid_plugins_with_license_' + mode, function (page) {
                page.load(paidPluginsUrl);
            });
        });

        it(mode + ' for a user with exceeded license key should be able to open paid plugins', async function() {
            setEnvironment(mode, exceededLicense);
            assumePaidPluginsActivated();

            captureMarketplace(done, 'paid_plugins_with_exceeded_license_' + mode, function (page) {
                page.load(paidPluginsUrl);
            });
        });

        it('should show themes page', async function() {
            setEnvironment(mode, validLicense);

            captureMarketplace(done, 'themes_with_valid_license_' + mode, function (page) {
                page.load(themesUrl);
            });
        });

        it('should show free plugin details', async function() {
            setEnvironment(mode, noLicense);

            captureWithDialog(done, 'free_plugin_details_' + mode, function (page) {
                var isFree = true;
                loadPluginDetailPage(page, 'TreemapVisualization', isFree);
            });
        });

        it('should show paid plugin details when having no license', async function() {
            setEnvironment(mode, noLicense);

            captureWithDialog(done, 'paid_plugin_details_no_license_' + mode, function (page) {
                assumePaidPluginsActivated();
                var isFree = false;
                loadPluginDetailPage(page, 'PaidPlugin1', isFree);
            });
        });

        it('should show paid plugin details when having valid license', async function() {
            setEnvironment(mode, validLicense);

            captureWithDialog(done, 'paid_plugin_details_valid_license_' + mode + '_installed', function (page) {
                assumePaidPluginsActivated();
                var isFree = false;
                loadPluginDetailPage(page, 'PaidPlugin1', isFree);
            });
        });

        it('should show paid plugin details when having valid license', async function() {
            setEnvironment(mode, exceededLicense);

            captureWithDialog(done, 'paid_plugin_details_exceeded_license_' + mode, function (page) {
                assumePaidPluginsActivated();
                var isFree = false;
                loadPluginDetailPage(page, 'PaidPlugin1', isFree);
            });
        });
    });

    var mode = 'superuser';

    it('should show a dialog showing a list of all possible plugins to install', async function() {
        setEnvironment(mode, validLicense);

        captureSelector(done, mode + '_install_all_paid_plugins_at_once', function (page) {
            page.load(pluginsUrl);
            page.click('.installAllPaidPlugins');
        }, '.modal.open');
    });

    it('should show an error message when invalid license key entered', async function() {
        setEnvironment(mode, noLicense);

        captureWithNotification(done, mode + '_invalid_license_key_entered', function (page) {
            page.load(pluginsUrl);
            page.sendKeys('#license_key', 'invalid');
            page.click('.marketplace-paid-intro'); // click outside so change event is triggered
            page.click('#submit_license_key input');
        });
    });

    it('should show a confirmation before removing a license key', async function() {
        setEnvironment(mode, validLicense);

        captureSelector(done, mode + '_remove_license_key_confirmation', function (page) {
            page.load(pluginsUrl);
            page.click('#remove_license_key input');
        }, '.modal.open');
    });

    it('should show a confirmation before removing a license key', async function() {
        setEnvironment(mode, noLicense);

        captureMarketplace(done, mode + '_remove_license_key_confirmed', function (page) {
            page.click('.modal.open .modal-footer a:contains(Yes)')
        });
    });

    it('should show a success message when valid license key entered', async function() {
        setEnvironment(mode, noLicense);

        captureMarketplace(done, mode + '_valid_license_key_entered', function (page) {
            page.load(pluginsUrl);
            page.sendKeys('#license_key', 'valid');
            page.execCallback(function () {
                setEnvironment(mode, validLicense);
            });
            page.click('#submit_license_key input');
        });
    });

    it('should hide activate / deactivate buttons if plugins admin is disabled', async function() {
        setEnvironment(mode, noLicense);
        testEnvironment.overrideConfig('General', 'enable_plugins_admin', '0');
        testEnvironment.save();

        captureMarketplace(done, mode + '_enable_plugins_admin', function (page) {
            page.load(pluginsUrl);
        });
    });

    it('should hide activate / deactivate buttons if plugins admin is disabled when also multi server environment is enabled', async function() {
        setEnvironment('multiUserEnvironment', noLicense);
        testEnvironment.overrideConfig('General', 'enable_plugins_admin', '0');
        testEnvironment.save();

        captureMarketplace(done, mode + '_enable_plugins_admin_with_multiserver_enabled', function (page) {
            page.load(pluginsUrl);
        });
    });

    [expiredLicense, exceededLicense, validLicense, noLicense].forEach(function (consumer) {
        it('should show a subscription overview for ' + consumer, async function() {
            setEnvironment('superuser', consumer);

            captureSelector(done, 'subscription_overview_' + consumer, function (page) {
                page.load('?module=Marketplace&action=subscriptionOverview');
            }, '#content');
        });
    });

    [noLicense, expiredLicense, exceededLicense].forEach(function (consumer) {
        // when there is no license it should not show a warning! as it could be due to network problems etc
        it('should show a warning if license is ' + consumer, async function() {
            setEnvironment('superuser', consumer);

            assumePaidPluginsActivated();

            captureSelector(done, 'notification_plugincheck_' + consumer, function (page) {
                page.load('?module=UsersManager&action=index');
            }, '#notificationContainer');
        });
    });

});