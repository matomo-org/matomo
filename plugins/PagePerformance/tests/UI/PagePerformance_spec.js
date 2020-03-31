/*!
 * Matomo - free/libre analytics platform
 *
 * Page Performance screenshot tests.
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PagePerformance", function () {

    this.timeout(0);
    this.fixture = "Piwik\\Plugins\\PagePerformance\\tests\\Fixtures\\VisitsWithPagePerformanceMetrics";

    const generalParams = 'idSite=1&period=day&date=2010-03-12',
        urlBase = 'module=CoreHome&action=index&' + generalParams;

    it("should load page performance overview", async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Visitors&subcategory=General_Overview");
        pageWrap = await page.$('.pageWrap');

        await page.hover('#widgetPagePerformancegetEvolutionGraphforceView1viewDataTablegraphStackedBarEvolution .piwik-graph');
        await page.waitFor(50);

        expect(await pageWrap.screenshot()).to.matchImage('load');
    });

    it("should show new row action in pages reports", async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=General_Pages");

        // hover first row
        const row = await page.waitForSelector('.dataTable tbody tr:first-child');
        await row.hover();

        pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('rowactions');
    });

    it("should load page performance overlay", async function () {

        // click page performance icon
        const icon = await page.waitForSelector('.dataTable tbody tr:first-child a.actionPagePerformance');
        await icon.click();

        await page.waitForNetworkIdle();

        pageWrap = await page.waitForSelector('.ui-dialog');

        await page.hover('.piwik-graph');
        await page.waitFor(50);

        expect(await pageWrap.screenshot()).to.matchImage('pageurl_overlay');
    });

    it("should show new table with performance metrics visualization in selection", async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=General_Pages");

        // hover visualization selection
        const icon = await page.jQuery('.activateVisualizationSelection');
        await icon.click();

        pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('visualizations');
    });

    it("should load new table with performance metrics visualization", async function () {

        // hover visualization selection
        const icon = await page.jQuery('.dropdown-content .icon-page-performance');
        await icon.click();

        await page.waitForNetworkIdle();

        pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('performance_visualization');
    });

    it("should show rowaction for subtable rows", async function () {

        const subtablerow = await page.jQuery('tr.subDataTable:eq(1)');
        await subtablerow.click();

        await page.waitForNetworkIdle();
        await page.waitFor(200);

        // hover first row
        const row = await page.jQuery('tr.subDataTable:eq(1) + tr');
        await row.hover();

        pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('rowactions_subtable');
    });

    it("performance overlay should work on page titles report", async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=Actions_SubmenuPageTitles");

        // hover first row
        const row = await page.waitForSelector('.dataTable tbody tr:first-child');
        await row.hover();

        // click page performance icon
        const icon = await page.waitForSelector('.dataTable tbody tr:first-child a.actionPagePerformance');
        await icon.click();

        await page.waitForNetworkIdle();

        pageWrap = await page.waitForSelector('.ui-dialog');

        await page.hover('.piwik-graph');
        await page.waitFor(50);

        expect(await pageWrap.screenshot()).to.matchImage('pagetitle_overlay');
    });


});