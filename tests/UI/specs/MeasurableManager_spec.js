/*!
 * Piwik - free/libre analytics platform
 *
 * Site selector screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("MeasurableManager", function () {
    this.fixture = "Piwik\\Plugins\\SitesManager\\tests\\Fixtures\\ManySites";

    const url = "?module=SitesManager&action=index&idSite=1&period=day&date=yesterday&showaddsite=false";

    before(function () {
        testEnvironment.pluginsToLoad = ['MobileAppMeasurable'];
        testEnvironment.save();
    });

    async function assertScreenshotEquals(screenshotName, selector) {
        const element = await page.jQuery(selector);
        expect(await element.screenshot()).to.matchImage(screenshotName);
    }

    it("should load correctly and should not use SitesManager wording as another type is enabled", async function ()  {
        await page.goto(url);
        await assertScreenshotEquals("loaded", '#content.admin');
    });

    it("should use measurable wording in menu", async function ()  {
        const element = await page.jQuery('#secondNavBar li:contains(Manage):first');
        expect(await element.screenshot()).to.matchImage('measurable_menu_item');
    });

    // '.sitesManagerList,.sitesButtonBar,.sites-manager-header,.ui-dialog.ui-widget,.modal.open'
    it("should show selection of available types when adding a type", async function ()  {
        const element = await page.jQuery('.SitesManager .addSite:first');
        await element.click();
        await page.waitFor('.modal.open');
        await page.waitFor(250); // wait for modal animation
        await assertScreenshotEquals("add_new_dialog", '#content.admin');
    });

    it("should load mobile app specific fields", async function ()  {
        const element = await page.jQuery('.modal.open .btn:contains(Mobile App)');
        await element.click();

        await page.waitFor('input.btn[value=Save]');
        await page.evaluate(function () {
            $('.form-help:contains(UTC time is)').hide();
        });
        await page.waitFor(250);

        await assertScreenshotEquals("add_measurable_view", '#content.admin');
    });

});
