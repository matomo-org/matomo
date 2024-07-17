/*!
 * Matomo - free/libre analytics platform
 *
 * Site selector screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
describe("IntranetMeasurable", function () {
    this.timeout(5*60*1000); // timeout of 5 minutes per test
    this.fixture = "Piwik\\Plugins\\SitesManager\\tests\\Fixtures\\ManySites";

    var url = "?module=SitesManager&action=index&idSite=1&period=day&date=yesterday&showaddsite=false";

    before(function () {
        testEnvironment.pluginsToLoad = ['IntranetMeasurable'];

        testEnvironment.save();
    });

    after(async function () {
        // ensure the newly created site is removed afterwards, so other tests reusing the fixture won't change results
        await testEnvironment.callApi('SitesManager.deleteSite', { idSite: 64 });
    });

    it("should show intranet selection", async function () {
        await page.goto(url);
        await (await page.jQuery('.SitesManager .addSite:first')).click();
        await page.waitForTimeout(500);

        const elem = await page.$('.modal.open');
        expect(await elem.screenshot()).to.matchImage('add_new_dialog');
    });

    it("should load intranet specific fields", async function () {
        await (await page.jQuery('.modal.open .btn:contains(Intranet)')).click();
        await page.waitForNetworkIdle();
        await page.waitForTimeout(250);

        await page.evaluate(function () {
            $('.form-help:contains(UTC time is)').hide();
        });

        pageWrap = await page.$('.editingSite');
        expect(await pageWrap.screenshot()).to.matchImage('intranet_create');
    });

    it("should load intranet specific fields", async function () {
        await page.type('.editingSite [placeholder="Name"]', 'My intranet');
        await page.type('.editingSite [name="urls"]', 'https://www.example.com');
        await page.waitForTimeout(250);
        await page.click('.editingSiteFooter input.btn');
        await page.waitForNetworkIdle();

        pageWrap = await page.$('.site[type=intranet]');
        expect(await pageWrap.screenshot()).to.matchImage('intranet_created');
    });

});
