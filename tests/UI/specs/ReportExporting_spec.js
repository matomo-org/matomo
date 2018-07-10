/*!
 * Piwik - free/libre analytics platform
 *
 * Export link screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ReportExporting", function () {
    var baseUrl = "?module=Widgetize&action=iframe&idSite=1&period=year&date=2012-08-09&isFooterExpandedInDashboard=1",
        referrersGetWebsitesUrl = baseUrl + "&moduleToWidgetize=Referrers&actionToWidgetize=getWebsites&filter_limit=5",
        visitsSummaryGetUrl = baseUrl + "&moduleToWidgetize=VisitsSummary&actionToWidgetize=get&forceView=1&viewDataTable=graphEvolution";

    function normalReportTest(format) {
        it("should export a normal report correctly when the " + format + " export is chosen", async function () {
            if (await page.url() != referrersGetWebsitesUrl) {
                await page.goto(referrersGetWebsitesUrl);
                await page.click('.activateExportSelection');
            }

            await page.waitForSelector('[name="format"] input[value="'+format+'"] + label');

            await page.click('[name="format"] input[value="' + format + '"] + label');
            await page.click('[name="filter_limit_all"] input[value="no"] + label');
            await page.evaluate(function () {
                $('[name=filter_limit] input').val(100).trigger('change');
            });

            var url = await page.evaluate(function () {
                return $('#reportExport a.btn').attr('href');
            });
            var pageContents = await page.downloadUrl(url);

            expect.file('Referrers.getWebsites_exported.' + format.toLowerCase() + '.txt').to.equal(pageContents);
        });
    }

    function evolutionReportTest(format) {
        it("should export an evolution graph report correctly when the " + format + " export is chosen", async function () {
            if (await page.url() != visitsSummaryGetUrl) {
                await page.goto(visitsSummaryGetUrl);
                await page.click('.activateExportSelection');
            }

            await page.waitForSelector('[name="format"] input[value="'+format+'"] + label');

            await page.click('[name="format"] input[value="'+format+'"] + label');
            await page.click('[name="filter_limit_all"] input[value="no"] + label');
            await page.evaluate(function(){
                $('[name=filter_limit] input').val(100).trigger('change');
            });

            var url = await page.evaluate(function() {
                return $('#reportExport a.btn').attr('href');
            });
            var pageContents = await page.downloadUrl(url);

            expect.file('VisitsSummary.get_exported.' + format.toLowerCase() + '.txt').to.equal(pageContents);
        });
    }

    function rowEvolutionReportTest(format) {
        it("should export an row evolution graph report correctly when the " + format + " export link is clicked", async function () {
            if (!page.url() || page.url().indexOf('popover') == -1) {
                await page.goto(referrersGetWebsitesUrl);

                const row = await page.waitForSelector('tbody tr:first-child');
                await row.hover();

                const icon = await page.waitForSelector('tbody tr:first-child a.actionRowEvolution');
                await icon.click();

                await page.waitForSelector('.ui-dialog');
                await page.waitForNetworkIdle();

                await page.click('.ui-dialog .activateExportSelection');
            }

            await page.waitForSelector('[name="format"] input[value="'+format+'"] + label');

            await page.click('[name="format"] input[value="'+format+'"] + label');
            await page.click('[name="filter_limit_all"] input[value="no"] + label');
            await page.evaluate(function(){
                $('[name=filter_limit] input').val(100).trigger('change');
            });

            var url = await page.evaluate(function() {
                return $('#reportExport a.btn').attr('href');
            });
            var pageContents = await page.downloadUrl(url);

            expect.file('RowEvolution_exported.' + format.toLowerCase() + '.txt').to.equal(pageContents);
        });
    }

    var formats = ['CSV', 'TSV', 'XML', 'JSON', 'PHP'];
    formats.forEach(normalReportTest);
    formats.forEach(evolutionReportTest);
    formats.forEach(rowEvolutionReportTest);
});