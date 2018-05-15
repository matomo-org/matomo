/*!
 * Piwik - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("EmptySite", function () {
    this.timeout(0);

    var generalParams = 'idSite=4&period=day&date=2010-01-03';

    it('should show the tracking code if the website has no recorded data', function (done) {
        var urlToTest = "?" + generalParams + "&module=CoreHome&action=index";

        expect.screenshot('emptySiteDashboard').to.be.captureSelector('.page', function (page) {
            page.load(urlToTest);
        }, done);
    });

    it('should be possible to ignore this screen for one hour', function (done) {
        expect.screenshot('emptySiteDashboard_ignored').to.be.captureSelector('.page', function (page) {
            page.click('.ignoreSitesWithoutData');
            page.wait(1000)
        }, done);
    });
});
