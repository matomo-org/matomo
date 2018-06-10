/*!
 * Piwik - free/libre analytics platform
 *
 * login & password reset screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Login", function () {
    this.timeout(0);

    var md5Pass = "0adcc0d741277f74c64c8abab7330d1c", // md5("smarty-pants")
        formlessLoginUrl = "?module=Login&action=logme&login=oliverqueen&password=" + md5Pass,
        bruteForceLogUrl = "?module=Login&action=bruteForceLog",
        apiAuthUrl = "?module=API&method=UsersManager.getTokenAuth&format=json&userLogin=ovliverqueen&md5Password=" + md5Pass;

    before(function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.queryParamOverride = {date: "2012-01-01", period: "year"};
        testEnvironment.save();
    });

    beforeEach(function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.queryParamOverride = {date: "2012-01-01", period: "year"};
        testEnvironment.save();
    });

    after(function () {
        testEnvironment.testUseMockAuth = 1;
        delete testEnvironment.bruteForceBlockIps;
        delete testEnvironment.bruteForceBlockThisIp;
        delete testEnvironment.queryParamOverride;
        testEnvironment.save();
    });

    afterEach(function () {
        testEnvironment.testUseMockAuth = 1;
        delete testEnvironment.bruteForceBlockIps;
        delete testEnvironment.bruteForceBlockThisIp;
        delete testEnvironment.queryParamOverride;
        testEnvironment.save();
    });

    it("should show error when trying to log in through login form", function (done) {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.bruteForceBlockThisIp = 1;
        delete testEnvironment.bruteForceBlockIps;
        delete testEnvironment.queryParamOverride;
        testEnvironment.save();

        expect.screenshot("bruteforcelog_blockedlogin").to.be.capture(function (page) {
            page.load("");
        }, done);
    });

    it("should load correctly", async function() {
        expect.screenshot("login_form").to.be.capture(function (page) {
            page.load("");
        }, done);
    });

    it("should fail when incorrect credentials are supplied", async function() {
        expect.screenshot("login_fail").to.be.capture(function (page) {
            page.sendKeys('#login_form_login', 'superUserLogin');
            page.sendKeys('#login_form_password', 'wrongpassword');
            page.click('#login_form_submit');
        }, done);
    });

    it("should redirect to Piwik when correct credentials are supplied", async function() {
        expect.current_page.contains("#dashboard", function (page) {
            page.sendKeys("#login_form_login", "superUserLogin");
            page.sendKeys("#login_form_password", "superUserPass");
            page.click("#login_form_submit");
        }, done);
    });

    it("should redirect to login when logout link clicked", async function() {
        expect.screenshot("login_form").to.be.capture("logout_form", function (page) {
            page.click("nav .right .icon-sign-out");
        }, done);
    });

    it("login with email and password should work", async function() {
        expect.current_page.contains("#dashboard", function (page) {
            page.sendKeys("#login_form_login", "hello@example.org");
            page.sendKeys("#login_form_password", "superUserPass");
            page.click("#login_form_submit");
        }, done);
    });

    it("should display password reset form when forgot password link clicked", async function() {
        expect.screenshot("forgot_password").to.be.capture(function (page) {
            page.click("nav .right .icon-sign-out");
            page.click("a#login_form_nav");
        }, done);
    });

    it("should show reset password form and error message on error", async function() {
        expect.screenshot("password_reset_error").to.be.capture(function (page) {
            page.sendKeys("#reset_form_login", "superUserLogin");
            page.sendKeys("#reset_form_password", "superUserPass2");
            page.click("#reset_form_submit", 3000);
        }, done);
    });

    it("should send email when password reset form submitted", async function() {
        expect.screenshot("password_reset").to.be.capture(function (page) {
            page.reload();
            page.click("a#login_form_nav");
            page.sendKeys("#reset_form_login", "superUserLogin");
            page.sendKeys("#reset_form_password", "superUserPass2");
            page.sendKeys("#reset_form_password_bis", "superUserPass2");
            page.click("#reset_form_submit", 3000);
        }, done);
    });

    it("should reset password when password reset link is clicked", async function() {
        expect.screenshot("password_reset_complete").to.be.capture(function (page) {
            var expectedMailOutputFile = PIWIK_INCLUDE_PATH + '/tmp/Login.resetPassword.mail.json',
                mailSent = JSON.parse(require("fs").read(expectedMailOutputFile)),
                resetUrl = mailSent.contents.match(/http:\/\/.*/)[0];

            page.load(resetUrl);
        }, done);
    });

    it("should login successfully when new credentials used", async function() {
        expect.page("").contains("#dashboard", function (page) {
            page.sendKeys("#login_form_login", "superUserLogin");
            page.sendKeys("#login_form_password", "superUserPass2");
            page.click("#login_form_submit");
        }, done);
    });

    it("should login successfully when formless login used", async function() {
        expect.page("").contains('#dashboard', /*'formless_login',*/ function (page) {
            page.click("nav .right .icon-sign-out");
            page.load(formlessLoginUrl);
        }, done);
    });

    it('should not show login page when ips whitelisted and ip is not matching', async function() {
        expect.screenshot('ip_not_whitelisted').to.be.captureSelector('.box', function (page) {
            testEnvironment.overrideConfig('General', 'login_whitelist_ip', ['199.199.199.199']);
            testEnvironment.save();
            page.load('');
        }, done);
    });

    it("should show brute force log url when there are no entries", function (done) {
        testEnvironment.testUseMockAuth = 1;
        delete testEnvironment.queryParamOverride;
        delete testEnvironment.bruteForceBlockThisIp;
        delete testEnvironment.bruteForceBlockIps;
        testEnvironment.overrideConfig('General', 'login_whitelist_ip', []);
        testEnvironment.save();

        expect.screenshot("bruteforcelog_noentries").to.be.capture(function (page) {
            page.load(bruteForceLogUrl);
        }, done);
    });

    it("should show brute force log url when there are entries", function (done) {
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.bruteForceBlockIps = 1;
        delete testEnvironment.bruteForceBlockThisIp;
        delete testEnvironment.queryParamOverride;
        testEnvironment.save();

        expect.screenshot("bruteforcelog_withentries").to.be.capture(function (page) {
            page.load(bruteForceLogUrl);
        }, done);
    });

    it("should show error when trying to attempt a log in through API", function (done) {
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.bruteForceBlockThisIp = 1;
        delete testEnvironment.bruteForceBlockIps;
        delete testEnvironment.queryParamOverride;
        testEnvironment.save();

        expect.screenshot("bruteforcelog_blockedapi").to.be.capture(function (page) {
            page.load(apiAuthUrl);
        }, done);
    });

    it("should show error when trying to log in through logme", function (done) {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.bruteForceBlockThisIp = 1;
        delete testEnvironment.bruteForceBlockIps;
        delete testEnvironment.queryParamOverride;
        testEnvironment.save();

        expect.screenshot("bruteforcelog_blockedlogme").to.be.capture(function (page) {
            page.load(formlessLoginUrl);
        }, done);
    });
});