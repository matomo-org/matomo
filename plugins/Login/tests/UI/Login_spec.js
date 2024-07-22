/*!
 * Matomo - free/libre analytics platform
 *
 * login & password reset screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Login", function () {
    this.timeout(0);

    var md5Pass = "0adcc0d741277f74c64c8abab7330d1c", // md5("smarty-pants")
        formlessLoginUrl = "?module=Login&action=logme&login=oliverqueen&password=" + md5Pass,
        bruteForceLogUrl = "?module=Login&action=bruteForceLog",
        apiAuthUrl = "?module=API&method=UsersManager.getTokenAuth&format=json&userLogin=ovliverqueen&md5Password=" + md5Pass;

    before(async function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.queryParamOverride = {date: "2012-01-01", period: "year"};
        testEnvironment.save();
    });

    beforeEach(function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.queryParamOverride = {date: "2012-01-01", period: "year"};
        testEnvironment.save();
    });

    after(async function () {
        testEnvironment.overrideConfig('General', 'login_allow_logme', '0')
        testEnvironment.testUseMockAuth = 1;
        delete testEnvironment.bruteForceBlockIps;
        delete testEnvironment.bruteForceBlockThisIp;
        delete testEnvironment.queryParamOverride;
        delete testEnvironment.configOverride.General;
        testEnvironment.save();
    });

    afterEach(function () {
        testEnvironment.testUseMockAuth = 1;
        delete testEnvironment.bruteForceBlockIps;
        delete testEnvironment.bruteForceBlockThisIp;
        delete testEnvironment.queryParamOverride;
        delete testEnvironment.configOverride.General;
        testEnvironment.save();
    });

    it("should show error when trying to log in through login form", async function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.bruteForceBlockThisIp = 1;
        delete testEnvironment.bruteForceBlockIps;
        delete testEnvironment.queryParamOverride;
        testEnvironment.save();

        await page.goto("");
        expect(await page.screenshot({ fullPage: true })).to.matchImage('bruteforcelog_blockedlogin');
    });

    it("should load correctly", async function() {
        await page.goto("");
        await page.waitForNetworkIdle();
        await page.waitForSelector('input');
        await page.mouse.click(0, 0);
        await page.waitForTimeout(250);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('login_form');
    });

    it("should fail when incorrect credentials are supplied", async function() {
        await page.type('#login_form_login', 'superUserLogin');
        await page.type('#login_form_password', 'wrongpassword');
        await page.evaluate(function(){
            $('#login_form_submit').click();
        });
        await page.waitForNetworkIdle();
        await page.waitForSelector('.notification');
        await page.mouse.click(0, 0);
        await page.waitForTimeout(250);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('login_fail');
    });

    it("should redirect to Matomo when correct credentials are supplied", async function() {
        await page.type("#login_form_login", superUserLogin);
        await page.type("#login_form_password", superUserPassword);
        await page.evaluate(function(){
            $('#login_form_submit').click();
        });
        await page.waitForNetworkIdle();

        // check dashboard is shown
        await page.waitForSelector('#dashboard');
        await page.waitForNetworkIdle();
    });

    it("should redirect to login when logout link clicked", async function() {
        await page.click("nav .right .icon-sign-out");
        await page.waitForNetworkIdle();
        await page.waitForSelector('input');
        await page.mouse.click(0, 0);
        await page.waitForTimeout(250);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('login_form_logout');
    });

    it("login with email and password should work", async function() {
        await page.type("#login_form_login", "hello@example.org");
        await page.type("#login_form_password", superUserPassword);
        await page.evaluate(function(){
            $('#login_form_submit').click();
        });

        // check dashboard is shown
        await page.waitForNetworkIdle();
        await page.waitForSelector('#dashboard');
    });

    it("should display password reset form when forgot password link clicked", async function() {
        await page.click("nav .right .icon-sign-out");
        await page.waitForNetworkIdle();
        await page.waitForSelector("a#login_form_nav");
        await page.click("a#login_form_nav");
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('forgot_password');
    });

    it("should show reset password form and error message on error", async function() {
        await page.type("#reset_form_login", superUserLogin);
        await page.type("#reset_form_password", superUserPassword + '2');
        await page.click("#reset_form_submit");
        await page.waitForNetworkIdle();
        await page.waitForSelector('.notification');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('password_reset_error');
    });

    it("should send email when password reset form submitted", async function() {
        await page.reload();
        await page.click("a#login_form_nav");
        await page.type("#reset_form_login", superUserLogin);
        await page.type("#reset_form_password", superUserPassword + '2');
        await page.type("#reset_form_password_bis", superUserPassword + '2');
        await page.click("#reset_form_submit");
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('password_reset');
    });

    it("should show reset password confirmation page when password reset link is clicked", async function() {
        var expectedMailOutputFile = PIWIK_INCLUDE_PATH + '/tmp/Login.resetPassword.mail.json',
            fileContents = require("fs").readFileSync(expectedMailOutputFile),
            mailSent = JSON.parse(fileContents),
            resetUrl = mailSent.contents.match(/http:\/\/[^"]+resetToken[^"]+"/);

        if (!resetUrl || !resetUrl[0]) {
            throw new Error(`Could not find reset URL in email, captured mail info: ${fileContents}`)
        }
        resetUrl = resetUrl[0].replace(/\"$/, '');
        resetUrl = await page.evaluate((resetUrl) => {
            return piwikHelper.htmlDecode(resetUrl);
        }, resetUrl);

        await page.goto(resetUrl);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('password_reset_confirm');
    });

    it("should reset password when password reset link is clicked", async function() {

        await page.type("#mtmpasswordconfirm", superUserPassword + '2');
        await page.click("#login_reset_confirm");
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('password_reset_complete');
    });

    it("should login successfully when new credentials used", async function() {
        await page.type("#login_form_login", superUserLogin);
        await page.type("#login_form_password", superUserPassword + '2');
        await page.evaluate(function(){
            $('#login_form_submit').click();
        });

        // check dashboard is shown
        await page.waitForNetworkIdle();
        await page.waitForSelector('#dashboard');
    });

    it("should show error when formless login used, but disabled", async function () {
        testEnvironment.overrideConfig('General', 'login_allow_logme', '0')
        testEnvironment.save();
        await page.click("nav .right .icon-sign-out");
        await page.waitForNetworkIdle();

        await page.goto(formlessLoginUrl);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('logme_disabled');
    });

    it("should login successfully when formless login used", async function() {
        testEnvironment.overrideConfig('General', 'login_allow_logme', '1')
        testEnvironment.save();
        await page.goto("about:blank");
        await page.goto(formlessLoginUrl);

        // check dashboard is shown
        await page.waitForNetworkIdle();
        await page.waitForSelector('#dashboard');
    });

    it('should not show login page when ips whitelisted and ip is not matching', async function() {
        testEnvironment.overrideConfig('General', 'login_allowlist_ip', ['199.199.199.199']);
        testEnvironment.save();
        await page.goto('');
        await page.waitForNetworkIdle();

        const element = await page.$('.box');
        expect(await element.screenshot()).to.matchImage('ip_not_whitelisted');
    });

    it("should show brute force log url when there are no entries", async function () {
        testEnvironment.testUseMockAuth = 1;
        delete testEnvironment.queryParamOverride;
        delete testEnvironment.bruteForceBlockThisIp;
        delete testEnvironment.bruteForceBlockIps;
        testEnvironment.overrideConfig('General', 'login_allowlist_ip', []);
        testEnvironment.save();

        await page.goto(bruteForceLogUrl);

        expect(await page.screenshotSelector('#content')).to.matchImage('bruteforcelog_noentries');
    });

    it("should show brute force log url when there are entries", async function () {
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.bruteForceBlockIps = 1;
        delete testEnvironment.bruteForceBlockThisIp;
        delete testEnvironment.queryParamOverride;
        testEnvironment.save();

        await page.goto(bruteForceLogUrl);

        expect(await page.screenshotSelector('#content')).to.matchImage('bruteforcelog_withentries');
    });

    it("should show error when trying to attempt a log in through API", async function () {
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.bruteForceBlockThisIp = 1;
        delete testEnvironment.bruteForceBlockIps;
        delete testEnvironment.queryParamOverride;
        testEnvironment.save();

        await page.goto(apiAuthUrl);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('bruteforcelog_blockedapi');
    });

    it("should show error when trying to log in through logme", async function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.bruteForceBlockThisIp = 1;
        delete testEnvironment.bruteForceBlockIps;
        delete testEnvironment.queryParamOverride;
        testEnvironment.save();

        await page.goto(formlessLoginUrl);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('bruteforcelog_blockedlogme');
    });

    it("should show invalid host warning if redirect url is not trusted in logme", async function () {
        testEnvironment.overrideConfig('General', 'login_allow_logme', '1')
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();

        await page.goto(formlessLoginUrl + "&url="+encodeURIComponent("https://www.matomo.org/security"));

        expect(await page.screenshot({ fullPage: true })).to.matchImage('logme_redirect_invalid');
    });

    it("should redirect if host is trusted in logme", async function () {
        testEnvironment.overrideConfig('General', 'login_allow_logme', '1');
        testEnvironment.overrideConfig('General', 'trusted_hosts', ["matomo.org"]);
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();

        await page.goto(formlessLoginUrl + "&url="+encodeURIComponent("https://matomo.org/security/"));

        expect(await page.getWholeCurrentUrl()).to.equal("https://matomo.org/security/");
    });

    it("should correctly redirect for unencoded url", async function () {
        testEnvironment.overrideConfig('General', 'login_allow_logme', '1');
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();

        await page.goto(formlessLoginUrl + "&url=//google.com\\@localhost/path");

        expect(await page.getWholeCurrentUrl()).to.equal("http://localhost/path"); // username part is hidden
    });

    it("should not redirect to invalid url", async function () {
        testEnvironment.overrideConfig('General', 'login_allow_logme', '1');
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();

        await page.goto(formlessLoginUrl + "&url=http:google.com");

        expect(await page.getWholeCurrentUrl()).to.contain(formlessLoginUrl + "&url=http:google.com"); // no redirect
        expect(await page.evaluate(() => document.getElementsByClassName('content')[0].innerText)).to.contain('The redirect URL is not valid.');
    });
});
