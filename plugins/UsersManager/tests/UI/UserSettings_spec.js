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
        await page.click('#newsletterSignupBtn');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('#newsletterSignup')).to.matchImage('signup_success');
    });

    it('should not prompt user to subscribe again', async function () {
        // Assumes previous test has clicked on the signup button - so we shouldn't see it this time
        await page.goto(url);
        expect(await page.screenshotSelector('.admin')).to.matchImage('already_signed_up');
    });
});