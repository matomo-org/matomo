/*!
 * Matomo - free/libre analytics platform
 *
 * Dashboard screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('Notifications', function () {
    this.timeout(5*60*1000); // timeout of 5 minutes per test

    this.fixture = "Piwik\\Tests\\Fixtures\\OneVisit";

    var url = "?module=CoreAdminHome&action=home&idSite=1&period=day&date=yesterday";

    it('should show notifications', async function () {
        await page.goto(url + '&setNotifications=1');
        await page.waitForNetworkIdle();

        var elem = await page.waitForSelector('#notificationContainer');

        expect(await elem.screenshot()).to.matchImage('loaded');
    });

    it('should still show persistent notifications on reload', async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();

        var elem = await page.waitForSelector('#notificationContainer');

        expect(await elem.screenshot()).to.matchImage('reloaded');
    });

    it('should close a notification', async function () {
        await page.click('.notification:first-child .close');
        await page.waitForNetworkIdle();

        var elem = await page.waitForSelector('#notificationContainer');

        expect(await elem.screenshot()).to.matchImage('close');
    });

    it('should still be closed on reload', async function () {
        await page.reload();
        await page.waitForNetworkIdle();

        var elem = await page.waitForSelector('#notificationContainer');

        expect(await elem.screenshot()).to.matchImage('close_reload');
    });
});
