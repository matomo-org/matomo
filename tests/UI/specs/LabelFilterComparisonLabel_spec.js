/*!
 * Matomo - free/libre analytics platform
 *
 * Checks that a report specific row identifier cannot reach the graph tooltip as markup when it is
 * promoted to the label of a compared row.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("LabelFilterComparisonLabel", function () {
    this.fixture = "Piwik\\Tests\\Fixtures\\PageViewsWithRawRowIdentifier";

    // must match PageViewsWithRawRowIdentifier::FIRST_ROW_IDENTIFIER
    const firstRowIdentifier = 'row zero <b>markup</b>';

    // The label parameter is read with Common::getRequestVar(), which sanitizes it, so every
    // character it would escape has to arrive percent encoded. On top of that '>' separates the
    // parts of a recursive label and the label is urldecoded once per level of the recursive
    // search, so a '>' that belongs to the label itself needs one extra level of encoding.
    function labelParameter(label) {
        return encodeURIComponent(encodeURIComponent(label).replace(/%3E/g, '%253E'));
    }

    // labelSeries selects by label index, so the first label supplies the x axis label while the
    // second one supplies the series data. Both are needed for the graph to render a data point.
    const url = "?module=Widgetize&action=iframe&moduleToWidgetize=Actions"
        + "&actionToWidgetize=getPageUrls&idSite=1&period=day&date=2010-03-06"
        + "&viewDataTable=graphVerticalBar&filter_limit=-1"
        + "&compare=1&compareSegments[]=&labelSeries=0"
        + "&label[]=" + labelParameter(firstRowIdentifier)
        + "&label[]=" + labelParameter('row 1');

    it("should render a row identifier promoted to a comparison label as text, not as markup", async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();
        await page.waitForSelector('.jqplot-target');

        // jqplot fires jqplotDataHighlight when a bar is hovered, and jqplot.js turns that into the
        // tooltip. Triggering the event directly skips only jqplot's hit detection on the canvas,
        // which is not what this test is about, and keeps the test independent of bar geometry.
        await page.evaluate(function () {
            $('.jqplot-target').trigger('jqplotDataHighlight', [0, 0]);
        });
        await page.waitForSelector('.ui-tooltip-content h3');

        const tooltip = await page.evaluate(function () {
            const header = document.querySelector('.ui-tooltip-content h3');
            return {
                text: header.textContent,
                elementCount: header.children.length,
            };
        });

        expect(tooltip.elementCount).to.equal(0);
        expect(tooltip.text).to.contain(firstRowIdentifier);
    });
});
