/*!
 * Matomo - free/libre analytics platform
 *
 * Installation screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
var fs = require('fs'),
    path = require('../../../../tests/lib/screenshot-testing/support/path');

describe("Installation", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Tests\\Fixtures\\EmptySite";

    before(function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.configFileLocal = path.join(PIWIK_INCLUDE_PATH, "/tmp/test.config.ini.php");
        testEnvironment.dontUseTestConfig = true;
        testEnvironment.ignoreClearAllViewDataTableParameters = 1;
        testEnvironment.tablesPrefix = 'piwik_';
        testEnvironment.save();

        if (fs.existsSync(testEnvironment.configFileLocal)) {
            fs.unlinkSync(testEnvironment.configFileLocal);
        }
    });

    after(function () {
        delete testEnvironment.configFileLocal;
        delete testEnvironment.dontUseTestConfig;
        delete testEnvironment.tablesPrefix;
        delete testEnvironment.testUseMockAuth;
        testEnvironment.save();
    });

    it("should display an error message when trying to access a resource w/o a config.ini.php file", async function() {
        await page.goto("?module=CoreHome&action=index");

        await page.evaluate(function () {
            // ensure screenshots are reporting same config file for comparison
            // no jQuery existing on these error pages...
            document.body.innerHTML = document.body.innerHTML.replace(
                /{\/.*\/test\.config\.ini\.php}/,
                '{/home/matomo/config/test.config.ini.php}'
            );
        });

        expect(await page.screenshot({ fullPage: true })).to.matchImage('access_no_config');
    });

    it("should start the installation process when the index is visited w/o a config.ini.php file", async function() {
        await page.goto("");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('start');
    });

    it("should display the system check page when next is clicked on the first page", async function() {
        await page.click('.next-step .btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('system_check');
    });

    let pageUrl;
    it("should have already created a tmp/sessions/index.htm file to prevent directory listing", async function() {
        pageUrl = page.url();

        // page.load will load by default the proxy ie. http://localhost/piwik/tests/PHPUnit/proxy/
        // but we need here to check in: http://localhost/piwik/tmp/sessions/
        await page.goto("../../../tmp/sessions/index.htm");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('nothing_to_see_here');
    });

    it("should display the database setup page when next is clicked on the system check page", async function() {
        await page.goto(pageUrl);

        page.click('.next-step .btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('db_setup');
    });

    it("should fail when the next button is clicked and no database info is entered in the form", async function() {
        await page.click('.btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('db_setup_fail');
    });

    it("should display the tables created page when next is clicked on the db setup page w/ correct info entered in the form", async function() {
        const dbInfo = testEnvironment.readDbInfoFromConfig();
        const username = dbInfo.username;
        const password = dbInfo.password;
        await page.type('input[name="username"]', username);

        if (password) {
            await page.type('input[name="password"]', password);
        }

        await page.type('input[name="dbname"]', 'newdb');
        await page.click('.btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('db_created');
    });

    it("should display the superuser configuration page when next is clicked on the tables created page", async function() {
        await page.click('.next-step .btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('superuser');
    });

    let pageUrlDe;

    it("should un-select Professional Services newsletter checkbox when language is German", async function() {
        pageUrl = await page.url();
        pageUrlDe = pageUrl + '&language=de';
        await page.goto(pageUrlDe);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('superuser_de');
    });

    it("should fail when incorrect information is entered in the superuser configuration page", async function() {
        await page.goto(pageUrl);
        await page.click('.btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('superuser_fail');
    });

    it("should display the setup a website page when next is clicked on the filled out superuser config page", async function() {
        await page.type('input[name="login"]', 'thesuperuser');
        await page.type('input[name="password"]', 'thepassword');
        await page.type('input[name="password_bis"]', 'thepassword');
        await page.type('input[name="email"]', 'hello@piwik.org');
        await page.click('.btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('setup_website');
    });

    it("should should fail when incorrect information is entered in the setup a website page", async function() {
        await page.click('.btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('setup_website_fail');
    });

    it("should display the javascript tracking page when correct information is entered in the setup website page and next is clicked", async function() {
        await page.type('input[name="siteName"]', 'Serenity');
        await page.evaluate(function () {
            // cannot use sendKeys since quickform does not use placeholder attribute
            $('input[name=url]').val('serenity.com');

            $('select[name=timezone]').val('Europe/Paris');
            $('select[name=ecommerce]').val('1');
        });
        await page.click('.btn');
        await page.waitForNetworkIdle();

        // manually remove port in tracking code, since ui-test.php won't be using the correct INI config file
        await page.evaluate(function () {
            $('pre').each(function () {
                var html = $(this).html();
                html = html.replace(/localhost\:[0-9]+/g, 'localhost');
                $(this).html(html);
            });
        });

        expect(await page.screenshot({ fullPage: true })).to.matchImage('js_tracking');
    });

    it("should display the congratulations page when next is clicked on the javascript tracking page", async function() {
        await page.click('.next-step .btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('congrats');
    });

    it("should continue to piwik after submitting on the privacy settings form in the congrats page", async function() {
        await page.click('.btn');
        await page.waitForNetworkIdle();

        // check login form is displayed
        await page.waitForSelector('.loginForm');
    });
});
