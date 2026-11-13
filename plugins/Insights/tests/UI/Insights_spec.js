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

            expect(await title()).to.contain('Jul 2012');
        });

        it("should compare with the month selected in the date selector", async function () {
            await page.goto(monthUrl + '&compareDates[]=2011-08-09&comparePeriods[]=month');

            expect(await title()).to.contain('Aug 2011');
            // the comparison is the one of the date selector, so neither the control choosing it nor the message
            // saying the visualization ignores the comparison is shown
            expect(await page.evaluate(() => $('select[name=comparedToXPeriodsAgo]').length)).to.equal(0);
            expect(await page.evaluate(() => $('.datatableFooterMessage:contains("does not support")').length)).to.equal(0);
            expect(await page.evaluate(() => $('.datatableFooterMessage:contains("cannot use the comparison")').length)).to.equal(0);
        });

        async function footerMessage()
        {
            return page.evaluate(() => $('.datatableFooterMessage').text());
        }

        it("should keep comparing with the selected month when another segment is compared too", async function () {
            await page.goto(monthUrl + '&compareDates[]=2011-08-09&comparePeriods[]=month&compareSegments[]=' + encodeURIComponent('browserCode==FF'));

            expect(await title()).to.contain('Aug 2011');
            expect(await footerMessage()).to.contain('The compared segments are ignored.');
            expect(await footerMessage()).not.to.contain('cannot use the comparison');
        });

        [
            ['a custom range', '&compareDates[]=2011-08-01,2011-08-31&comparePeriods[]=range'],
            ['two dates', '&compareDates[]=2011-08-09&comparePeriods[]=month&compareDates[]=2010-08-09&comparePeriods[]=month'],
            ['another period type', '&compareDates[]=2011-08-09&comparePeriods[]=day'],
        ].forEach(([comparison, params]) => {
            it(`should compare with the previous month when the date selector compares ${comparison}`, async function () {
                await page.goto(monthUrl + params);

                expect(await title()).to.contain('Jul 2012');
                expect(await page.evaluate(() => $('select[name=comparedToXPeriodsAgo]').length)).to.equal(1);
                expect(await footerMessage()).to.contain('cannot use the comparison selected in the date selector, so they compare with Jul 2012 instead');
            });
        });

        describe('on a date range', function () {
            const rangeUrl = url.replace('period=year&date=2012-08-09', 'period=range&date=2012-08-01,2012-08-31');

            it("should compare with the range selected in the date selector", async function () {
                await page.goto(rangeUrl + '&compareDates[]=2011-08-01,2011-08-31&comparePeriods[]=range');

                expect(await title()).to.contain('2011');
                expect(await footerMessage()).not.to.contain('cannot use the comparison');
            });

            it("should compare with the previous range when the selected range has another length", async function () {
                await page.goto(rangeUrl + '&compareDates[]=2011-08-01,2011-08-02&comparePeriods[]=range');

                expect(await title()).to.contain('Jul');
                expect(await title()).not.to.contain('2011');
                expect(await footerMessage()).to.contain('cannot use the comparison selected in the date selector, so they compare with Jul');
            });
        });
    });

});
