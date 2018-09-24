/*!
 * Piwik - free/libre analytics platform
 *
 * Site selector screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
describe("IntranetMeasurable", function () {
    this.timeout(0);
    this.fixture = "Piwik\\Plugins\\SitesManager\\tests\\Fixtures\\ManySites";

    var url = "?module=SitesManager&action=index&idSite=1&period=day&date=yesterday&showaddsite=false";

    before(function () {
        testEnvironment.pluginsToLoad = ['IntranetMeasurable'];

        testEnvironment.save();
    });

    function assertScreenshotEquals(screenshotName, done, test, selector)
    {
        expect.screenshot(screenshotName).to.be.captureSelector(selector, test, done);
    }

    it("should show intranet selection", function (done) {
        assertScreenshotEquals("add_new_dialog", done, function (page) {
            page.load(url);
            page.click('.SitesManager .addSite:first');
        }, '.modal.open');
    });

    it("should load intranet specific fields", function (done) {
        assertScreenshotEquals("intranet_create", done, function (page) {
            page.click('.modal.open .btn:contains(Intranet)');
            page.evaluate(function () {
                $('.form-help:contains(UTC time is)').hide();
            });
            page.wait(250);
        }, '.editingSite');
    });

    it("should load intranet specific fields", function (done) {
        assertScreenshotEquals("intranet_created", done, function (page) {
            page.sendKeys('.editingSite [placeholder="Name"]', 'My intranet');
            page.sendKeys('.editingSite [name="urls"]', 'https://www.example.com');
            page.click('.editingSiteFooter input.btn');
        }, '.site[type=intranet]');
    });

});