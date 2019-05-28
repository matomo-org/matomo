/*!
 * Piwik - free/libre analytics platform
 *
 * Dashboard screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('WelcomeWidget', function () {
    var url = "";

    it('should load correctly', async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();
        expect (await page.screenshotSelector('#widgetCoreHomegetPromoVideo')).to.matchImage('load');
    });

    it('should share a link to matomo.org', async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();

        var shareLinks = await page.$$('#piwik-promo-share a');
        expect(shareLinks.length).to.eql(3);

        var getLinkAddress = function(link) {
            return link.getAttribute('href');
        };
        var facebookLink = await page.evaluate(getLinkAddress, shareLinks[0]);
        expect(facebookLink).to.eql("https://www.facebook.com/sharer.php?u=https%3A%2F%2Fmatomo.org%2Fdocs%2Fvideos%2F");
        var twitterLink = await page.evaluate(getLinkAddress, shareLinks[1]);
        expect(twitterLink).to.eql("https://twitter.com/share?" 
            + "text=Matomo%21%20Free%2Flibre%20web%20analytics.%20Own%20your%20data." 
            + "&url=https%3A%2F%2Fmatomo.org%2Fdocs%2Fvideos%2F"
        );
    });
});
