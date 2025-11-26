/*!
 * Matomo - free/libre analytics platform
 *
 * login & password reset screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("NoAccess", function () {
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

        const loginPage = await page.waitForSelector('#loginPage', {visible: true});
        expect(loginPage).to.be.ok;

        const expectedText = 'Error: You can\'t access this resource as it requires \'view\' access for the website id = 2.';
        const notificationText = await page.$eval('div.system.notification-error .notification-body', el => el.textContent.trim());
        expect(notificationText).to.equal(expectedText);
    });

    it("should show session timeout error", async function() {
        await page.clearCookies();
        await page.goto("?module=CoreHome&action=index&idSite=1&period=day&date=yesterday#?idSite=1&period=day&date=yesterday&category=General_Visitors&subcategory=General_Overview");
        await page.waitForNetworkIdle();
        await page.type("#login_form_login", "oliverqueen");
        await page.type("#login_form_password", "smartypants");
        await page.evaluate(function(){
            $('#login_form_submit').click();
        });

        await page.waitForTimeout(60500); // wait for session timeout

        await page.click('#topmenu-corehome');
        await page.waitForNetworkIdle();

        const loginPage = await page.waitForSelector('#loginPage', {visible: true});
        expect(loginPage).to.be.ok;

        const expectedText = 'Error: Your session has expired due to inactivity. Please log in to continue.';
        const notificationText = await page.$eval('div.system.notification-error .notification-body', el => el.textContent.trim());
        expect(notificationText).to.equal(expectedText);
    });

});
