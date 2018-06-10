/*!
 * Piwik - free/libre analytics platform
 *
 * Visitor Map screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("VisitorMap", function () {
    this.timeout(0);

    var url = "?module=Widgetize&action=iframe&moduleToWidgetize=UserCountryMap&idSite=1&period=year&date=2012-08-09&"
        + "actionToWidgetize=visitorMap&viewDataTable=table&filter_limit=5&isFooterExpandedInDashboard=1",
        urlWithCities = "?module=Widgetize&action=iframe&moduleToWidgetize=UserCountryMap&idSite=3&period=week&date=yesterday&"
            + "actionToWidgetize=visitorMap&viewDataTable=table&filter_limit=5&isFooterExpandedInDashboard=1";

    it("should display the bounce rate metric correctly", async function() {
        this.retries(3);

        expect.screenshot('bounce_rate').to.be.similar(0.002).to.capture(function (page) {
            page.load(url);
            page.evaluate(function () {
                $('.userCountryMapSelectMetrics').val('bounce_rate').trigger('change');
            });
            page.mouseMove('.UserCountryMap_map.kartograph');
        }, done);
    });

    it("should display the average time on site metric correctly", async function() {
        expect.screenshot('avg_time_on_site').to.be.similar(0.002).to.capture(function (page) {
            page.evaluate(function () {
                $('.userCountryMapSelectMetrics').val('avg_time_on_site').trigger('change');
            });
            page.mouseMove('.UserCountryMap_map.kartograph');
        }, done);
    });

    it("should display the regions layer correctly", async function() {
        expect.screenshot('regions').to.be.similar(0.002).to.capture(function (page) {
            page.load(urlWithCities);
            page.evaluate(function () {
                // zoom into USA
                var path = window.visitorMap.map.getLayer('countries').getPaths({iso: "USA"})[0].svgPath[0];
                $(path).click();
            });
            page.evaluate(function () {
                // go to regions view
                var path = window.visitorMap.map.getLayer('countries').getPaths({iso: "USA"})[0].svgPath[0];
                $(path).click();
            });
        }, done);
    });

    it("should display the cities layer correctly", async function() {
        expect.screenshot('cities').to.be.similar(0.002).to.capture(function (page) {
            page.click('.UserCountryMap-btn-city');
        }, done);
    });
});
