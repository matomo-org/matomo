/*!
 * Matomo - free/libre analytics platform
 *
 * Dashboard screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('SingleMetricView', function () {
    var url = "?module=Widgetize&action=iframe&idSite=1&period=year&date=2012-08-09&moduleToWidgetize=Dashboard&"
        + "actionToWidgetize=index&idDashboard=1";
    var rangeUrl = "?module=Widgetize&action=iframe&idSite=1&period=range&date=2012-08-07,2012-08-10&moduleToWidgetize=Dashboard&"
        + "actionToWidgetize=index&idDashboard=1";

    it('should load correctly', async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();

        // Add the Single Metric (KPI Metric) widget directly instead of going
        // through the Add Widget modal so the screenshot stays focused on the
        // widget itself rather than the surrounding picker flow.
        await page.evaluate(function () {
            return new Promise(function (resolve) {
                window.widgetsHelper.getWidgetObjectFromUniqueId(
                    'widgetCoreVisualizationssingleMetricViewcolumn',
                    function (widget) {
                        $('#dashboardWidgetsArea').dashboard(
                            'addWidget', widget.uniqueId, 1, widget.parameters, true, false
                        );
                        resolve();
                    }
                );
            });
        });

        var elem = await page.waitForSelector('#widgetCoreVisualizationssingleMetricViewcolumn');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(250);

        expect(await elem.screenshot()).to.matchImage('loaded');
    });

    it('should handle formatted metrics properly', async function () {
        await page.evaluate(() => {
            $('#dashboardWidgetsArea #widgetCoreVisualizationssingleMetricViewcolumn .jqplot-seriespicker').trigger('mouseenter');
        });
        await page.webpage.evaluate(function(){
            $('#dashboardWidgetsArea .jqplot-seriespicker-popover label:contains(Revenue):eq(0)').click();
        });
        await page.waitForNetworkIdle();
        await page.waitForTimeout(250);

        var elem = await page.waitForSelector('#dashboardWidgetsArea #widgetCoreVisualizationssingleMetricViewcolumn');
        expect(await elem.screenshot()).to.matchImage('formatted_metric');
    });

    it('should handle individual goal metrics properly', async function () {
        await page.evaluate(function(){
            $('#dashboardWidgetsArea #widgetCoreVisualizationssingleMetricViewcolumn .jqplot-seriespicker').last().trigger('mouseenter');
        });
        await page.waitForTimeout(250);
        await page.evaluate(function(){
            $('#dashboardWidgetsArea .jqplot-seriespicker-popover label:contains(_x):eq(0)').click()
        });
        await page.waitForNetworkIdle();
        await page.waitForTimeout(250);

        var elem = await page.$('#dashboardWidgetsArea #widgetCoreVisualizationssingleMetricViewcolumn');
        expect(await elem.screenshot()).to.matchImage('goal_metric');
    });

    it('should handle range periods correctly', async function () {
        await page.goto(rangeUrl);
        await page.evaluate(function(){
            $('#dashboardWidgetsArea #widgetCoreVisualizationssingleMetricViewcolumn .jqplot-seriespicker').trigger('mouseenter');
        });
        await page.waitForTimeout(250);
        await page.evaluate(function(){
            $('#dashboardWidgetsArea #widgetCoreVisualizationssingleMetricViewcolumn .jqplot-seriespicker-popover label:contains(Revenue):eq(0)').click()
        });
        await page.waitForNetworkIdle();
        await page.waitForTimeout(250);

        var elem = await page.$('#dashboardWidgetsArea #widgetCoreVisualizationssingleMetricViewcolumn');
        expect(await elem.screenshot()).to.matchImage('range');
    });

    // selects a metric in the widget's series picker and returns the evolution
    // indicator's css classes + label so we can assert on them
    async function selectMetricAndReadEvolution(metricLabel) {
        await page.waitForSelector('#dashboardWidgetsArea #widgetCoreVisualizationssingleMetricViewcolumn');
        await page.evaluate(function () {
            $('#dashboardWidgetsArea #widgetCoreVisualizationssingleMetricViewcolumn .jqplot-seriespicker').trigger('mouseenter');
        });
        await page.waitForTimeout(250);
        await page.evaluate(function (label) {
            $('#dashboardWidgetsArea .jqplot-seriespicker-popover label:contains(' + label + '):eq(0)').click();
        }, metricLabel);
        await page.waitForNetworkIdle();
        await page.waitForTimeout(250);

        return page.evaluate(function () {
            var el = document.querySelector('#dashboardWidgetsArea #widgetCoreVisualizationssingleMetricViewcolumn .metricEvolution > span');
            return el ? { className: el.className, text: el.textContent.trim() } : null;
        });
    }

    it('should show an increase of a lower-is-better metric as a negative evolution', async function () {
        // bounce rate is a "lower is better" metric, so an increasing value must be
        // shown as a negative (red) evolution and a decrease as a positive (green) one
        await page.goto(url);
        await page.waitForNetworkIdle();

        var evolution = await selectMetricAndReadEvolution('Bounce Rate');

        expect(evolution).to.not.equal(null);
        var hasUp = evolution.className.indexOf('evolution-up') !== -1;
        var hasDown = evolution.className.indexOf('evolution-down') !== -1;
        expect(hasUp || hasDown).to.equal(true);

        if (hasUp) {
            expect(evolution.className).to.contain('negative-evolution');
            expect(evolution.className).to.not.contain('positive-evolution');
        } else {
            expect(evolution.className).to.contain('positive-evolution');
            expect(evolution.className).to.not.contain('negative-evolution');
        }
    });

    it('should show a -100% evolution when the current value dropped to zero', async function () {
        // March 2012 has visits, April 2012 has none, so the current value is zero
        // while the previous period has data: the evolution must read as -100%
        var zeroUrl = "?module=Widgetize&action=iframe&idSite=1&period=month&date=2012-04-15&moduleToWidgetize=Dashboard&"
            + "actionToWidgetize=index&idDashboard=1";
        await page.goto(zeroUrl);
        await page.waitForNetworkIdle();

        var evolution = await selectMetricAndReadEvolution('Visits');

        expect(evolution).to.not.equal(null);
        expect(evolution.text).to.contain('-100');
        expect(evolution.className).to.contain('evolution-down');
    });

    it('should show a message when the selected metric has no data for the period', async function () {
        // unique visitors are not archived for year periods (enable_processing_unique_visitors_year=0),
        // so a KPI widget pinned to that metric must show the "unavailable" message instead of a "0".
        var unavailableUrl = "?module=Widgetize&action=iframe&moduleToWidgetize=CoreVisualizations&"
            + "actionToWidgetize=singleMetricView&column=nb_uniq_visitors&idSite=1&period=year&date=2012-08-09";
        await page.goto(unavailableUrl);
        await page.waitForSelector('.singleMetricView');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(250);

        var message = await page.$('.singleMetricView .metric-unavailable');
        expect(message).to.not.equal(null);

        // the element being present isn't enough: assert the actual message text is rendered,
        // otherwise an empty container would silently pass and hide a regression
        var messageText = await (await message.getProperty('textContent')).jsonValue();
        expect(messageText).to.contain("This metric isn't available for this period.");
    });
});
