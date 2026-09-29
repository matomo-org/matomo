/*!
 * Matomo - free/libre analytics platform
 *
 * Page Performance screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PagePerformance", function () {
    this.fixture = "Piwik\\Plugins\\PagePerformance\\tests\\Fixtures\\VisitsWithPagePerformanceMetrics";

    const generalParams = 'idSite=1&period=day&date=2010-03-12',
        urlBase = 'module=CoreHome&action=index&' + generalParams;
    const pageUrlsReportId = '#widgetActionsgetPageUrlsforceView1viewDataTabletablePerformanceColumnsperformance1';
    const pageTitleReportId = '#widgetActionsgetPageTitlesforceView1viewDataTabletablePerformanceColumnsperformance1';

    async function ensureTooltipIsVisibleInScreenshot() {
        await page.evaluate(() => {
            var html = $('.ui-tooltip').attr('id', 'test-tooltip-permanent')[0].outerHTML;
            $('.ui-dialog').append(html);
        });
    }

    it("should load page performance overview", async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=PagePerformance_Performance");
        await page.waitForSelector('.piwik-graph');
        await page.waitForNetworkIdle();

        pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('load');
    });

    it("should show new row action in pages reports", async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=General_Pages");

        // hover first row
        const row = await page.waitForSelector('.dataTable tbody tr:first-child');
        await row.hover();
        await page.waitForTimeout(50);

        pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('rowactions');
    });

    it("should show rowaction for subtable rows", async function () {
        const subtablerow = await page.jQuery('tr.subDataTable:eq(1) .label');
        await subtablerow.click();

        await page.waitForNetworkIdle();
        await page.waitForTimeout(200);

        // hover first row
        const row = await page.jQuery('tr.subDataTable:eq(1) + tr');
        await row.hover();

        pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('rowactions_subtable');
    });

    it("should load page performance overlay", async function () {
        // click page performance icon
        const row = await page.waitForSelector('.dataTable tbody tr:first-child');
        await row.hover();

        const icon = await page.waitForSelector('.dataTable tbody tr:first-child a.actionPagePerformance');
        await icon.click();

        await page.waitForNetworkIdle();

        const pageWrap = await page.waitForSelector('.ui-dialog');

        await page.hover('.piwik-graph');
        await page.waitForSelector('.ui-tooltip', { visible: true });

        await ensureTooltipIsVisibleInScreenshot();
        await page.waitForTimeout(100);

        expect(await pageWrap.screenshot()).to.matchImage('pageurl_overlay');
    });

    it("should work with flattened report", async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=General_Pages");

        // make report flattened
        await page.click('.reportHeader__actionsTrigger');
        await page.click('.dataTableFlatten');
        await page.waitForNetworkIdle();

        // click page performance icon
        const row = await page.waitForSelector('.dataTable tbody tr:first-child');
        await row.hover();

        const icon = await page.waitForSelector('.dataTable tbody tr:first-child a.actionPagePerformance');
        await icon.click();

        await page.waitForNetworkIdle();

        const pageWrap = await page.waitForSelector('.ui-dialog');

        await page.hover('.piwik-graph');
        await page.waitForSelector('.ui-tooltip', { visible: true });

        await ensureTooltipIsVisibleInScreenshot();
        await page.waitForTimeout(100);

        expect(await pageWrap.screenshot()).to.matchImage('pageurl_overlay_flattened');
    });

    it("should show new table with performance metrics visualization in selection", async function () {
        await page.goto("?module=Widgetize&action=iframe&disableLink=0&widget=1&moduleToWidgetize=Actions&actionToWidgetize=getPageUrls&" + generalParams);

        // open the report actions menu
        const icon = await page.jQuery('.reportHeader__actionsTrigger:last');
        await icon.click();
        await page.waitForTimeout(500); // animation

        expect(await page.screenshot({ fullPage: true })).to.matchImage('visualizations');
    });

    it("should load new table with performance metrics visualization", async function () {
        // the menu opened by the test above lists the visualisations; `.dropdown-content` was the
        // footer bar's own wrapper and is not rendered any more
        const icon = await page.jQuery('.reportHeader__actionsMenu .tableIcon[data-footer-icon-id=tablePerformanceColumns]:last');
        await icon.click();
        await page.mouse.move(-10, -10);

        await page.waitForNetworkIdle();

        pageWrap = await page.$('.widget');
        expect(await pageWrap.screenshot()).to.matchImage('performance_visualization');
    });

    it("performance overlay should work on page titles report", async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&segment=actions>=1&category=General_Actions&subcategory=Actions_SubmenuPageTitles");

        // hover first row
        const row = await page.waitForSelector('.dataTable tbody tr:first-child');
        await row.hover();

        // click page performance icon
        const icon = await page.waitForSelector('.dataTable tbody tr:first-child a.actionPagePerformance');
        await icon.click();

        await page.waitForNetworkIdle();

        pageWrap = await page.waitForSelector('.ui-dialog');

        await page.hover('.piwik-graph');
        await page.waitForSelector('.ui-tooltip', { visible: true });

        await ensureTooltipIsVisibleInScreenshot();
        await page.waitForTimeout(250);

        expect(await pageWrap.screenshot()).to.matchImage('pagetitle_overlay');
    });

  it("should not show row evolution icon in page urls and page titles reports when in Behaviour > Performance page", async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=PagePerformance_Performance");

    // Check page report
    let row = await page.waitForSelector(pageUrlsReportId + ' .dataTable tbody tr:first-child');
    await row.hover();
    await page.waitForTimeout(50);
    pageWrap = await page.$(pageUrlsReportId);

    let rowActions = await row.$('.dataTableRowActions');
    expect(rowActions).to.not.equal(null);

    let rowActionLinks = await row.$$('.dataTableRowActions a');
    expect(rowActionLinks.length).to.equal(4);

    let icon = await pageWrap.$('.actionRowEvolution');
    expect(icon).to.equal(null);

    // Check Page Titles report
    row = await page.waitForSelector(pageTitleReportId + ' .dataTable tbody tr:first-child');
    await row.hover();
    await page.waitForTimeout(50);
    pageWrap = await page.$(pageTitleReportId);

    rowActions = await row.$('.dataTableRowActions');
    expect(rowActions).to.not.equal(null);

    rowActionLinks = await row.$$('.dataTableRowActions a');
    expect(rowActionLinks.length).to.equal(3);

    icon = await row.$('.actionRowEvolution');
    expect(icon).to.equal(null);
  });

  it("should not show row evolution icon for subtable rows in Behaviour > Performance", async function () {
    // Check the subtable parent row shows its row actions on hover, before expanding it.
    let rowWithSubtable = await page.waitForSelector(pageUrlsReportId + ' tr.subDataTable');
    // Scroll the row to the centre so it is not hidden behind the sticky table header (otherwise the
    // hover lands on the header), move the mouse away so a fresh mouseenter fires (row actions are
    // added on mouseenter), then wait for the actions in the live DOM via a page-level selector --
    // an element-scoped waitForSelector does not reliably observe them being inserted under Chrome 149.
    await rowWithSubtable.evaluate(el => el.scrollIntoView({ block: 'center' }));
    await page.mouse.move(0, 0);
    await rowWithSubtable.hover();
    await page.waitForSelector(pageUrlsReportId + ' tr.subDataTable .dataTableRowActions');
    let rowActionLinks = await rowWithSubtable.$$('.dataTableRowActions a');
    expect(rowActionLinks.length).to.equal(2);

    // Expand the subtable. A synthesised mouse click can miss and trigger a column sort instead of
    // expanding under the modern headless Chrome, so use a JS click which reliably triggers it.
    let subtableLabel = await rowWithSubtable.$('.label');
    await subtableLabel.evaluate(el => el.click());
    await page.waitForNetworkIdle();

    // hover first sub row
    let row = await page.waitForSelector(pageUrlsReportId + ' tr.subDataTable.level0 + tr.level1');
    await row.evaluate(el => el.scrollIntoView({ block: 'center' }));
    await page.mouse.move(0, 0);
    await row.hover();
    await page.waitForSelector(pageUrlsReportId + ' tr.subDataTable.level0 + tr.level1 .dataTableRowActions a');
    rowActionLinks = await row.$$('.dataTableRowActions a');
    expect(rowActionLinks.length).to.equal(4);

    let rowEvolutionIcon = await row.$('.dataTableRowActions .actionRowEvolution');
    expect(rowEvolutionIcon).to.equal(null);
  });
});
