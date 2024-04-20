/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Goals", function () {
    this.fixture = 'Piwik\\Tests\\Fixtures\\SomePageGoalVisitsWithConversions';

    it('should show the goals overview', async function() {
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2009-01-01#?idSite=1&period=year&date=2009-01-01&category=Goals_Goals&subcategory=General_Overview");

        await page.waitForNetworkIdle();
        await page.waitForSelector('.dataTableVizGoals');

        var report = await page.$('.reporting-page');
        expect(await report.screenshot()).to.matchImage('overview');
    });

    it('should show goals by page', async function() {

        await page.evaluate(function(){
            $('div.dimensionCategory:nth-child(2) > ul:nth-child(1) > li:nth-child(1)').click();
        });
        await page.waitForTimeout(100);
        await page.waitForSelector('.dimensionReport .dataTableVizGoals');
        await page.waitForNetworkIdle();

        await page.waitForFunction("$('tr .value:contains(\"page_A\")').length > 0");
        const first = await page.jQuery('tr .value:contains("page_A")');
        await first.click();
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);

        var report = await page.$('.dimensionReport');
        expect(await report.screenshot()).to.matchImage('goals_by_pages');
    });

    it('should load row evolution with goal metrics for subtable row', async function() {
      const row = await page.jQuery('.dataTable tr.level1:eq(1)');
      await row.hover();

      const icon = await page.jQuery('.dataTable tr.level1:eq(1) a.actionRowEvolution');
      await icon.click();

      await page.waitForSelector('.ui-dialog');
      await page.waitForNetworkIdle();

      const dialog = await page.$('.ui-dialog');
      expect(await dialog.screenshot()).to.matchImage('goals_by_pages_row_evolution');
    });

    it('should show goals by page titles', async function() {
        await page.click('.ui-widget .ui-dialog-titlebar-close');
        await page.evaluate(function(){
            $('div.dimensionCategory:nth-child(2) > ul:nth-child(1) > li:nth-child(4)').click();
        });
        await page.waitForTimeout(100);
        await page.waitForSelector('.dimensionReport .dataTableVizGoals');
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);

        var report = await page.$('.dimensionReport');
        expect(await report.screenshot()).to.matchImage('goals_by_page_titles');
    });

    it('should show goals by entry page', async function() {

        await page.evaluate(function(){
            $('div.dimensionCategory:nth-child(2) > ul:nth-child(1) > li:nth-child(2)').click();
        });
        await page.waitForTimeout(100);
        await page.waitForSelector('.dimensionReport .dataTableVizGoals');
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);

        var report = await page.$('.dimensionReport');
        expect(await report.screenshot()).to.matchImage('goals_by_entry_pages');
    });

    it('should show goals by entry page titles', async function() {

        await page.evaluate(function(){
            $('div.dimensionCategory:nth-child(2) > ul:nth-child(1) > li:nth-child(3)').click();
        });
        await page.waitForTimeout(100);
        await page.waitForSelector('.dimensionReport .dataTableVizGoals');
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);

        var report = await page.$('.dimensionReport');
        expect(await report.screenshot()).to.matchImage('goals_by_entry_page_titles');
    });


    it('should show action goals visualization for page urls', async function() {

        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2009-01-04#?idSite=1&period=year&date=2009-01-04&category=General_Actions&subcategory=General_Pages&viewDataTable=tableGoals");
        await page.waitForNetworkIdle();

        var report = await page.$('.dimensionReport');
        expect(await page.screenshot({fullPage: true})).to.matchImage('action_goals_visualization_page_urls');
    });

    it("should load subtables correctly for action goals visualization if row clicked", async function() {
        let firstRow = await page.jQuery('tr.subDataTable:first');
        await firstRow.click();
        await page.mouse.move(-10, -10);

        await page.waitForNetworkIdle();
        await page.waitForTimeout(250); // rendering

        expect(await page.screenshot({ fullPage: true })).to.matchImage('action_goals_visualization_page_urls_subtable');
    });

    it("should load row evolution", async function() {
        const row = await page.waitForSelector('.dataTable tbody tr:first-child');
        await row.hover();

        const icon = await page.waitForSelector('.dataTable tbody tr:first-child a.actionRowEvolution');
        await icon.click();

        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();

        const series = await page.waitForSelector('[data-name="series3"]');
        await series.click();

        await page.waitForTimeout(250); // rendering

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('action_goals_row_evolution');
    });
});
