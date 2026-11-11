/*!
 * Matomo - free/libre analytics platform
 *
 * Insights screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Insights", function () {
    var url = "?module=Widgetize&action=iframe&idSite=1&period=year&date=2012-08-09&moduleToWidgetize=Actions&actionToWidgetize=getPageUrls&isFooterExpandedInDashboard=1&viewDataTable=insightsVisualization";

    it("should load correctly", async function() {
        await page.goto(url);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('initial');
    });

    describe('when comparing with the previous year in the date selector', function () {
        const monthUrl = url.replace('period=year', 'period=month');

        async function title()
        {
            return page.evaluate(() => $('.insightsDataTable').attr('title'));
        }

        it("should compare with the previous month by default", async function () {
            await page.goto(monthUrl);

            expect(await title()).to.contain('July 2012');
        });

        it("should compare with the month selected in the date selector", async function () {
            await page.goto(monthUrl + '&compareDates[]=2011-08-09&comparePeriods[]=month');

            expect(await title()).to.contain('August 2011');
            // the comparison is the one of the date selector, so neither the control choosing it nor the message
            // saying the visualization ignores the comparison is shown
            expect(await page.evaluate(() => $('select[name=comparedToXPeriodsAgo]').length)).to.equal(0);
            expect(await page.evaluate(() => $('.datatableFooterMessage:contains("does not support")').length)).to.equal(0);
        });

        it("should keep comparing with the selected month when another segment is compared too", async function () {
            await page.goto(monthUrl + '&compareDates[]=2011-08-09&comparePeriods[]=month&compareSegments[]=' + encodeURIComponent('browserCode==FF'));

            expect(await title()).to.contain('August 2011');
        });
    });

});
