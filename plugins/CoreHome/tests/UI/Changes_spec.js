/*!
 * Matomo - free/libre analytics platform
 *
 * Dashboard screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('Changes', function () {
    this.timeout(0);

    this.fixture = "Piwik\\Tests\\Fixtures\\CreateChanges";

    var url = "?module=CoreAdminHome&action=home&idSite=1&period=day&date=yesterday";

    it('should show changes', async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();

        await page.click('.right > li:nth-child(5) > a:nth-child(1)');
        await page.waitForNetworkIdle();

        var elem = await page.waitForSelector('#Piwik_Popover');

        expect(await elem.screenshot()).to.matchImage('show_popover');
    });

});
