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

            // remove the port from URLs if any so UI tests won't fail if the port isn't 80
            // TODO: code redundancy w/ UIIntegrationTest. can be fixed w/ new UI test DI environment type.
            page.evaluate(function () {
                $('pre').each(function () {
                    var html = $(this).html().replace(/localhost\:[0-9]+/g, 'localhost');
                    $(this).html(html);
                });
            });
        }, done);
    });
});
