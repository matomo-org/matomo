/*!
 * Matomo - free/libre analytics platform
 *
 * Installation screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
var fs = require('fs');

describe("Installation", function () {
    this.timeout(0);

    this.fixture = null;

    before(function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.configFileLocal = path.join(PIWIK_INCLUDE_PATH, "/tmp/test.config.ini.php");
        testEnvironment.dontUseTestConfig = true;
        testEnvironment.tablesPrefix = 'piwik_';
        testEnvironment.save();

        if (fs.exists(testEnvironment.configFileLocal)) {
            fs.remove(testEnvironment.configFileLocal);
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
        expect.screenshot("access_no_config").to.be.capture(function (page) {
            page.load("?module=CoreHome&action=index&ignoreClearAllViewDataTableParameters=1");

            page.evaluate(function () {
                // ensure screenshots are reporting travis config file for comparison
                // no jQuery existing on these error pages...
                document.body.innerHTML = document.body.innerHTML.replace(
                    /{\/.*\/test\.config\.ini\.php}/,
                    '{/home/travis/build/piwik/piwik/tests/lib/screenshot-testing/../../../tmp/test.config.ini.php}'
                );
            });
        }, done);
    });

    it("should start the installation process when the index is visited w/o a config.ini.php file", async function() {
        expect.screenshot("start").to.be.capture(function (page) {
            page.load("?ignoreClearAllViewDataTableParameters=1");
        }, done);
    });

    it("should display the system check page when next is clicked on the first page", async function() {
        expect.screenshot("system_check").to.be.capture(function (page) {
            page.click('.next-step .btn');
        }, done);
    });

    var pageUrl;
    it("should have already created a tmp/sessions/index.htm file to prevent directory listing", async function() {
        expect.screenshot('nothing_to_see_here').to.be.capture(function (page) {
            pageUrl = page.url();

            // page.load will load by default the proxy ie. http://localhost/piwik/tests/PHPUnit/proxy/
            // but we need here to check in: http://localhost/piwik/tmp/sessions/
            page.load("../../../tmp/sessions/index.htm");

        }, done);
    });

    it("should display the database setup page when next is clicked on the system check page", async function() {
        expect.screenshot("db_setup").to.be.capture(function (page) {
            page.load(pageUrl);

            page.click('.next-step .btn');
        }, done);
    });

    it("should fail when the next button is clicked and no database info is entered in the form", async function() {
        expect.screenshot("db_setup_fail").to.be.capture(function (page) {
            page.click('.btn');
        }, done);
    });

    it("should display the tables created page when next is clicked on the db setup page w/ correct info entered in the form", async function() {
        expect.screenshot("db_created").to.be.capture(function (page) {
            var dbInfo = testEnvironment.readDbInfoFromConfig();
            var username = dbInfo.username;
            var password = dbInfo.password;

            page.sendKeys('input[name=username]', username);

            if (password) {
                page.sendKeys('input[name=password]', password);
            }

            page.sendKeys('input[name=dbname]', 'newdb');
            page.click('.btn');
        }, done);
    });

    it("should display the superuser configuration page when next is clicked on the tables created page", async function() {
        expect.screenshot("superuser").to.be.capture(function (page) {
            page.click('.next-step .btn');
        }, done);
    });

    var pageUrl, pageUrlDe;

    it("should un-select Professional Services newsletter checkbox when language is German", async function() {
        expect.screenshot("superuser_de").to.be.capture(function (page) {
            pageUrl = page.url();
            pageUrlDe = pageUrl + '&language=de'
            page.load(pageUrlDe);
        }, done);
    });

    it("should fail when incorrect information is entered in the superuser configuration page", async function() {
        expect.screenshot("superuser_fail").to.be.capture(function (page) {
            page.load(pageUrl);
            page.click('.btn');
        }, done);
    });

    it("should display the setup a website page when next is clicked on the filled out superuser config page", async function() {
        expect.screenshot("setup_website").to.be.capture(function (page) {
            page.sendKeys('input[name=login]', 'thesuperuser');
            page.sendKeys('input[name=password]', 'thepassword');
            page.sendKeys('input[name=password_bis]', 'thepassword');
            page.sendKeys('input[name=email]', 'hello@piwik.org');
            page.click('.btn');
            page.wait(3000);
        }, done);
    });

    it("should should fail when incorrect information is entered in the setup a website page", async function() {
        expect.screenshot("setup_website_fail").to.be.capture(function (page) {
            page.click('.btn');
        }, done);
    });

    it("should display the javascript tracking page when correct information is entered in the setup website page and next is clicked", async function() {
        expect.screenshot("js_tracking").to.be.capture(function (page) {
            page.sendKeys('input[name=siteName]', 'Serenity');
            page.evaluate(function () {
                // cannot use sendKeys since quickform does not use placeholder attribute
                $('input[name=url]').val('serenity.com');
                
                $('select[name=timezone]').val('Europe/Paris');
                $('select[name=ecommerce]').val('1');
            });
            page.click('.btn');
            page.wait(3000);

            // manually remove port in tracking code, since ui-test.php won't be using the correct INI config file
            page.evaluate(function () {
                $('pre').each(function () {
                    var html = $(this).html();
                    html = html.replace(/localhost\:[0-9]+/g, 'localhost');
                    $(this).html(html);
                });
            });
        }, done);
    });

    it("should display the congratulations page when next is clicked on the javascript tracking page", async function() {
        expect.screenshot("congrats").to.be.capture(function (page) {
            page.click('.next-step .btn');
        }, done);
    });

    it("should continue to piwik after submitting on the privacy settings form in the congrats page", async function() {
        expect.current_page.contains('.loginForm', function (page) {
            page.click('.btn');
        }, done);
    });
});
