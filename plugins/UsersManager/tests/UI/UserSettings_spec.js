/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("UserSettings", function () {
    this.timeout(0);
    this.fixture = "Piwik\\Plugins\\UsersManager\\tests\\Fixtures\\ManyUsers";

    var url = "?module=UsersManager&action=userSettings";

    before(async function() {
        await page.webpage.setViewport({
            width: 1250,
            height: 768
        });
    });

    it('should show user settings page', async function () {
        await page.goto(url);
        expect(await page.screenshotSelector('.admin')).to.matchImage('load');
    });

    it('should allow user to subscribe to newsletter', async function () {
        await page.click('#newsletterSignup label');
        await page.click('#newsletterSignupBtn input');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.pageWrap')).to.matchImage('signup_success');
    });

    it('should not prompt user to subscribe to newsletter again', async function () {
        // Assumes previous test has clicked on the signup button - so we shouldn't see it this time
        await page.goto(url);
        expect(await page.screenshotSelector('.admin')).to.matchImage('already_signed_up');
    });

    it('should ask for password confirmation when changing email', async function () {
        await page.evaluate(function () {
            $('#userSettingsTable input#email').val('testlogin123@example.com').change();
        });
        await page.click('#userSettingsTable [piwik-save-button] .btn');
        await page.waitFor(500); // wait for animation

        let pageWrap = await page.$('.modal.open');
        expect(await pageWrap.screenshot()).to.matchImage('asks_confirmation');
    });

    it('should load error when wrong password specified', async function () {
        await page.type('.modal.open #currentPassword', 'foobartest123');
        btnNo = await page.jQuery('.modal.open .modal-action:not(.modal-no)');
        await btnNo.click();
        await page.waitForNetworkIdle();

        let pageWrap = await page.$('#notificationContainer');
        expect(await pageWrap.screenshot()).to.matchImage('wrong_password_confirmed');
    });
});