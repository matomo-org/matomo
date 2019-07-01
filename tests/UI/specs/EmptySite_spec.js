/*!
 * Piwik - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("EmptySite", function () {
    const generalParams = 'idSite=4&period=day&date=2010-01-03';

    it('should show the tracking code if the website has no recorded data', async function () {
        const urlToTest = "?" + generalParams + "&module=CoreHome&action=index";
        await page.goto(urlToTest);

        const pageElement = await page.$('.page');
        expect(await pageElement.screenshot()).to.matchImage('emptySiteDashboard');
    });

    it('should be possible to ignore this screen for one hour', async function () {
        await page.click('.ignoreSitesWithoutData');
        await page.waitFor('.widget');
        await page.waitForNetworkIdle();

        const pageElement = await page.$('.page');
        expect(await pageElement.screenshot()).to.matchImage('emptySiteDashboard_ignored');
    });
});
