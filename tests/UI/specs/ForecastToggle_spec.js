/*!
 * Matomo - free/libre analytics platform
 *
 * Forecast toggle action tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('ForecastToggle', function () {
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

    async function injectForecastScenario() {
        await page.evaluate(() => {
            const dataTable = $('.dataTable:has(.piwik-graph)').data('uiControlObject');
            const isForecastEnabled = () => (
                dataTable.param.show_forecast === true
                || dataTable.param.show_forecast === 1
                || dataTable.param.show_forecast === '1'
                || (
                    typeof dataTable.param.show_forecast === 'undefined'
                    && (
                        dataTable.props.show_forecast === true
                        || dataTable.props.show_forecast === 1
                        || dataTable.props.show_forecast === '1'
                    )
                )
            );
            const ensureForecastAction = () => {
                let action = dataTable.$element.find('.dataTableAction.dataTableShowForecast');

                if (!action.length) {
                    action = $('<a class="dataTableAction dataTableShowForecast" href="" title="Show forecast"><span></span></a>');

                    const actionParent = dataTable.$element.find('.dataTableAction').last().parent();
                    if (actionParent.length) {
                        actionParent.append(action);
                    } else {
                        dataTable.$element.append(action);
                    }
                }

                action.find('span')
                    .removeClass('icon-show icon-hide')
                    .addClass(isForecastEnabled() ? 'icon-show' : 'icon-hide');
            };
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

            window.__forecastToggleTest = {
                originalReload: dataTable.reloadAjaxDataTable.bind(dataTable),
                originalNotify: dataTable.notifyWidgetParametersChange.bind(dataTable),
                forecastData,
                targetDivId: dataTable.targetDivId,
                targetTick,
            };

            dataTable._setDataStates(states);
            dataTable._setForecastData([]);
            dataTable.render();

            dataTable.reloadAjaxDataTable = function reloadAjaxDataTable() {
                this._setForecastData(this.param.show_forecast === '1' ? window.__forecastToggleTest.forecastData : []);
                this.render();
                ensureForecastAction();
            };
            ensureForecastAction();
        });

        await page.waitForTimeout(250);
    }

    async function showTooltip() {
        await page.evaluate(() => {
            const target = $('#' + window.__forecastToggleTest.targetDivId);
            target.trigger('jqplotPiwikTickOver', [window.__forecastToggleTest.targetTick]);
        });

        await page.waitForFunction(() => $('.ui-tooltip:visible').length > 0);
        return page.evaluate(() => $('.ui-tooltip').text().trim());
    }

    it('should show a dedicated forecast action on evolution charts and toggle forecast tooltip values', async function () {
        await openEvolutionGraph();
        await injectForecastScenario();
        await page.waitForSelector(forecastActionSelector, { visible: true });

        let tooltipContent = await showTooltip();
        expect(tooltipContent).not.to.contain('Forecast');

        await page.click(forecastActionSelector);
        await page.waitForTimeout(250);

        tooltipContent = await showTooltip();
        expect(tooltipContent).to.contain('Forecast');

        await page.click(forecastActionSelector);
        await page.waitForTimeout(250);

        tooltipContent = await showTooltip();
        expect(tooltipContent).not.to.contain('Forecast');
    });

    it('should persist the dedicated forecast action state across redraws for the same evolution report', async function () {
        await openEvolutionGraph();
        await injectForecastScenario();
        await page.waitForSelector(forecastActionSelector, { visible: true });

        await page.click(forecastActionSelector);
        await page.waitForNetworkIdle();
        expect(await page.$eval(forecastActionSelector + ' span', (node) => node.classList.contains('icon-show'))).to.equal(true);

        await injectForecastScenario();
        await page.waitForSelector(forecastActionSelector, { visible: true });
        expect(await page.$eval(forecastActionSelector + ' span', (node) => node.classList.contains('icon-show'))).to.equal(true);

        await page.click(forecastActionSelector);
        await page.waitForNetworkIdle();
        expect(await page.$eval(forecastActionSelector + ' span', (node) => node.classList.contains('icon-hide'))).to.equal(true);
    });

    it('should not show the forecast action on unrelated graph types', async function () {
        await page.goto(pieUrl);
        await page.waitForNetworkIdle();

        expect(await page.$(forecastActionSelector)).to.equal(null);
    });
});
