/*!
 * Matomo - free/libre analytics platform
 *
 * Forecast Period Visualisation Test
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('ForecastPeriodVisualisation', function () {
    const pageUrl = '?module=Widgetize&action=iframe&idSite=1&period=day&date=2012-01-31&evolution_day_last_n=30'
        + '&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry&viewDataTable=graphEvolution'
        + '&isFooterExpandedInDashboard=1';

    async function loadForecastScenario(scenario) {
        await page.goto(pageUrl);
        await page.waitForNetworkIdle();

        await page.evaluate((currentScenario) => {
            function normalizePoint(value) {
                return Array.isArray(value) ? value[1] : value;
            }

            function setPointValue(dataTable, seriesIndex, tickIndex, value) {
                const point = dataTable.data[seriesIndex][tickIndex];

                if (Array.isArray(point)) {
                    dataTable.data[seriesIndex][tickIndex] = [point[0], value];
                } else {
                    dataTable.data[seriesIndex][tickIndex] = value;
                }
            }

            function resolveForecastValue(valueConfig, context) {
                if (valueConfig === null || typeof valueConfig === 'number') {
                    return valueConfig;
                }

                switch (valueConfig.type) {
                    case 'currentPlus':
                        return context.currentValue + valueConfig.delta;
                    case 'previousPlus':
                        return context.previousValue + valueConfig.delta;
                    case 'absolute':
                        return valueConfig.value;
                    default:
                        throw new Error(`Unsupported forecast config type "${valueConfig.type}".`);
                }
            }

            const dataTable = $('.dataTable:has(.piwik-graph)').data('uiControlObject');
            const normalizedSeries = dataTable.data.map((series) => series.map(normalizePoint));
            const usedOffsets = [
                currentScenario.targetTickOffset || 0,
                ...currentScenario.incompleteOffsets,
                ...currentScenario.actualValues.map((entry) => entry.offset),
                ...currentScenario.forecastValues.map((entry) => entry.offset),
            ];
            const maxOffset = Math.max(...usedOffsets);

            let targetSeries = -1;
            let startTick = -1;

            for (let seriesIndex = 0; seriesIndex < normalizedSeries.length; seriesIndex += 1) {
                const series = normalizedSeries[seriesIndex];
                const lastUsableTick = series.length - 1 - maxOffset;

                for (let tickIndex = lastUsableTick; tickIndex >= Math.max(1, lastUsableTick - 7); tickIndex -= 1) {
                    let isUsable = Number.isFinite(series[tickIndex - 1]);

                    for (let offset = 0; offset <= maxOffset && isUsable; offset += 1) {
                        isUsable = Number.isFinite(series[tickIndex + offset]);
                    }

                    if (isUsable) {
                        targetSeries = seriesIndex;
                        startTick = tickIndex;
                        break;
                    }
                }

                if (startTick !== -1) {
                    break;
                }
            }

            if (startTick === -1) {
                throw new Error(`Could not find a contiguous run of points for forecast scenario "${currentScenario.name}".`);
            }

            currentScenario.actualValues.forEach((entry) => {
                const tickIndex = startTick + entry.offset;
                setPointValue(dataTable, targetSeries, tickIndex, entry.value);
                normalizedSeries[targetSeries][tickIndex] = entry.value;
            });

            const states = Array(normalizedSeries[0].length).fill('complete');
            currentScenario.incompleteOffsets.forEach((offset) => {
                states[startTick + offset] = 'incomplete';
            });

            const forecastData = normalizedSeries.map((series) => Array(series.length).fill(null));
            currentScenario.forecastValues.forEach((entry) => {
                const tickIndex = startTick + entry.offset;
                const currentValue = normalizedSeries[targetSeries][tickIndex];
                const previousValue = normalizedSeries[targetSeries][tickIndex - 1];

                forecastData[targetSeries][tickIndex] = resolveForecastValue(entry.value, {
                    currentValue,
                    previousValue,
                });
            });

            dataTable._setDataStates(states);
            dataTable._setForecastData(forecastData);
            dataTable.render();

            window.__forecastTest = {
                startTick,
                targetTick: startTick + (currentScenario.targetTickOffset || 0),
                targetDivId: dataTable.targetDivId,
            };
        }, scenario);

        await page.waitForSelector('.piwik-graph canvas');
    }

    const scenarios = {
        continuous_forecast_line: {
            name: 'continuous_forecast_line',
            incompleteOffsets: [0, 1, 2],
            actualValues: [],
            forecastValues: [
                { offset: 0, value: { type: 'previousPlus', delta: 4 } },
                { offset: 1, value: { type: 'previousPlus', delta: 8 } },
                { offset: 2, value: { type: 'previousPlus', delta: 12 } },
            ],
            targetTickOffset: 0,
        },
        forecast_tooltip_incomplete_period: {
            name: 'forecast_tooltip_incomplete_period',
            incompleteOffsets: [0, 1, 2],
            actualValues: [],
            forecastValues: [
                { offset: 0, value: { type: 'previousPlus', delta: 4 } },
                { offset: 1, value: { type: 'previousPlus', delta: 8 } },
                { offset: 2, value: { type: 'previousPlus', delta: 12 } },
            ],
            targetTickOffset: 0,
        },
        suppressed_forecast_gap: {
            name: 'suppressed_forecast_gap',
            incompleteOffsets: [0, 1],
            actualValues: [
                { offset: 0, value: 3 },
                { offset: 1, value: 9 },
            ],
            forecastValues: [
                { offset: 0, value: { type: 'absolute', value: 6 } },
                { offset: 1, value: null },
            ],
            targetTickOffset: 1,
        },
        forecast_resumes_after_suppressed_gap: {
            name: 'forecast_resumes_after_suppressed_gap',
            incompleteOffsets: [0, 1, 2],
            actualValues: [
                { offset: 0, value: 3 },
                { offset: 1, value: 9 },
                { offset: 2, value: 2 },
            ],
            forecastValues: [
                { offset: 0, value: { type: 'absolute', value: 6 } },
                { offset: 1, value: null },
                { offset: 2, value: { type: 'absolute', value: 5 } },
            ],
            targetTickOffset: 2,
        },
        forecast_matches_actual_keeps_tooltip: {
            name: 'forecast_matches_actual_keeps_tooltip',
            incompleteOffsets: [0],
            actualValues: [
                { offset: 0, value: 7 },
            ],
            // Forecast equals the tracked value exactly (the "flat at current" case): the
            // renderer skips the redundant dashed connector/diamond, but the forecast value
            // must still be reported in the tooltip.
            forecastValues: [
                { offset: 0, value: { type: 'currentPlus', delta: 0 } },
            ],
            targetTickOffset: 0,
        },
        carry_forward_forecast_on_zero_data: {
            name: 'carry_forward_forecast_on_zero_data',
            incompleteOffsets: [0, 1, 2],
            actualValues: [
                { offset: 0, value: 2 },
                { offset: 1, value: 0 },
                { offset: 2, value: 0 },
            ],
            forecastValues: [
                { offset: 0, value: { type: 'absolute', value: 5 } },
                { offset: 1, value: { type: 'absolute', value: 6 } },
                { offset: 2, value: { type: 'absolute', value: 7 } },
            ],
            targetTickOffset: 2,
        },
    };

    it('should show one continuous forecast line across incomplete periods', async function () {
        await loadForecastScenario(scenarios.continuous_forecast_line);

        const graph = await page.$('.piwik-graph');
        expect(await graph.screenshot()).to.matchImage('continuous_forecast_line');
    });

    it('should show forecast details in the tooltip for an incomplete period', async function () {
        await loadForecastScenario(scenarios.forecast_tooltip_incomplete_period);

        await page.evaluate(() => {
            const target = $('#' + window.__forecastTest.targetDivId);
            target.trigger('jqplotPiwikTickOver', [window.__forecastTest.targetTick]);
        });

        await page.waitForFunction(() => $('.ui-tooltip:visible').length > 0);

        const tooltipContent = await page.evaluate(() => $('.ui-tooltip').text().trim());
        expect(tooltipContent).to.contain('Incomplete Period');
        expect(tooltipContent).to.contain('Forecast');
    });

    it('should suppress forecast rendering when the forecasted point is omitted', async function () {
        await loadForecastScenario(scenarios.suppressed_forecast_gap);

        const graph = await page.$('.piwik-graph');
        expect(await graph.screenshot()).to.matchImage('suppressed_forecast_gap');
    });

    it('should restart forecast rendering after a suppressed gap without bridging it', async function () {
        await loadForecastScenario(scenarios.forecast_resumes_after_suppressed_gap);

        const graph = await page.$('.piwik-graph');
        expect(await graph.screenshot()).to.matchImage('forecast_resumes_after_suppressed_gap');
    });

    it('should show forecast carry-forward across zero-data incomplete periods', async function () {
        await loadForecastScenario(scenarios.carry_forward_forecast_on_zero_data);

        const graph = await page.$('.piwik-graph');
        expect(await graph.screenshot()).to.matchImage('carry_forward_forecast_on_zero_data');
    });

    it('should still report the forecast in the tooltip when it matches the tracked value', async function () {
        // when the forecast equals the tracked value, the redundant dashed connector
        // (drawn over the incomplete-period segment) is suppressed, but the forecast readout in
        // the tooltip is preserved -- so the "flat at current" projection is still communicated.
        await loadForecastScenario(scenarios.forecast_matches_actual_keeps_tooltip);

        await page.evaluate(() => {
            const target = $('#' + window.__forecastTest.targetDivId);
            target.trigger('jqplotPiwikTickOver', [window.__forecastTest.targetTick]);
        });

        await page.waitForFunction(() => $('.ui-tooltip:visible').length > 0);

        const tooltipContent = await page.evaluate(() => $('.ui-tooltip').text().trim());
        expect(tooltipContent).to.contain('Incomplete Period');
        expect(tooltipContent).to.contain('Forecast');
    });
});
