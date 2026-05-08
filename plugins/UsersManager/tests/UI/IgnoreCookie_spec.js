/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("IgnoreCookie", function () {
    this.fixture = "Piwik\\Plugins\\UsersManager\\tests\\Fixtures\\ManyUsers";

    var userSettingsUrl = "?module=UsersManager&action=userSettings";

    async function getIgnoreCookieText() {
        return page.evaluate(() => $('.ignoreCookieSettings').text().replace(/\s+/g, ' ').trim());
    }

    it('should show ignore cookie setting on user settings page', async function () {
        await page.goto(userSettingsUrl);
        await page.waitForSelector('.ignoreCookieSettings', { visible: true });

        const text = await getIgnoreCookieText();
        expect(text).to.contain('Your visits are not ignored');
        expect(text).to.contain('Click here to set a cookie');
    });

    it('should set an ignore cookie and reload the page correctly when clicking ignore link', async function () {
      await page.click('.ignoreCookieSettings a');
      await page.waitForNetworkIdle();
      await page.waitForSelector('.ignoreCookieSettings', { visible: true });

      var cookies = await page.cookies();
      var ignoreCookie = cookies.filter((cookie) => cookie.name === 'matomo_ignore');

      expect(ignoreCookie.length).to.eq(1);

      const text = await getIgnoreCookieText();
      expect(text).to.contain('Your visits are ignored');
      expect(text).to.contain('Click here to delete the cookie');
    });

    it('should remove ignore cookie and reload the page correctly when clicking ignore link again', async function () {
      await page.click('.ignoreCookieSettings a');
      await page.waitForNetworkIdle();
      await page.waitForSelector('.ignoreCookieSettings', { visible: true });

      var cookies = await page.cookies();
      var ignoreCookie = cookies.filter((cookie) => cookie.name === 'matomo_ignore');

      expect(ignoreCookie.length).to.eq(0);

      const text = await getIgnoreCookieText();
      expect(text).to.contain('Your visits are not ignored');
      expect(text).to.contain('Click here to set a cookie');
    });

    it('should fail when directly opening the ignore cookie action without a nonce', async function () {
      await page.goto('?module=UsersManager&action=setIgnoreCookie');

      expect(await page.evaluate(() => document.getElementsByClassName('header')[0].innerText)).to.contain('An error occurred');
    });
});
