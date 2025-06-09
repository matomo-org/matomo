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


});
