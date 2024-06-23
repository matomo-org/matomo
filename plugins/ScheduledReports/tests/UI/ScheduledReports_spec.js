/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ScheduledReports", function () {
    this.timeout(0);
    this.fixture = "Piwik\\Plugins\\ScheduledReports\\tests\\Fixtures\\ReportSubscription";

    it("should show an error if no token was provided", async function () {
        await page.goto("?module=ScheduledReports&action=unsubscribe&token=");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('no_token');
    });

    it("should show an error if token is invalid", async function () {
        await page.goto("?module=ScheduledReports&action=unsubscribe&token=invalidtoken");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('invalid_token');
    });

    it("should ask for confirmation before unsubscribing", async function () {
        await page.goto("?module=ScheduledReports&action=unsubscribe&token=mycustomtoken");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('unsubscribe_form');
    });

    it("should show success message on submit", async function () {
        await page.click(".submit");
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('unsubscribe_success');
    });

    it("token should be invalid on second try", async function () {
        await page.goto("?module=ScheduledReports&action=unsubscribe&token=mycustomtoken");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('invalid_token');
    });
});
