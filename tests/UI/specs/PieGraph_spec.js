/*!
 * Matomo - free/libre analytics platform
 *
 * Pie graph screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PieGraph", function () {
    const url = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
              + "actionToWidgetize=getKeywords&viewDataTable=graphPie&isFooterExpandedInDashboard=1";
    const plotLinesTweaksUrl = url + "&columns=nb_visits,nb_actions,bounce_rate&filter_add_columns_when_show_all_columns=0";

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
        await page.waitForSelector('.jqplot-seriespicker-popover input');
        var element = await page.jQuery('.jqplot-seriespicker-popover input:not(:checked):eq(0)');
        await element.click();

        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('other_metric');
    });

    describe("with_PlotLinesTweaks_enabled", function () {
        before(function () {
            testEnvironment.overrideConfig('FeatureFlags', 'PlotLinesTweaks_feature', 'enabled');
            testEnvironment.save();
        });

        after(function () {
            if (testEnvironment.configOverride.FeatureFlags) {
                delete testEnvironment.configOverride.FeatureFlags.PlotLinesTweaks_feature;
            }
            testEnvironment.save();
        });

        it("should render the footer legend for pie graphs", async function () {
            await page.goto(plotLinesTweaksUrl);
            await page.waitForNetworkIdle();

            const legendState = await page.evaluate(function () {
                const footer = document.querySelector('.jqplot-legend-footer.has-legend');
                const labels = footer ? Array.from(footer.querySelectorAll('.jqplot-legend-label')).map(function (label) {
                    return label.textContent.trim();
                }).filter(Boolean) : [];

                return {
                    hasLegend: !!footer,
                    itemCount: labels.length,
                    labels: labels,
                };
            });

            expect(legendState.hasLegend).to.equal(true);
            expect(legendState.itemCount).to.be.above(0);
            expect(legendState.labels.length).to.equal(legendState.itemCount);
        });
    });
});
