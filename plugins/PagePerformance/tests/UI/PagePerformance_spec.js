/*!
 * Matomo - free/libre analytics platform
 *
 * Page Performance screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PagePerformance", function () {

    this.timeout(0);
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
        await page.click('.dropdownConfigureIcon');
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

        // hover visualization selection
        const icon = await page.jQuery('.activateVisualizationSelection:last');
        await icon.click();
        await page.waitForTimeout(500); // animation

        expect(await page.screenshot({ fullPage: true })).to.matchImage('visualizations');
    });

    it("should load new table with performance metrics visualization", async function () {
        // hover visualization selection
        const icon = await page.jQuery('.dropdown-content .icon-page-performance:last');
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
    // Check page url report
    let subtableLabel = await page.waitForSelector(pageUrlsReportId + ' tr.subDataTable .label');
    await subtableLabel.click();

    let rowWithSubtable = await page.waitForSelector(pageUrlsReportId + ' tr.subDataTable');
    let rowActionsSubtable = await rowWithSubtable.$('td .dataTableRowActions');
    expect(rowActionsSubtable).to.not.equal(null);

    let rowActionLinks = await rowActionsSubtable.$$('.dataTableRowActions a');
    expect(rowActionLinks.length).to.equal(2);

    // hover first row
    let row = await page.waitForSelector(pageUrlsReportId + ' tr.subDataTable.level0 + tr.level1');
    await row.hover();
    await page.waitForTimeout(50);
    rowActionLinks = await row.$$('.dataTableRowActions a');
    expect(rowActionLinks.length).to.equal(4);

    let rowEvolutionIcon = await row.$('.dataTableRowActions .actionRowEvolution');
    expect(rowEvolutionIcon).to.equal(null);
  });
});
