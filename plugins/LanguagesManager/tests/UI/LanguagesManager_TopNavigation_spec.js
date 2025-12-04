/*!
 * Matomo - free/libre analytics platform
 *
 * Language selector top navigation screenshot tests for anonymous users.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('LanguagesManager_TopNavigation', function () {
    this.fixture = "Piwik\\Tests\\Fixtures\\OneVisit";
    this.optionsOverride = {
      'persist-fixture-data': false
    };

    const reportingUrl = '?module=CoreHome&action=index&idSite=1&period=day&date=2012-08-09';

    before(async function () {
        await testEnvironment.callApi('UsersManager.setUserAccess', {
            userLogin: 'anonymous',
            access: 'view',
            idSites: [1],
        });

        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();
    });

    after(async function () {
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    it('should display the language selector in the top navigation for anonymous users', async function () {
        await page.goto(reportingUrl);
        await page.waitForSelector('.nav-wrapper .languageSelection');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);

        const nav = await page.$('.nav-wrapper');
        expect(nav).to.be.ok;

        expect(await nav.screenshot()).to.matchImage('top_navigation_anonymous');
    });
});
