/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("IgnoreCookie", function () {
    this.timeout(0);
    this.fixture = "Piwik\\Plugins\\UsersManager\\tests\\Fixtures\\ManyUsers";

    var userSettingsUrl = "?module=UsersManager&action=userSettings";

    it('should show ignore cookie setting on user settings page', async function () {
        await page.goto(userSettingsUrl);
        expect(await page.screenshotSelector('.ignoreCookieSettings')).to.matchImage('loaded');
    });

    it('should set an ignore cookie and reload the page correctly when clicking ignore link', async function () {
      await page.click('.ignoreCookieSettings a');
      await page.waitForNetworkIdle();

      var cookies = await page.cookies();
      var ignoreCookie = cookies.filter((cookie) => cookie.name === 'matomo_ignore');

      expect(ignoreCookie.length).to.eq(1);
      expect(await page.screenshotSelector('.ignoreCookieSettings')).to.matchImage('ignored');
    });

    it('should remove ignore cookie and reload the page correctly when clicking ignore link again', async function () {
      await page.click('.ignoreCookieSettings a');
      await page.waitForNetworkIdle();

      var cookies = await page.cookies();
      var ignoreCookie = cookies.filter((cookie) => cookie.name === 'matomo_ignore');

      expect(ignoreCookie.length).to.eq(0);
      expect(await page.screenshotSelector('.ignoreCookieSettings')).to.matchImage('reset');
    });

    it('should fail when directly opening the ignore cookie action without a nonce', async function () {
      await page.goto('?module=UsersManager&action=setIgnoreCookie');

      expect(await page.evaluate(() => document.getElementsByClassName('header')[0].innerText)).to.contain('An error occurred');
    });
});
