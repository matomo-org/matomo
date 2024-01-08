/*!
 * Matomo - free/libre analytics platform
 *
 * evolution graph screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('EvolutionGraphIncompleteData', function () {
    const url = '?module=Widgetize&action=iframe&idSite=1&period=day&date=2012-01-31&evolution_day_last_n=30'
              + '&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry&viewDataTable=graphEvolution'
              + '&isFooterExpandedInDashboard=1';

    before(() => {
        testEnvironment.forceArchiveStates = {
          '2012-01-08': 'incomplete',
          '2012-01-09': 'incomplete',
          '2012-01-10': 'incomplete',
          // center point used for tooltip check
          '2012-01-16': 'incomplete',
        };

        testEnvironment.save();
    });

    after(() => {
        delete testEnvironment.forceArchiveStates;
        testEnvironment.save();
    });

    it('should show incomplete data points', async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('graph');
    });

    it('tooltip for incomplete period should say "incomplete period"', async function () {
        const graph = await page.$('.piwik-graph');
        const boundingBox = await graph.boundingBox();

        await page.mouse.move(
            boundingBox.x + boundingBox.width / 2,
            boundingBox.y + boundingBox.height / 2
        );

        const tooltipContent = await page.evaluate(() => $('.ui-tooltip').text().trim());

        expect(tooltipContent).to.contain('Incomplete Period');
    });
});
