/*!
 * Matomo - free/libre analytics platform
 *
 * Forecast rendering tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('Forecast', function () {
    const evolutionUrl = '?module=Widgetize&action=iframe&idSite=1&period=day&date=2012-01-31&evolution_day_last_n=30'
        + '&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry&viewDataTable=graphEvolution'
        + '&isFooterExpandedInDashboard=1';
    const pieUrl = '?module=Widgetize&action=iframe&idSite=1&period=day&date=2012-01-31'
        + '&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry&viewDataTable=graphPie'
        + '&isFooterExpandedInDashboard=1';
    const forecastActionSelector = '.dataTableAction.dataTableShowForecast';

    async function openEvolutionGraph() {
        await page.goto(evolutionUrl);
        await page.waitForNetworkIdle();
        await page.waitForSelector('.piwik-graph', { visible: true });
    }

    // The forecast is always active, so there is no UI to toggle it on. To exercise the
    // tooltip rendering deterministically we inject a single incomplete tick with a forecast
    // value, mirroring what the backend produces for an in-progress period.
    async function injectForecastScenario() {
        await page.evaluate(() => {
            const dataTable = $('.dataTable:has(.piwik-graph)').data('uiControlObject');
            const normalizedSeries = dataTable.data.map((series) => series.map((value) => (
                Array.isArray(value) ? value[1] : value
            )));
            const targetSeries = normalizedSeries.findIndex((series) => series.length >= 4);

            if (targetSeries === -1) {
                throw new Error('No usable series found for forecast injection.');
            }

            const targetTick = normalizedSeries[targetSeries].length - 2;
            const previousTick = targetTick - 1;
            const previousValue = normalizedSeries[targetSeries][previousTick];
            const states = Array(normalizedSeries[0].length).fill('complete');
            states[targetTick] = 'incomplete';

            const forecastData = normalizedSeries.map((series) => Array(series.length).fill(null));
            forecastData[targetSeries][targetTick] = previousValue + 5;

            window.__forecastTest = {
                targetDivId: dataTable.targetDivId,
                targetTick,
            };

            dataTable._setDataStates(states);
            dataTable._setForecastData(forecastData);
            dataTable.render();
        });

        await page.waitForTimeout(250);
    }

    async function showTooltip() {
        await page.evaluate(() => {
            const target = $('#' + window.__forecastTest.targetDivId);
            target.trigger('jqplotPiwikTickOver', [window.__forecastTest.targetTick]);
        });

        await page.waitForFunction(() => $('.ui-tooltip:visible').length > 0);
        return page.evaluate(() => $('.ui-tooltip').text().trim());
    }

    it('should not expose a forecast toggle action on evolution charts', async function () {
        await openEvolutionGraph();

        expect(await page.$(forecastActionSelector)).to.equal(null);
    });

    it('should always render forecast values in the tooltip for incomplete ticks', async function () {
        await openEvolutionGraph();
        await injectForecastScenario();

        const tooltipContent = await showTooltip();
        expect(tooltipContent).to.contain('Forecast');
    });

    it('should not render forecast on unrelated graph types', async function () {
        await page.goto(pieUrl);
        await page.waitForNetworkIdle();

        expect(await page.$(forecastActionSelector)).to.equal(null);
    });
});
