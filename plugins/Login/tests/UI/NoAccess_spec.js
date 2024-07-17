/*!
 * Matomo - free/libre analytics platform
 *
 * login & password reset screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("NoAccess", function () {
    this.timeout(5*60*1000); // timeout of 5 minutes per test

    before(async function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.overrideConfig('General', 'login_session_not_remembered_idle_timeout', 1)
        testEnvironment.save();
    });

    after(async function () {
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    it("should login successfully with user credentials and show error when a site without access is viewed", async function() {
        await page.goto("?idSite=2");
        await page.waitForNetworkIdle();
        await page.type("#login_form_login", "oliverqueen");
        await page.type("#login_form_password", "smartypants");
        await page.evaluate(function(){
            $('#login_form_submit').click();
        });

        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('login_noaccess');
    });

    it("should show session timeout error", async function() {
        await page.clearCookies();
        await page.goto("");
        await page.waitForNetworkIdle();
        await page.type("#login_form_login", "oliverqueen");
        await page.type("#login_form_password", "smartypants");
        await page.evaluate(function(){
            $('#login_form_submit').click();
        });

        await page.waitForTimeout(60500); // wait for session timeout

        await page.click('#topmenu-corehome');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('login_session_timeout');
    });

});
