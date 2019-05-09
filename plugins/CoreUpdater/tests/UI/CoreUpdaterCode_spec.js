/*!
 * Matomo - free/libre analytics platform
 *
 * CoreUpdater screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("CoreUpdaterCode", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\CoreUpdater\\tests\\Fixtures\\FailUpdateHttpsFixture";

    var url = "?module=CoreUpdater&action=newVersionAvailable";

    it("should show a new version is available", async function() {
        await page.goto(url);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('newVersion');
    });

    it("should offer to retry using https when updating over https fails", async function() {
        await page.click('#updateAutomatically');
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('httpsUpdateFail');
    });

    it("should offer to retry over http when updating over https fails", async function() {
        await page.click('#updateUsingHttps');
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('httpsUpdateFail');
    });

    it("should show the update steps when updating over http succeeds", async function() {
        await page.click('#updateUsingHttp');
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('httpUpdateSuccess');
    });
});
