/*!
 * Matomo - free/libre analytics platform
 *
 * Session timeout refresh UI test.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('SessionTimeoutRefresh', function () {
    this.fixture = 'Piwik\\Tests\\Fixtures\\OneVisitorTwoVisits';

    const reportUrl = '?module=CoreHome&action=index&idSite=1&period=day&date=yesterday'
        + '#?idSite=1&period=day&date=yesterday&category=General_Visitors&subcategory=UserId_UserReportTitle';

    before(async function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.overrideConfig('General', 'login_session_not_remembered_idle_timeout', 2);
        testEnvironment.save();
    });

    after(async function () {
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    it('should refresh to login when an ajax call happens after session timeout', async function () {
        await page.clearCookies();
        await page.goto(reportUrl);
        await page.waitForNetworkIdle();

        await page.type('#login_form_login', superUserLogin);
        await page.type('#login_form_password', superUserPassword);
        await page.click('#login_form_submit');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.reporting-page');

        await page.waitForTimeout(3500);

        await page.click('div.reportingMenu ul.navbar li[data-category-id="General_Actions"]');
        await page.waitForTimeout(100);
        await page.click('div.reportingMenu ul.navbar li[data-category-id="General_Actions"] ul li:nth-of-type(1)');

        const loginPage = await page.waitForSelector('#loginPage', { visible: true });
        expect(loginPage).to.be.ok;

        const expectedText = 'Error: Your session has expired due to inactivity. Please log in to continue.';
        await page.waitForSelector('div.system.notification-error .notification-body');
        const notificationText = await page.$eval(
            'div.system.notification-error .notification-body',
            (el) => el.textContent.trim()
        );
        expect(notificationText).to.equal(expectedText);
    });
});
