/*!
 * Matomo - free/libre analytics platform
 *
 * Login screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Login", function () {
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

    // auto-clear-password directive applied
    it('should have the auto-clear-password directive applied to the password field', async function () {
      const element = await page.$('input[type="password"][data-auto-clear-enabled="true"]');
      expect(element).to.be.ok;
    });

    // Empty field validation.
    it('should report the empty fields instead of submitting the login form', async function () {
      await page.goto('?module=CoreHome&action=index&idSite=1&period=week&date=2017-06-04');

      // The button is always enabled, and native validation is off now that the handler is bound.
      await page.waitForSelector('#login_form_submit:not([disabled])');
      await page.waitForSelector('#login_form[novalidate]');

      const urlBeforeSubmit = page.url();

      // Both fields empty: both are reported, and the form is not submitted.
      await page.click('#login_form_submit');
      await page.waitForSelector('#login_form_errors .notification');

      let errors = await page.$eval('#login_form_errors', el => el.innerText);
      expect(errors).to.contain('Username or e-mail required');
      expect(errors).to.contain('Password required');
      expect(page.url()).to.equal(urlBeforeSubmit);

      // Only the password missing: only that is reported, and the banners don't stack.
      await page.type('#login_form_login', 'u');
      await page.click('#login_form_submit');
      await page.waitForFunction(
        () => !$('#login_form_errors').text().includes('Username or e-mail required')
      );

      errors = await page.$eval('#login_form_errors', el => el.innerText);
      expect(errors).to.contain('Password required');
      expect(await page.$$('#login_form_errors .notification')).to.have.lengthOf(1);
      expect(page.url()).to.equal(urlBeforeSubmit);

      // Reset the field for the tests that follow.
      await page.evaluate(() => { $('#login_form_login').val(''); });
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

    it("should go back to login form on logout", async function() {
        await page.click("nav .right .icon-sign-out");
        await page.waitForNetworkIdle();
        await page.waitForSelector("a#login_form_nav");
    });

    it("should show error when formless login used, but disabled", async function () {
        testEnvironment.overrideConfig('General', 'login_allow_logme', '0')
        testEnvironment.save();

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
        await page.clearCookies(); // ensure user is logged out
        testEnvironment.overrideConfig('General', 'login_allow_logme', '1')
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();

        await page.goto(formlessLoginUrl + "&url="+encodeURIComponent("https://www.matomo.org/security"));

        expect(await page.getWholeCurrentUrl()).to.contain(formlessLoginUrl + "&url=" + encodeURIComponent("https://www.matomo.org/security")); // no redirect
        expect(await page.evaluate(() => document.getElementsByClassName('content')[0].innerText)).to.contain('The redirect URL host is not valid, it is not a trusted host.');
    });

    it("should show error when superuser tries to use logme", async function () {
        await page.clearCookies(); // ensure user is logged out
        testEnvironment.overrideConfig('General', 'login_allow_logme', '1')
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();

        await page.goto('?module=Login&action=logme&login=' + superUserLogin + '&password=6495dcb1a3a3353b109562954b5514d3'); // md5 of superUserPassword

        expect(await page.getWholeCurrentUrl()).to.contain('?module=Login&action=logme&login=' + superUserLogin + '&password=6495dcb1a3a3353b109562954b5514d3'); // no redirect
        expect(await page.evaluate(() => document.getElementsByClassName('content')[0].innerText)).to.contain('A user with superuser access cannot be authenticated using the \'logme\' mechanism.');
    });

    it("should redirect if host is trusted in logme", async function () {
        await page.clearCookies(); // ensure user is logged out
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

        expect(await page.getWholeCurrentUrl()).to.match(/https?:\/\/localhost\/path/); // username part is hidden
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
