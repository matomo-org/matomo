/*!
 * Matomo - free/libre analytics platform
 *
 * Visitor Map screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("VisitorMap", function () {
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
        // The map is redrawn when the window resizes (the harness triggers that after loading), so its layer
        // can briefly be missing: click USA as soon as it's there, first to zoom in, then for the regions.
        const clickUsa = () => page.waitForFunction(function () {
            var layer = window.visitorMap && window.visitorMap.map && window.visitorMap.map.getLayer('countries');
            var paths = layer ? layer.getPaths({iso: "USA"}) : [];
            if (!paths.length) {
                return false;
            }
            $(paths[0].svgPath[0]).click();
            return true;
        });
        await clickUsa();
        await page.waitForTimeout(1000);
        await clickUsa();
        await page.waitForTimeout(1000);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('regions');
    });

    it("should display the cities layer correctly", async function() {
        await page.click('.UserCountryMap-btn-city');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(1000); // wait for map

        expect(await page.screenshot({ fullPage: true })).to.matchImage('cities');
    });

    it("should display the map correctly in dark mode", async function() {
        await page.goto(url);
        await page.waitForNetworkIdle();
        // Enable dark mode the same way piwik.setThemeMode() does: the theme CSS
        // variables key off [data-theme-mode="dark"] on the root element.
        await page.evaluate(function () {
            document.documentElement.setAttribute('data-theme-mode', 'dark');
        });
        await page.mouse.move(900, 140);
        await page.waitForTimeout(100); // wait for tooltip + legend to appear

        expect(await page.screenshot({ fullPage: true })).to.matchImage('dark_mode');
    });

    it("should render with a positive svg height inside the AddWidget preview", async function () {
        // Regression: the resize clamp previously fired on any `.widget` ancestor,
        // including the AddWidget preview, which made maxHeight negative and
        // collapsed the SVG. The clamp now only fires for the Widgetize iframe.
        const dashboardParams = 'idSite=1&period=year&date=2012-08-09';
        const modalSelector = '.modal.open.add-widget-modal';

        await page.goto('?module=CoreHome&action=index&' + dashboardParams
            + '#?' + dashboardParams + '&category=Dashboard_Dashboard&subcategory=1');
        await page.waitForNetworkIdle();

        await page.click('.dashboard-manager .title');
        await page.waitForTimeout(50);
        await page.click('button[data-action="addWidget"]');
        await page.waitForSelector(modalSelector);
        await page.waitForSelector(modalSelector + ' .widgetpreview-categorylist>li');

        await (await page.jQuery(modalSelector + ' .widgetpreview-categorylist>li:contains(Visitors - Locations):first')).hover();
        await (await page.jQuery(modalSelector + ' .widgetpreview-widgetlist>li:contains(Visitor Map):first')).hover();
        await page.waitForNetworkIdle();

        const svg = await page.waitForSelector(modalSelector + ' .widgetpreview-preview .UserCountryMap_map svg');
        const box = await svg.boundingBox();

        expect(box.height).to.be.above(0);
    });
});
