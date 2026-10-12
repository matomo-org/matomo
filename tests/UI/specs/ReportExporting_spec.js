/*!
 * Matomo - free/libre analytics platform
 *
 * Export link screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ReportExporting", function () {
    var baseUrl = "?module=Widgetize&action=iframe&idSite=1&period=year&date=2012-08-09&isFooterExpandedInDashboard=1",
        referrersGetWebsitesUrl = baseUrl + "&moduleToWidgetize=Referrers&actionToWidgetize=getWebsites&filter_limit=5",
        visitsSummaryGetUrl = baseUrl + "&moduleToWidgetize=VisitsSummary&actionToWidgetize=get&forceView=1&viewDataTable=graphEvolution";


    // Export leaves the 3-dots menu when the header line has room for it, so reach it wherever it
    // is rather than always through the menu.
    const openExport = async function (scope) {
        const at = (selector) => (scope ? `${scope} ${selector}` : selector);

        if (await page.$(at('[data-report-action="export"]'))) {
            await page.click(at('[data-report-action="export"] .mtm-selector__trigger'));
        } else {
            await page.click(at('.reportHeader__actionsTrigger'));
        }

        await page.click(at('.activateExportSelection'));
    };

    function normalReportTest(format) {
        it(`should export a normal report correctly when the ${format} export is chosen`, async function () {
            if (await page.url() !== referrersGetWebsitesUrl) {
                await page.goto(referrersGetWebsitesUrl);
                await openExport();
            }

            await page.waitForSelector('[name="format"] input[value="'+format+'"]');

            await page.click('[name="format"] input[value="' + format + '"]');
            await page.click('[name="filter_limit_all"] input[value="no"]');
            await page.click('input[name="option_format_metrics"]');
            await page.evaluate(function () {
                $('[name=filter_limit] input').val(100).trigger('change');
            });
            await page.waitForTimeout(250);

            var url = await page.evaluate(function () {
                return $('#reportExport a.btn').attr('href');
            });
            var pageContents = await page.downloadUrl(url);

            expect.fileMatchesContent('Referrers.getWebsites_exported.' + format.toLowerCase() + '.txt', pageContents);
        });
    }

    function evolutionReportTest(format) {
        it(`should export an evolution graph report correctly when the ${format} export is chosen`, async function () {
            if (await page.url() !== visitsSummaryGetUrl) {
                await page.goto(visitsSummaryGetUrl);
                await openExport();
            }

            await page.waitForSelector('[name="format"] input[value="'+format+'"]');

            await page.click('[name="format"] input[value="'+format+'"]');
            await page.click('[name="filter_limit_all"] input[value="no"]');
            await page.click('input[name="option_format_metrics"]');
            await page.evaluate(function(){
                $('[name=filter_limit] input').val(100).trigger('change');
            });
            await page.waitForTimeout(250);

            var url = await page.evaluate(function() {
                return $('#reportExport a.btn').attr('href');
            });
            var pageContents = await page.downloadUrl(url);

            expect.fileMatchesContent('VisitsSummary.get_exported.' + format.toLowerCase() + '.txt', pageContents);
        });
    }

    function rowEvolutionReportTest(format) {
        it(`should export an row evolution graph report correctly when the ${format} export link is clicked`, async function () {
            if (!page.url() || page.url().indexOf('popover') === -1) {
                await page.goto(referrersGetWebsitesUrl);

                const row = await page.waitForSelector('tbody tr:first-child');
                await row.hover();

                const icon = await page.waitForSelector('tbody tr:first-child a.actionRowEvolution');
                await icon.click();

                await page.waitForSelector('.ui-dialog');
                await page.waitForNetworkIdle();

                await openExport('.ui-dialog');
            }

            await page.waitForSelector('[name="format"] input[value="'+format+'"]');

            await page.click('[name="format"] input[value="'+format+'"]');
            await page.click('[name="filter_limit_all"] input[value="no"]');
            await page.click('input[name="option_format_metrics"]');
            await page.evaluate(function(){
                $('[name=filter_limit] input').val(100).trigger('change');
            });
            await page.waitForTimeout(250);

            var url = await page.evaluate(function() {
                return $('#reportExport a.btn').attr('href');
            });
            var pageContents = await page.downloadUrl(url);

            expect.fileMatchesContent('RowEvolution_exported.' + format.toLowerCase() + '.txt', pageContents);
        });
    }

    var formats = ['CSV', 'TSV', 'XML', 'JSON'];
    formats.forEach(normalReportTest);
    formats.forEach(evolutionReportTest);
    formats.forEach(rowEvolutionReportTest);
});
