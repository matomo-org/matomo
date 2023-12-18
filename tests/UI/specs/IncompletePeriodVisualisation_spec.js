/*!
 * Matomo - free/libre analytics platform
 *
 * Incomplete Period Visualisation Test
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('IncompletePeriodVisualisation', function () {
    this.fixture = 'Piwik\\Tests\\Fixtures\\SomeVisitsLastYearAndThisYear';

    const generalParams = 'idSite=1&period=year&date=today';
    const pageUrl = '?module=CoreHome&action=index&' + generalParams;

    it('should load visitors > overview page and show incomplete period', async function () {
        await page.goto(pageUrl + generalParams + '&segment=&category=General_Visitors&subcategory=General_Overview#?idSite=1&period=year&date=today&segment=&category=General_Visitors&subcategory=General_Overview');
        await page.waitForNetworkIdle();
        const pageWrap = await page.$('.pageWrap');

        expect(await pageWrap.screenshot()).to.matchImage('visitors_overview');
    });

    it('tooltip for incomplete period should say "incomplete period"', async function () {
        const graph = await page.$('.piwik-graph');
        const boundingBox = await graph.boundingBox();

        await page.mouse.move(
          boundingBox.x + boundingBox.width - 50,
          boundingBox.y + boundingBox.height - 50
        );

        const tooltipContent = await page.evaluate(() => $('.ui-tooltip').text().trim());

        expect(tooltipContent).to.contain('Incomplete Period');
    });
});
