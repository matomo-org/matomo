/*!
 * Matomo - free/libre analytics platform
 *
 * ViewDataTable screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ViewDataTableTest", function () { // TODO: should remove Test suffix from images instead of naming suites ...Test
    // TODO: rename screenshot files, remove numbers

    before(function () {
        const firefoxUserAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 11.2; rv:85.0) Gecko/20100101 Firefox/85.0";
        page.setUserAgent(firefoxUserAgent);
    });

    after(async () => {
        await page.setUserAgent(page.originalUserAgent);
    });

    var url = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
            + "actionToWidgetize=getKeywords&viewDataTable=table&filter_limit=5&isFooterExpandedInDashboard=1";

    it("should load correctly", async function () {
        await page.goto(url);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('0_initial');
    });

    it("should load all columns when all columns clicked", async function () {
        await page.click('.reportHeader__actionsTrigger');
        await page.click('.tableIcon[data-footer-icon-id=tableAllColumns]');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('1_all_columns');
    });

    it("should sort a column in descending order when column clicked initially", async function () {
        await page.click('th#avg_time_on_site');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('2_column_sorted_desc');
    });

    it("should sort a column in ascending order when column clicked second time", async function () {
        await page.click('th#avg_time_on_site');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('3_column_sorted_asc');
    });

    it("should show all available visualizations for this report", async function () {
        await page.click('.reportHeader__actionsTrigger');
        await page.mouse.move(-10, -10);
        await page.waitForTimeout(1000); // wait for animation

        // Note: The selection captured in screenshot is cut of, as the available space in the
        // widget's iframe is too small, so materialize crops the selection into available space.
        const element = await page.$('.reportHeader__actionsMenu');
        expect(await element.screenshot()).to.matchImage('5_visualizations');
    });

    it("should load goals table when goals footer icon clicked", async function () {
        await page.click('.tableIcon[data-footer-icon-id=tableGoals]');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('5_goals');
    });

    it("should load bar graph when bar graph footer icon clicked", async function () {
        await page.click('.reportHeader__actionsTrigger');
        await page.click('.tableIcon[data-footer-icon-id=graphVerticalBar]');
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('6_bar_graph');
    });

    it("should load pie graph when pie graph footer icon clicked", async function () {
        await page.click('.reportHeader__actionsTrigger');
        await page.click('.tableIcon[data-footer-icon-id=graphPie]');
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('7_pie_graph');
    });

    it("should load a tag cloud when tag cloud footer icon clicked", async function () {
        await page.click('.reportHeader__actionsTrigger');
        await page.click('.tableIcon[data-footer-icon-id=cloud]');
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('8_tag_cloud');
    });

    it("should load normal table when normal table footer icon clicked", async function () {
        await page.click('.reportHeader__actionsTrigger');
        await page.click('.tableIcon[data-footer-icon-id=table]');
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10); // mae sure no row is highlighted
        expect(await page.screenshot({ fullPage: true })).to.matchImage('9_normal_table');
    });

    // The header is mounted outside `.dataTable` so a reload does not replace it. Every reload
    // above therefore has to come back without a header of its own, or they accumulate.
    it("should keep a single actions menu after the reloads above", async function () {
        const triggers = await page.evaluate(
            () => document.querySelectorAll('.reportHeader__actionsTrigger').length
        );
        expect(triggers).to.equal(1);
    });

    it("should show the limit selector when the limit selector is clicked", async function () {
        await page.click('.limitSelection .mtm-selector__trigger');
        await page.mouse.move(-10, -10);
        await page.waitForTimeout(200);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('limit_selector_open');
    });

    it("should change the number of rows when new limit selected", async function () {
        await page.click('.limitSelection [data-limit="10"]');
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('10_change_limit');
    });

    it("should flatten the table when the flatten link is clicked", async function () {
        await page.click('.reportHeader__actionsTrigger');
        await page.click('.dataTableFlatten');
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('11_flattened');
    });

    it("should show dimensions separately when option is clicked", async function () {
        await page.click('.reportHeader__actionsTrigger');
        await page.click('.dataTableShowDimensions');
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('dimension_columns');
    });

    it("should search in subtable dimensions even when they are displayed separately", async function () {
        await page.focus('.reportHeader__search .mtm-searchInput__input');
        await page.keyboard.type('Bing');
        // the search is debounced; waitForNetworkIdle waits it out (its idle threshold exceeds the
        // debounce delay) and then for the reload it triggers to settle
        await page.waitForNetworkIdle();
        await page.evaluate(() => document.activeElement.blur());
        await page.waitForTimeout(500);
        await page.mouse.move(-10, -10);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('dimension_search');
    });

    it("search should still work when showing dimensions combined again", async function () {
        await page.click('.reportHeader__actionsTrigger');
        await page.click('.dataTableShowDimensions');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('flatten_search');
    });

    it("search should still work when switching to back to separate dimensions", async function () {
        await page.click('.reportHeader__actionsTrigger');
        await page.click('.dataTableShowDimensions');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
        await page.evaluate(() => document.activeElement.blur());
        expect(await page.screenshot({ fullPage: true })).to.matchImage('dimension_search');
    });

    it("should show aggregate rows when the aggregate rows option is clicked", async function () {
        await page.goto(url.replace(/filter_limit=5/, 'filter_limit=10') + '&flat=1');
        await page.waitForNetworkIdle();
        await page.click('.reportHeader__actionsTrigger');
        await page.click('.dataTableIncludeAggregateRows');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('12_aggregate_shown');
    });

    it("should make the report hierarchical when the flatten link is clicked again", async function () {
        await page.click('.reportHeader__actionsTrigger');
        await page.click('.dataTableFlatten');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('13_make_hierarchical');
    });

    it("should show the visits percent when hovering over a column", async function () {
        await page.hover('td.column:not(.label)');
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('14_visits_percent');
    });

    it("should load subtables correctly when row clicked", async function () {
        (await page.$$('tr.subDataTable'))[0].click();
        await page.waitForNetworkIdle();

        (await page.$$('tr.subDataTable'))[2].click();
        await page.mouse.move(-10, -10); // make sure no krow is highlighted
        await page.waitForNetworkIdle();

        await page.waitForFunction(function () {
            return $('.cellSubDataTable > .dataTable').length === 2;
        });

        expect(await page.screenshot({ fullPage: true })).to.matchImage('subtables_loaded');
    });

    it("should search the table when a search string is entered in the header search", async function () {
        await page.focus('.reportHeader__search .mtm-searchInput__input');
        await page.keyboard.type('term');
        // the search is debounced; waitForNetworkIdle waits it out (its idle threshold exceeds the
        // debounce delay) and then for the reload it triggers to settle
        await page.waitForNetworkIdle();
        await page.evaluate(() => document.activeElement.blur());
        await page.waitForTimeout(500);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('15_search');
    });


    // Export leaves the 3-dots menu when the header line has room for it, so reach it wherever it
    // is rather than always through the menu.
    const openExport = async function (scope) {
        const at = (selector) => (scope ? `${scope} ${selector}` : selector);

        if (await page.$(at('[data-report-action="export"]'))) {
            await page.click(at('[data-report-action="export"] .mtm-selector__trigger'));
        } else {
            await page.click(at('.reportHeader__actionsTrigger'));
        }

        await page.click(at('.activateExportSelection'));
    };

    it("should display the export popover when clicking the export icon", async function () {
        await openExport();
        await page.waitForSelector('#reportExport .btn');

        let dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('export_options');
    });

    it("should display the ENTER_YOUR_TOKEN_AUTH_HERE text in the export url", async function () {
        await page.goto(url.replace(/filter_limit=5/, 'filter_limit=10') + '&flat=1');
        await page.waitForNetworkIdle();
        await openExport();
        await page.waitForSelector('.toggle-export-url');
        await page.click('.toggle-export-url');
        await page.waitForSelector('.exportFullUrl');
        await page.waitForTimeout(250);

        let dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('export_options_2');
    });

    it("should show the totals row when the config link is clicked", async function () {
        await page.goto(url);
        await page.click('.reportHeader__actionsTrigger');
        await page.click('.dataTableShowTotalsRow');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('totals_row');
    });

    it("should display a related report when related report link is clicked", async function () {
        const newReportUrl = url.replace("=Referrers", "=DevicesDetection").replace("=getKeywords", "=getOsFamilies");
        await page.goto(newReportUrl);

        const visibleSpan = await page.jQuery('.datatableRelatedReports li>span:visible');
        await visibleSpan.click();

        await page.mouse.move(-10, -10); // mae sure no row is highlighted
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('related_report_click');
    });

    it("should exclude low population rows when low population clicked", async function () {
        const newUrl = url
            .replace('moduleToWidgetize=Referrers', 'moduleToWidgetize=Actions')
            .replace('actionToWidgetize=getKeywords', 'actionToWidgetize=getPageUrls');
        await page.goto(newUrl);
        await page.click('.reportHeader__actionsTrigger');
        await page.click('.dataTableExcludeLowPopulation');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('exclude_low_population');
    });
});
