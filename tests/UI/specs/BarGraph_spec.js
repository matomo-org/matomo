/*!
 * Matomo - free/libre analytics platform
 *
 * Bar graph screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("BarGraph", function () {
    var tokenAuth = "c4ca4238a0b923820dcc509a6f75849b",
        viewTokenAuth = "a4ca4238a0b92382" + "0dcc509a6f75849f",
        url = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
            + "actionToWidgetize=getKeywords&viewDataTable=graphVerticalBar&isFooterExpandedInDashboard=1&";
    const multiMetricUrl = url + "columns=nb_visits,nb_actions,bounce_rate&filter_add_columns_when_show_all_columns=0&token_auth=" + viewTokenAuth;

    before(function () {
        // use real auth + token auth to test that auth works when widgetizing reports in an iframe
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();
    });

    it("should fail when admin token is used", async function () {
        await page.goto(url + 'token_auth=' + tokenAuth);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('load_fail_when_token_used');
    });

    it("should load correctly", async function () {
        await page.goto(url + 'token_auth=' + viewTokenAuth);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('load');
    });

    it("should display the metric picker when the metric picker button is clicked", async function () {
        await page.click('.metrics-picker__toggle');
        expect(await page.screenshot({ fullPage: true })).to.matchImage('metric_picker_shown');
        await page.keyboard.press('Escape');
    });

    it("should display multiple metrics when another metric picked", async function () {
        await page.click('.metrics-picker__toggle');
        await page.waitForSelector('.metrics-picker__options input');
        var element = await page.jQuery('.metrics-picker__options input:not(:checked):first');
        await element.click();
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('other_metric');
    });

    it("should render the footer legend for bar graphs", async function () {
        await page.goto(multiMetricUrl);
        await page.waitForNetworkIdle();

        const legendState = await page.evaluate(function () {
            const footer = document.querySelector('.jqplot-legend-footer.has-legend');

            return {
                hasLegend: !!footer,
                itemCount: footer ? footer.querySelectorAll('.jqplot-legend-item').length : 0,
                swatchCount: footer ? footer.querySelectorAll('.jqplot-legend-swatch').length : 0,
            };
        });

        expect(legendState.hasLegend).to.equal(true);
        expect(legendState.itemCount).to.equal(3);
        expect(legendState.swatchCount).to.equal(3);
    });
});
