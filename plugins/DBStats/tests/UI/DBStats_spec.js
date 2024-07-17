/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot tests for the DBStats plugin.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("DBStats", function () {
    this.timeout(5*60*1000); // timeout of 5 minutes per test

    const url = "?module=DBStats&action=index&idSite=1&period=day&date=yesterday";

    it("should load correctly", async function() {
        await page.goto(url);

        const content = await page.$('#content');
        expect(await content.screenshot()).to.matchImage('admin_page');
    });
});
