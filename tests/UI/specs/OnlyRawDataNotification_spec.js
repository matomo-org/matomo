/*!
 * Matomo - free/libre analytics platform
 *
 * Only Raw Data Notification screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("OnlyRawDataNotification", function () {
    this.fixture = "Piwik\\Tests\\Fixtures\\OneVisit";

    const generalParams = 'idSite=1&period=range&date=2021-01-01,today';
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
        await page.waitForSelector('.widget');
        const notificationContainer = await page.$('#notificationContainer');
        expect(await notificationContainer.screenshot()).to.matchImage('show_notification_when_only_raw_data_exists');
    });

    it("should show notification when only raw data exists and visits log is disabled", async function () {
        testEnvironment.overrideConfig('Live', 'disable_visitor_log', 1);
        testEnvironment.save();
        await page.goto('about:blank');
        await page.goto(pageUrl);
        await page.waitForSelector('.widget');
        const notificationContainer = await page.$('#notificationContainer');
        expect(await notificationContainer.screenshot()).to.matchImage('show_notification_when_only_raw_data_exists_no_visits_log');
    });
});
