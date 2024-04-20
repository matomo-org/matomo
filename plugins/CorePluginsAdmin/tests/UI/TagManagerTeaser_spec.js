/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("TagManagerTeaser", function () {
    this.timeout(0);

    var urlBase = '?module=CorePluginsAdmin&action=tagManagerTeaser&idSite=1&period=day&date=2019-01-03',
        pageSelector = '.activateTagManager';

    function setPluginsToLoad(plugins)
    {
        testEnvironment.pluginsToLoad = plugins;
        testEnvironment.save();
    }

    function unloadTagManager()
    {
        testEnvironment.unloadTagManager = 1;
        testEnvironment.save();
    }

    function setAdminUser()
    {
        delete testEnvironment.idSitesViewAccess;
        delete testEnvironment.idSitesWriteAccess;
        testEnvironment.idSitesAdminAccess = [1];
        testEnvironment.save();
    }

    function reset()
    {
        delete testEnvironment.idSitesViewAccess;
        delete testEnvironment.idSitesWriteAccess;
        delete testEnvironment.idSitesAdminAccess;
        delete testEnvironment.idSitesCapabilities;
        delete testEnvironment.unloadTagManager;
        testEnvironment.save();
    }

    beforeEach(function () {
        setPluginsToLoad(['CorePluginsAdmin']);
    });

    afterEach(reset);

    it('should show teaser to super user', async function () {
        unloadTagManager();
        await page.goto(urlBase);
        await page.waitForSelector('.activateTagManager');
        expect(await page.screenshotSelector(pageSelector)).to.matchImage('superuser_page');
    });

    it('should be possible to activate plugin and redirect to tag manager', async function () {
        await page.click('.activateTagManager .activateTagManagerPlugin');
        await page.waitForNetworkIdle();

        await page.type('#login_form_password', superUserPassword);
        await page.click('#login_form_submit');

        await page.waitForSelector('.tagManagerGettingStarted');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(250);

        expect(await page.screenshotSelector('.pageWrap')).to.matchImage('super_user_activate_plugin');
    });

    it('should show teaser to admin', async function () {
        unloadTagManager();
        setAdminUser();
        await page.goto(urlBase);
        expect(await page.screenshotSelector(pageSelector)).to.matchImage('admin_page');
    });

    it('should be possible to disable page and redirect to home', async function () {
        unloadTagManager();
        setAdminUser();
        await page.click('.activateTagManager .dontShowAgainBtn');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.widget');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.pageWrap')).to.matchImage('admin_page_disable');
    });

});
