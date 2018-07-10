/*!
 * Matomo - free/libre analytics platform
 *
 * Insights screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Insights", function () {
    this.timeout(0);

    var url = "?module=Widgetize&action=iframe&idSite=1&period=year&date=2012-08-09&moduleToWidgetize=Actions&actionToWidgetize=getPageUrls&isFooterExpandedInDashboard=1&viewDataTable=insightsVisualization";

    it("should load correctly", async function() {
        await page.goto(url);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('initial');
    });

});
