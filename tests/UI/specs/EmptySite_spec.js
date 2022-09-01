/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
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

    it('should have button to send tracking code to developer', async function() {
        var mailtoLink = await page.$eval('#emailTrackingCodeBtn', btn => btn.getAttribute('href'));

        // Check that it's a mailto link with correct subject line
        expect(mailtoLink).to.include('mailto:?subject=Matomo%20Analytics%20Tracking%20Code&body');
        // Check that template rendered and only contains chars that are OK in all mail clients (e.g. no HTML at all)
        expect(mailtoLink).to.match(/^mailto:\?[a-zA-Z0-9&%=.,-_]*$/);
    });

    it('should be possible to ignore this screen for one hour', async function () {
        await page.reload();

        await page.click('.ignoreSitesWithoutData');
        await page.waitForSelector('.widget');
        await page.waitForNetworkIdle();

        const pageElement = await page.$('.page');
        expect(await pageElement.screenshot()).to.matchImage('emptySiteDashboard_ignored');
    });
});
