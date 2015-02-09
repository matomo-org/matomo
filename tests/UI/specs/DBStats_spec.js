/*!
 * Piwik - free/libre analytics platform
 *
 * Screenshot tests for the DBStats plugin.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("DBStats", function () {
    this.timeout(0);

    var url = "?module=DBStats&action=index&idSite=1&period=day&date=yesterday";

    it("should load correctly", function (done) {
        expect.screenshot('admin_page').to.be.captureSelector('#content', function (page) {
            page.load(url);
        }, done);
    });
});