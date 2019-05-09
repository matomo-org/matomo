/*!
 * Matomo - free/libre analytics platform
 *
 * ImageGraph plugin screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ImageGraph", function () {
    this.timeout(0);

    function getImageGraphUrl(apiModule, apiAction, graphType, period, date) {
        return "index.php?module=API&method=ImageGraph.get&idSite=1&width=500&height=250&apiModule=" + apiModule + "&apiAction=" + apiAction
             + "&graphType=" + graphType + "&period=" + period + "&date=" + date;
    }

    it("should render evolution graphs correctly", async function() {
        await page.goto(getImageGraphUrl('VisitsSummary', 'get', 'evolution', 'month', '2011-06-01,2012-06-01'));

        expect(await page.screenshot({ fullPage: true })).to.matchImage('evolution_graph');
    });

    it("should render horizontal bar graphs correctly", async function() {
        await page.goto(getImageGraphUrl('UserSettings', 'getBrowser', 'horizontalBar', 'year', '2012-01-01'));

        expect(await page.screenshot({ fullPage: true })).to.matchImage('horizontal_bar');
    });

    it("should render vertical bar graphs correctly", async function() {
        await page.goto(getImageGraphUrl('UserCountry', 'getCountry', 'verticalBar', 'year', '2012-01-01'));

        expect(await page.screenshot({ fullPage: true })).to.matchImage('vertical_bar');
    });

    it("should render pie graphs correctly", async function() {
        await page.goto(getImageGraphUrl('DevicesDetection', 'getOsVersions', 'pie', 'year', '2012-01-01'));

        expect(await page.screenshot({ fullPage: true })).to.matchImage('pie');
    });
});