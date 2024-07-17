/*!
 * Matomo - free/libre analytics platform
 *
 * Visitor Map screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("VisitorMap", function () {
    this.timeout(5*60*1000); // timeout of 5 minutes per test

    var url = "?module=Widgetize&action=iframe&moduleToWidgetize=UserCountryMap&idSite=1&period=year&date=2012-08-09&"
        + "actionToWidgetize=visitorMap&viewDataTable=table&filter_limit=5&isFooterExpandedInDashboard=1",
        urlWithCities = "?module=Widgetize&action=iframe&moduleToWidgetize=UserCountryMap&idSite=3&period=day&date=yesterday&"
            + "actionToWidgetize=visitorMap&viewDataTable=table&filter_limit=5&isFooterExpandedInDashboard=1";

    it("should display the bounce rate metric correctly", async function() {
        await page.goto(url);
        await page.evaluate(function () {
            $('.userCountryMapSelectMetrics').val('bounce_rate').trigger('change');
        });
        await page.mouse.move(900, 140);
        await page.waitForTimeout(100); // wait for tooltip

        expect(await page.screenshot({ fullPage: true })).to.matchImage('bounce_rate');
    });

    it("should display the average time on site metric correctly", async function() {
        await page.mouse.move(0, 0);
        await page.evaluate(function () {
            $('.userCountryMapSelectMetrics').val('avg_time_on_site').trigger('change');
        });
        await page.mouse.move(900, 140);
        await page.waitForTimeout(100); // wait for tooltip

        expect(await page.screenshot({ fullPage: true })).to.matchImage('avg_time_on_site');
    });

    it("should display the regions layer correctly", async function() {
        await page.goto(urlWithCities);
        await page.waitForNetworkIdle();
        await page.waitForFunction('window.visitorMap && window.visitorMap.map && window.visitorMap.map.getLayer(\'countries\') !== null');
        await page.webpage.evaluate(function () {
            // zoom into USA
            var path = window.visitorMap.map.getLayer('countries').getPaths({iso: "USA"})[0].svgPath[0];
            $(path).click();
        });
        await page.waitForTimeout(1000);
        await page.webpage.evaluate(function () {
            // go to regions view
            var path = window.visitorMap.map.getLayer('countries').getPaths({iso: "USA"})[0].svgPath[0];
            $(path).click();
        });
        await page.waitForTimeout(1000);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('regions');
    });

    it("should display the cities layer correctly", async function() {
        await page.click('.UserCountryMap-btn-city');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(1000); // wait for map

        expect(await page.screenshot({ fullPage: true })).to.matchImage('cities');
    });
});
