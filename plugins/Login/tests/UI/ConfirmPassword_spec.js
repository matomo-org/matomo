/*!
 * Matomo - free/libre analytics platform
 *
 * Password confirmation screen tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ConfirmPassword", function () {
    // Reached by asking for an action that requires a recently verified password, which is the
    // only way in: the controller refuses unless a verification has actually been requested.
    var addNewTokenUrl = "?module=UsersManager&action=addNewToken&idSite=1&period=day&date=yesterday";

    before(async function () {
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    it("should report the empty password instead of submitting the confirmation form", async function () {
        await page.goto(addNewTokenUrl);
        await page.waitForNetworkIdle();

        // login.js sets `novalidate` when it takes the form's validation over, so waiting on it
        // means the handler is bound. The confirm button is always enabled now.
        await page.waitForSelector('#confirm_password_form[novalidate]');
        await page.waitForSelector('#login_form_submit:not([disabled])');

        const urlBeforeSubmit = page.url();
        const notifications = async () => (await page.$$('.loginForm .message_container .notification')).length;

        expect(await notifications()).to.equal(0);

        await page.click('#login_form_submit');
        await page.waitForSelector('#login_form_errors .notification');

        const errors = await page.$eval('#login_form_errors', el => el.innerText);
        expect(errors).to.contain('Password required');
        expect(page.url()).to.equal(urlBeforeSubmit);

        // Reported once, in the container the server uses for its own errors.
        expect(await notifications()).to.equal(1);
        expect(await page.evaluate(() => document.activeElement.id)).to.equal('login_form_password');
    });

    it("should not stack banners when the empty form is submitted again", async function () {
        await page.goto(addNewTokenUrl);
        await page.waitForNetworkIdle();
        await page.waitForSelector('#confirm_password_form[novalidate]');

        await page.click('#login_form_submit');
        await page.waitForSelector('#login_form_errors .notification');
        await page.click('#login_form_submit');
        await page.waitForSelector('#login_form_errors .notification');

        const notifications = await page.$$('.loginForm .message_container .notification');
        expect(notifications.length).to.equal(1);
    });
});
