/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("TagManagerTeaser", function () {
    this.timeout(0);

    var urlBase = '?module=CorePluginsAdmin&action=tagManagerTeaser&idSite=1&period=day&date=2010-01-03',
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

    function capturePage(done, screenshotName, test, selector)
    {
        if (!selector) {
            selector = pageSelector;
        }
        expect.screenshot(screenshotName).to.be.captureSelector(selector, test, done);
    }

    it('should show teaser to super user', function (done) {
        unloadTagManager();
        capturePage(done, 'superuser_page', function (page) {
            unloadTagManager();
            page.load(urlBase);
        });
    });

    it('should be possible to activate plugin and redirect to tag manager', function (done) {
        capturePage(done, 'super_user_activate_plugin', function (page) {
            page.click('.activateTagManager .activateTagManagerPlugin');
        }, '.pageWrap');
    });

    it('should show teaser to admin', function (done) {
        unloadTagManager();
        setAdminUser();
        capturePage(done, 'admin_page', function (page) {
            unloadTagManager();
            setAdminUser();
            page.load(urlBase);
        });
    });

    it('should be possible to disable page and redirect to home', function (done) {
        expect.page("").contains("#dashboard", function (page) {
            unloadTagManager();
            setAdminUser();
            page.click('.activateTagManager .dontShowAgainBtn');
        }, done);
    });

});