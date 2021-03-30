/*!
 * Matomo - free/libre analytics platform
 *
 * Only Raw Data Notification screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("OnlyRawDataNotification", function () {
    this.fixture = "Piwik\\Tests\\Fixtures\\OneVisit";

    const generalParams = 'idSite=1&period=week&date=today';
    const pageUrl = '?module=CoreHome&action=index&' + generalParams;

    before(function () {
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.configOverride.General = {
            browser_archiving_disabled_enforce: '1',
            enable_browser_archiving_triggering: '0',
        };
        testEnvironment.optionsOverride = {
            enableBrowserTriggerArchiving: '0',
        };
        testEnvironment.save();
    });

    it("should show notification when only raw data exists", async function () {
        await page.goto(pageUrl);
        pageWrap = await page.$('#notificationContainer');
        expect(await pageWrap.screenshot()).to.matchImage('show_notification_when_only_raw_data_exists');
    });
});
