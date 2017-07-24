/*!
 * Piwik - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Live", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\Live\\tests\\Fixtures\\VisitsWithAllActionsAndDevices";

    it('should show visitor log', function (done) {
        expect.screenshot('visitor_log').to.be.captureSelector('.reporting-page', function (page) {
            page.load("index.php?module=CoreHome&action=index&idSite=1&period=year&date=2010-01-03#?idSite=1&period=year&date=2010-01-03&category=General_Visitors&subcategory=Live_VisitorLog");
        }, done);
    });

    it('should show visitor profile', function (done) {
        expect.screenshot('visitor_profile').to.be.captureSelector('.ui-dialog', function (page) {
            page.click('.card:first-child .visitor-log-visitor-profile-link', 5000);
        }, done);
    });

});