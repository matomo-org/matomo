/*!
 * Piwik - free/libre analytics platform
 *
 * Site selector screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("MeasurableManager", function () {
    this.timeout(0);
    this.fixture = "Piwik\\Plugins\\SitesManager\\tests\\Fixtures\\ManySites";

    var url = "?module=SitesManager&action=index&idSite=1&period=day&date=yesterday&showaddsite=false";

    before(function () {
        testEnvironment.pluginsToLoad = ['MobileAppMeasurable'];

        testEnvironment.save();
    });

    function assertScreenshotEquals(screenshotName, done, test)
    {
        expect.screenshot(screenshotName).to.be.captureSelector('.sitesManagerList,.sitesButtonBar,.sites-manager-header,.ui-dialog.ui-widget,.modal.open', test, done);
    }

    it("should load correctly and should not use SitesManager wording as another type is enabled", async function ()  {
        assertScreenshotEquals("loaded", done, function (page) {
            page.goto(url);
        });
    });

    it("should use measurable wording in menu", async function ()  {
        var selector = '#secondNavBar li:contains(Manage):first';
        expect.screenshot('measurable_menu_item').to.be.captureSelector(selector, function (page) {

        }, done);
    });

    it("should show selection of available types when adding a type", async function ()  {
        assertScreenshotEquals("add_new_dialog", done, function (page) {
            page.click('.SitesManager .addSite:first');
        });
    });

    it("should load mobile app specific fields", async function ()  {
        assertScreenshotEquals("add_measurable_view", done, function (page) {
            page.click('.modal.open .btn:contains(Mobile App)');
            page.evaluate(function () {
                $('.form-help:contains(UTC time is)').hide();
            });
            page.wait(250);
        });
    });

});
