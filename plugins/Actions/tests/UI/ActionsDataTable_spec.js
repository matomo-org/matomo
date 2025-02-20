/*!
 * Matomo - free/libre analytics platform
 *
 * ActionsDataTable screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ActionsDataTable", function () {
    const url = "?module=Widgetize&action=iframe&idSite=1&period=year&date=2012-08-09&moduleToWidgetize=Actions&actionToWidgetize=getPageUrls&isFooterExpandedInDashboard=1";

    it("should load correctly", async function() {
        await page.goto(url);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('initial');
    });

    it("should sort column correctly when column header clicked", async function() {
        await page.click('th#avg_time_on_page');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('column_sorted');
    });

    it("should load subtables correctly when row clicked", async function() {
        secondRow = await page.jQuery('tr.subDataTable:eq(2)');
        await secondRow.click();
        firstRow = await page.jQuery('tr.subDataTable:first');
        await firstRow.click();
        await page.mouse.move(-10, -10);

        await page.waitForNetworkIdle();
        await page.waitForTimeout(250); // rendering

        expect(await page.screenshot({ fullPage: true })).to.matchImage('subtables_loaded');
    });

    it("should show configuration options", async function() {
        await page.click('.dropdownConfigureIcon');
        await page.mouse.move(-10, -10);
        const element = await page.$('.tableConfiguration');
        await page.waitForTimeout(250); // rendering
        expect(await element.screenshot()).to.matchImage('configuration_options');
    });

    it("should flatten table when flatten link clicked", async function() {
        await page.click('.dataTableFlatten');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('flattened');
    });

    it("should exclude low population rows when exclude low population link clicked", async function() {
        await page.click('.dropdownConfigureIcon');
        await page.click('.dataTableExcludeLowPopulation');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('exclude_low_population');
    });

    it("should load normal view when switch to view hierarchical view link is clicked", async function() {
        await page.click('.dropdownConfigureIcon');
        await page.click('.dataTableFlatten');
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('unflattened');
    });

    it("should display pageview percentages when hovering over pageviews column", async function() {
        const elem = await page.jQuery('tr:contains("thankyou") td.column:eq(1)');
        await elem.hover();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('pageview_percentages');
    });

    it("should generate a proper title for the visitor log segmented by the current row", async function() {
        await page.mouse.move(-10, -10);
        const row = 'tr:contains("thankyou") ';
        const first = await page.jQuery(row + 'td.column:first');
        await first.hover();
        const second = await page.jQuery(row + 'td.label .actionSegmentVisitorLog');
        await second.hover();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('segmented_visitor_log_hover');
    });

    it("should open the visitor log segmented by the current row", async function() {
        await page.evaluate(function(){
            $('tr:contains("thankyou") td.label .actionSegmentVisitorLog').click();
        });
        await page.mouse.move(-10, -10);
        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();
        const element = await page.$('.ui-dialog');
        expect(await element.screenshot()).to.matchImage('segmented_visitor_log');
    });

    it("should display unique pageview percentages when hovering over unique pageviews column", async function() {
        await page.click('.ui-widget .ui-dialog-titlebar-close');
        const elem = await page.jQuery('tr:contains("thankyou") td.column:eq(2)');
        await elem.hover();
        await page.waitForTimeout(100);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('unique_pageview_percentages');
    });

    it("should show the search when clicking on the search icon", async function() {
        await page.click('.dataTableAction.searchAction');
        await page.mouse.move(-10, -10);
        await page.waitForTimeout(500);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('search_visible');
    });

    it("should search through table when search input entered and search button clicked and input should be visible", async function() {
        await page.type('.searchAction .dataTableSearchInput', 'i');
        await page.click('.searchAction .icon-search');
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('search');
    });

    it("should close search when clicking on the x icon", async function() {
        await page.click('.searchAction .icon-close');
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('search_closed');
    });

    it("should automatically expand subtables if it contains only one folder", async function() {
        await page.goto(url + '&viewDataTable=table');

        await page.waitForFunction("$('tr .value:contains(\"blog\")').length > 0");
        const first = await page.jQuery('tr .value:contains("blog")');
        await first.click();
        await page.waitForFunction("$('tr .value:contains(\"2012\")').length > 0");
        const second = await page.jQuery('tr .value:contains("2012")');
        await second.click();

        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('auto_expand');
    });
});
