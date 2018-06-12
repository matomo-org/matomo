/*!
 * Piwik - free/libre analytics platform
 *
 * Pie graph screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PieGraph", function () {
    const url = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
              + "actionToWidgetize=getKeywords&viewDataTable=graphPie&isFooterExpandedInDashboard=1";

    it("should load correctly", async function () {
        await page.goto(url);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('load');
    });

    it("should show tooltip on hover", async function () {
        await page.hover('.piwik-graph');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('pie_segment_tooltip');
    });

    it("should display the metric picker on hover of metric picker icon", async function () {
        await page.hover('.jqplot-seriespicker');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('metric_picker_shown');
    });

    it("should change displayed metric when another metric picked", async function () {
        const element = await page.jQuery('.jqplot-seriespicker-popover input:not(:checked):first + label');
        await element.click();

        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('other_metric');
    });
});