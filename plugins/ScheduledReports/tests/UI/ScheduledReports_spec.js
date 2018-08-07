/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ScheduledReports", function () {
    this.timeout(0);
    this.fixture = "Piwik\\Plugins\\ScheduledReports\\tests\\Fixtures\\ReportSubscription";

    it("should show an error if no token was provided", function (done) {
        expect.screenshot("no_token").to.be.capture(function (page) {
            page.load("?module=ScheduledReports&action=unsubscribe&token=");
        }, done);
    });

    it("should show an error if token is invalid", function (done) {
        expect.screenshot("invalid_token").to.be.capture(function (page) {
            page.load("?module=ScheduledReports&action=unsubscribe&token=invalidtoken");
        }, done);
    });

    it("should ask for confirmation before unsubscribing", function (done) {
        expect.screenshot("unsubscribe_form").to.be.capture(function (page) {
            page.load("?module=ScheduledReports&action=unsubscribe&token=mycustomtoken");
        }, done);
    });

    it("should show success message on submit", function (done) {
        expect.screenshot("unsubscribe_success").to.be.capture(function (page) {
            page.click(".submit", 1000);
        }, done);
    });

    it("token should be invalid on second try", function (done) {
        expect.screenshot("invalid_token").to.be.capture(function (page) {
            page.load("?module=ScheduledReports&action=unsubscribe&token=mycustomtoken");
        }, done);
    });
});