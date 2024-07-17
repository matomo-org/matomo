/*!
 * Matomo - free/libre analytics platform
 *
 * Dashboard screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('SingleMetricView', function () {
    this.timeout(5*60*1000); // timeout of 5 minutes per test

    var url = "?module=Widgetize&action=iframe&idSite=1&period=year&date=2012-08-09&moduleToWidgetize=Dashboard&"
        + "actionToWidgetize=index&idDashboard=1";
    var rangeUrl = "?module=Widgetize&action=iframe&idSite=1&period=range&date=2012-08-07,2012-08-10&moduleToWidgetize=Dashboard&"
        + "actionToWidgetize=index&idDashboard=1";

    it('should load correctly', async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();
        await page.click('.dashboard-manager a.title');

        await (await page.jQuery('.widgetpreview-categorylist>li:contains(Goals)')).hover(); // have to mouse move twice... otherwise Live! will just be highlighted
        await (await page.jQuery('.widgetpreview-categorylist > li:contains(KPI Metric)')).hover();

        await (await page.jQuery('.widgetpreview-widgetlist li:contains(KPI Metric)')).hover();
        await (await page.jQuery('.widgetpreview-widgetlist li:contains(KPI Metric)')).click();

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
});
