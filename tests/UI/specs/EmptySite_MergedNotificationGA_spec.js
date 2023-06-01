/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("EmptySite_MergedNotificationGA", function () {

    this.fixture = "Piwik\\Tests\\Fixtures\\EmptySiteWithSiteContentDetectionMergeNotificationOnlyGA";

    const generalParams = 'idSite=1&period=day&date=2010-01-03';

    it('should show the tracking code if the website has no recorded data with GA notifications merged into one', async function () {
        const urlToTest = "?" + generalParams + "&module=CoreHome&action=index";
        await page.goto(urlToTest);

        const pageElement = await page.$('.page');
        expect(await pageElement.screenshot()).to.matchImage('emptySiteDashboard');
    });


});
