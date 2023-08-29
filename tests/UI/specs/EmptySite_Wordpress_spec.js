/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("EmptySite_Wordpress", function () {

    this.fixture = "Piwik\\Tests\\Fixtures\\EmptySiteWithSiteContentDetectionWordpress";

    const generalParams = 'idSite=1&period=day&date=2010-01-03';

    it('should show the tracking code if the website has no recorded data and wordpress guide', async function () {
        const urlToTest = "?" + generalParams + "&module=CoreHome&action=index";
        await page.goto(urlToTest);
        await page.waitForTimeout(200); // svg takes some time to render

        const pageElement = await page.$('.page');
        await page.waitForNetworkIdle();
        expect(await pageElement.screenshot()).to.matchImage('emptySiteDashboard');
    });

});
