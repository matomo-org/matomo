/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("EmptySite_CloudflareOverGTM", function () {

    this.fixture = "Piwik\\Tests\\Fixtures\\EmptySiteWithSiteContentDetectionGTM";

    const generalParams = 'idSite=1&period=day&date=2010-01-03';

    it('should select the tab provided by hash param', async function () {
        const urlToTest = "?" + generalParams + "&module=CoreHome&action=index#?" + generalParams + "&activeTab=cloudflare";
        await page.goto(urlToTest);

        const pageElement = await page.$('.page');
        expect(await pageElement.screenshot()).to.matchImage('emptySiteDashboard');
    });

});
