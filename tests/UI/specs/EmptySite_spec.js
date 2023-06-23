/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("EmptySite", function () {

    this.fixture = "Piwik\\Tests\\Fixtures\\DisableSiteContentDetection";

    const generalParams = 'idSite=1&period=day&date=2010-01-03';

    it('should show the tracking code if the website has no recorded data', async function () {
        const urlToTest = "?" + generalParams + "&module=CoreHome&action=index";
        await page.goto(urlToTest);

        const pageElement = await page.$('.page');
        expect(await pageElement.screenshot()).to.matchImage('emptySiteDashboard');
    });

    it('should show the advanced tracking options when clicked', async function () {
        await page.evaluate(() => $('.advance-option a').click());

        const pageElement = await page.$('.page');
        expect(await pageElement.screenshot()).to.matchImage('showAdvancedTrackingOptions');
    });

    it('should hide the advanced tracking options when clicked', async function () {
        await page.evaluate(() => $('.advance-option a').click());

        const pageElement = await page.$('.page');
        expect(await pageElement.screenshot()).to.matchImage('hideAdvancedTrackingOptions');
    });


    it('should show the SPA/PWA tab when clicked', async function () {
        await page.evaluate(function () {
          // since containerID will be random and keeps changing
          var selector = $('#spa .codeblock');
          selector.text(selector.text().replace(/container_(.*).js/g, 'container_test123.js'));
        });
        await page.evaluate(() => $('.no-data-screen-ul-tabs a[href="#spa"]')[0].click());
        await page.waitForTimeout(500);

        const pageElement = await page.$('.page');
        expect(await pageElement.screenshot()).to.matchImage('spa_pwa_page');
      });

    it('should have button to send tracking code to developer', async function() {
        var mailtoLink = await page.$eval('.emailTrackingCode', link => link.getAttribute('href'));

        // Check that it's a mailto link with correct subject line
        expect(mailtoLink).to.include('mailto:?subject=Matomo%20Analytics%20Tracking%20Code&body');
        // Check that template rendered and only contains chars that are OK in all mail clients (e.g. no HTML at all)
        expect(mailtoLink).to.match(/^mailto:\?[a-zA-Z0-9&%=.,-_]*$/);
    });

    it('should be possible to ignore this screen for one hour', async function () {
        await page.reload();

        await page.click('.ignoreSitesWithoutData');
        await page.waitForSelector('#dashboardWidgetsArea');
        await page.waitForNetworkIdle();

        // ensure dashbord widgets are loaded
        const widgetsCount = await page.evaluate(() => $('.widget').length);
        expect(widgetsCount).to.be.greaterThan(1);
    });
});
