/*!
 * Matomo - free/libre analytics platform
 *
 * Installation screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
var fs = require('fs'),
    path = require('../../../../tests/lib/screenshot-testing/support/path');

describe('Installation', function () {
    this.timeout(0);
    this.fixture = 'Piwik\\Tests\\Fixtures\\EmptySite';

    /** Timestamp when first website came online - Tue, 06 Aug 1991 00:00:00 GMT. */
    const firstWebsiteTimestamp = 681436800;
    const installationDbName = 'newdb';

    let installationStarted;
    let pageUrl;
    let pageUrlDe;

    function deleteLocalConfigFile() {
        if (fs.existsSync(testEnvironment.configFileLocal)) {
            fs.unlinkSync(testEnvironment.configFileLocal);
        }
    }

    before(function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.configFileLocal = path.join(PIWIK_INCLUDE_PATH, "/tmp/test.config.ini.php");
        testEnvironment.dontUseTestConfig = true;
        testEnvironment.ignoreClearAllViewDataTableParameters = 1;
        testEnvironment.tablesPrefix = 'piwik_';
        testEnvironment.save();

        deleteLocalConfigFile();
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

    it("should lock the installer when installation first accessed more than 3 days ago", async function() {
        deleteLocalConfigFile();

        // create a valid config file
        await page.goto("");

        // overwrite access timestamp
        testEnvironment.appendToLocalConfig(`
[General]
installation_first_accessed = ${firstWebsiteTimestamp}
`);

        await page.goto("");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('expired');
    });

    it("should start the installation process when the index is visited w/o a config.ini.php file", async function() {
        deleteLocalConfigFile();

        await page.goto("");

        const { firstAccessed, inProgress } = testEnvironment.readInstallationInfoFromLocalConfig();

        expect(inProgress).to.be.false;
        expect(firstAccessed).to.be.greaterThan(firstWebsiteTimestamp);

        installationStarted = firstAccessed;

        expect(await page.screenshot({ fullPage: true })).to.matchImage('start');
    });

    it("should display the system check page when next is clicked on the first page", async function() {
        await page.click('.next-step .btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('system_check');
    });

    it("should have already created a tmp/sessions/index.htm file to prevent directory listing", async function() {
        pageUrl = page.url();

        // page.load will load by default the proxy ie. http://localhost/piwik/tests/PHPUnit/proxy/
        // but we need here to check in: http://localhost/piwik/tmp/sessions/
        await page.goto("../../../tmp/sessions/index.htm");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('nothing_to_see_here');
    });

    it("should display the database setup page when next is clicked on the system check page", async function() {
        await page.goto(pageUrl);

        await page.click('.next-step .btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('db_setup');
    });

    it("should fail when the next button is clicked and no database info is entered in the form", async function() {
        await page.click('.btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('db_setup_fail');
    });

    it("should display the tables created page when next is clicked on the db setup page w/ correct info entered in the form", async function() {
        const { username, password } = testEnvironment.readDbInfoFromConfig();

        await page.type('input[name="username"]', username);
        await page.type('input[name="password"]', password);
        await page.type('input[name="dbname"]', installationDbName);
        await page.click('.btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('db_created');
    });

    it("should have set the installation to be 'in progress'", async function() {
        const { firstAccessed, inProgress } = testEnvironment.readInstallationInfoFromLocalConfig();

        expect(inProgress).to.be.true;
        expect(firstAccessed).to.be.equal(installationStarted);
    });

    it("should display the superuser configuration page when next is clicked on the tables created page", async function() {
        await page.click('.next-step .btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('superuser');
    });

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

        // the installation is NOT finished at this point
        // the user needs to click the submit button once more!
        expect(await page.screenshot({ fullPage: true })).to.matchImage('congrats');
    });

    it("should clear the configuration file when restarting the installation process", async function() {
        // restart installation
        await page.goto("");
        await page.waitForSelector('#installation');

        // go to system check
        await page.click('.next-step .btn');
        await page.waitForSelector('[vue-entry="Installation.SystemCheck"]');

        // system check will delete existing configuration file
        // but should keep "first_accessed" information
        const { firstAccessed, inProgress } = testEnvironment.readInstallationInfoFromLocalConfig();

        expect(inProgress).to.be.false;
        expect(firstAccessed).to.be.equal(installationStarted);
    });

    it("should allow reusing an existing database from a previous installation", async function() {
        // go to database setup
        await page.click('.next-step .btn');
        await page.waitForNetworkIdle();

        // submit database credentials
        const { username, password } = testEnvironment.readDbInfoFromConfig();

        await page.type('input[name="username"]', username);
        await page.type('input[name="password"]', password);
        await page.type('input[name="dbname"]', installationDbName);
        await page.click('.btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('db_existing');
    });

    it("should skip to the congrats page as installation was already completed up to that step", async function() {
        await page.click('a[href*="reuseTables"]');
        await page.waitForSelector('.installation-finished');
    });

    it("should continue to piwik after submitting on the privacy settings form in the congrats page", async function() {
        await page.click('.btn');
        await page.waitForNetworkIdle();

        // check login form is displayed
        await page.waitForSelector('.loginForm');

        // check installation is no longer in progress
        const { firstAccessed, inProgress } = testEnvironment.readInstallationInfoFromLocalConfig();

        expect(inProgress).to.be.false;
        expect(firstAccessed).to.be.null;
    });

    it("should display an error page if installation is accessed on an installed instance", async function() {
        // go back to a previous installation step
        await page.goto(pageUrl);
        await page.waitForNetworkIdle();

        // check no installation progress information was added
        const { firstAccessed, inProgress } = testEnvironment.readInstallationInfoFromLocalConfig();

        expect(inProgress).to.be.false;
        expect(firstAccessed).to.be.null;

        expect(await page.screenshot({ fullPage: true })).to.matchImage('already_installed');
    });
});
