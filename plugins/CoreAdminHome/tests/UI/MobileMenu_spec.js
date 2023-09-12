/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("MobileMenu", function () {
    this.timeout(0);
    before(function () {
      testEnvironment.pluginsToLoad = ['CorePluginsAdmin'];
      testEnvironment.save();

    });
    it('menu should load and be able to expand to see sub menus on mobile', async function() {
        const screenshotName  = 'mobileMenuPartial';
        const contentSelector = '#mobile-left-menu';

        await page.webpage.setViewport({ width: 815, height: 512 });
        await page.goto('?module=CoreAdminHome&action=home');
        await page.waitForNetworkIdle();
        await page.click('[data-target="mobile-left-menu"]');
        await page.waitForTimeout(150);
        await page.click('ul#mobile-left-menu > li:nth-child(2) a');
        await page.waitForTimeout(250);

        expect(await page.screenshotSelector(contentSelector)).to.matchImage(screenshotName);
    });
});
