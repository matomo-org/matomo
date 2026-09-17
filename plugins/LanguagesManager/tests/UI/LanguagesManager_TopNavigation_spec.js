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

    // The selector is the one top menu entry registered as raw HTML, so it never gets the icon
    // spacer the other rows carry and has to be lined up on its own.
    it('should line the language selector up with the other entries of the mobile drawer', async function () {
        await page.webpage.setViewport({ width: 768, height: 512 });
        await page.goto(reportingUrl);
        await page.waitForSelector('#mobile-top-menu .languageSelection');
        await page.waitForNetworkIdle();
        await page.evaluate(function () {
            $('.activateTopMenu>span').click();
        });
        await page.waitForTimeout(500);

        expect(await page.screenshotSelector('#mobile-top-menu', false)).to.matchImage('mobile_drawer_anonymous');
    });
});
