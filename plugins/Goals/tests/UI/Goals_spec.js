/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Goals", function () {
    this.fixture = 'Piwik\\Tests\\Fixtures\\SomePageGoalVisitsWithConversions';

    it('should show the goals overview', async function() {
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2009-01-04#?idSite=1&period=year&date=2009-01-04&category=Goals_Goals&subcategory=General_Overview");

        await page.waitForNetworkIdle();
        await page.waitForSelector('.dataTableVizGoals');

        var report = await page.$('.reporting-page');
        expect(await report.screenshot()).to.matchImage('overview');
    });

    it('should show goals by page', async function() {

        await page.evaluate(function(){
            $('div.dimensionCategory:nth-child(2) > ul:nth-child(1) > li:nth-child(1)').click();
        });

        await page.waitForSelector('.dimensionReport');
        await page.waitForNetworkIdle();

        var report = await page.$('.dimensionReport');
        expect(await report.screenshot()).to.matchImage('goals_by_pages');
    });

    it('should show goals by page titles', async function() {

        await page.evaluate(function(){
            $('div.dimensionCategory:nth-child(2) > ul:nth-child(1) > li:nth-child(3)').click();
        });

        await page.waitForSelector('.dimensionReport');
        await page.waitForNetworkIdle();

        var report = await page.$('.dimensionReport');
        expect(await report.screenshot()).to.matchImage('goals_by_page_titles');
    });

    it('should show goals by entry page', async function() {

        await page.evaluate(function(){
            $('div.dimensionCategory:nth-child(2) > ul:nth-child(1) > li:nth-child(2)').click();
        });

        await page.waitForSelector('.dimensionReport');
        await page.waitForNetworkIdle();

        var report = await page.$('.dimensionReport');
        expect(await report.screenshot()).to.matchImage('goals_by_entry_pages');
    });

    it('should show goals by entry page titles', async function() {

        await page.evaluate(function(){
            $('.datatableRelatedReports > ul:nth-child(1) > li:nth-child(2) > span:nth-child(1)').click();
        });

        await page.waitForSelector('.dimensionReport');
        await page.waitForNetworkIdle();

        var report = await page.$('.dimensionReport');
        expect(await report.screenshot()).to.matchImage('goals_by_entry_page_titles');
    });

});
