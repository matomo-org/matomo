/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("MobileMessaging", function () {
    this.timeout(0);
    this.fixture = "Piwik\\Plugins\\ScheduledReports\\tests\\Fixtures\\ReportSubscription";

    it("should have three options for report type", async function() {
        await page.goto("?module=ScheduledReports&action=index&idSite=1&period=day&date=yesterday&token=");
        await page.click('#add-report');

        var reportTypeOptions = await page.$$('select[name="report_type"] option');
        expect(reportTypeOptions.length).to.eql(3);

        expect(await page.evaluate(option => option.textContent, reportTypeOptions[0])).to.eql("EMAIL");
        expect(await page.evaluate(option => option.textContent, reportTypeOptions[1])).to.eql("MOBILE");
        expect(await page.evaluate(option => option.textContent, reportTypeOptions[2])).to.eql("BROWSER");
    });

    it("should change report options when mobile is selected", async function() {
        await page.select('select[name="report_type"]', 'string:mobile');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('mobile_report_editor');

        // Get the visible page element for the report format dropdown - sadly there are no IDs to help us
        var allDropdowns = await page.$$('input.select-dropdown');
        var formatDropdown = allDropdowns[6];
        await formatDropdown.click();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('mobile_report_editor_formats');
    });

    it("should change report options when mobile is selected", async function() {
        await page.select('select[name="report_type"]', 'string:browser');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('browser_report_editor');

        // Get the visible page element for the report format dropdown - sadly there are no IDs to help us
        var allDropdowns = await page.$$('input.select-dropdown');
        var formatDropdown = allDropdowns[7];
        await formatDropdown.click();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('browser_report_editor_formats');
    });

    it("should change report options when email is selected", async function() {
        await page.select('select[name="report_type"]', 'string:email');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('email_report_editor');

        // Get the visible page element for the report format dropdown - sadly there are no IDs to help us
        var allDropdowns = await page.$$('input.select-dropdown');
        var formatDropdown = allDropdowns[5];
        await formatDropdown.click();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('email_report_editor_formats');
    });

    it("should create a browser report", async function() {
        await page.select('select[name="report_type"]', 'string:browser');

        // Fill in the form and submit
        await page.type('textarea[name="report_description"]', 'Test browser report');
        await page.click('label[for="browserMultiSites_getAll"]');
        await page.click('#create-report');

        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('list_with_browser_report');
    });
});