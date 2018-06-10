/*!
 * Piwik - free/libre analytics platform
 *
 * Export link screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ReportExporting", function () {
    this.timeout(0);

    var baseUrl = "?module=Widgetize&action=iframe&idSite=1&period=year&date=2012-08-09&isFooterExpandedInDashboard=1",
        referrersGetWebsitesUrl = baseUrl + "&moduleToWidgetize=Referrers&actionToWidgetize=getWebsites&filter_limit=5",
        visitsSummaryGetUrl = baseUrl + "&moduleToWidgetize=VisitsSummary&actionToWidgetize=get&forceView=1&viewDataTable=graphEvolution";

    function normalReportTest(format) {
        it("should export a normal report correctly when the " + format + " export is chosen", async function () {
            expect.file('Referrers.getWebsites_exported.' + format.toLowerCase() + '.txt').to.be.pageContents(function (page) {

                if (page.url() != referrersGetWebsitesUrl) {
                    page.goto(referrersGetWebsitesUrl);
                    page.click('.activateExportSelection');
                }


                page.click('[name=format] input[value='+format+'] + label', 100);
                page.click('[name=filter_limit_all] input[value=no] + label', 100);
                page.evaluate(function(){
                    $('[name=filter_limit] input').val(100).trigger('change');
                });

                page.downloadLink('#reportExport a.btn');
            }, done);
        });
    }

    function evolutionReportTest(format) {
        it("should export an evolution graph report correctly when the " + format + " export is chosen", async function () {
            expect.file('VisitsSummary.get_exported.' + format.toLowerCase() + '.txt').to.be.pageContents(function (page) {
                if (page.url() != visitsSummaryGetUrl) {
                    page.goto(visitsSummaryGetUrl);
                    page.click('.activateExportSelection');
                }

                page.click('[name=format] input[value='+format+'] + label', 100);
                page.click('[name=filter_limit_all] input[value=no] + label', 100);
                page.evaluate(function(){
                    $('[name=filter_limit] input').val(100).trigger('change');
                });

                page.downloadLink('#reportExport a.btn');
            }, done);
        });
    }

    function rowEvolutionReportTest(format) {
        it("should export an row evolution graph report correctly when the " + format + " export link is clicked", async function () {
            expect.file('RowEvolution_exported.' + format.toLowerCase() + '.txt').to.be.pageContents(function (page) {
                if (!page.url() || page.url().indexOf('popover') == -1) {
                    page.goto(referrersGetWebsitesUrl);
                    page.mouseMove('tbody tr:first-child');
                    page.mouseMove('a.actionRowEvolution:visible'); // necessary to get popover to display
                    page.click('a.actionRowEvolution:visible');
                    page.click('.ui-dialog .activateExportSelection');
                }

                page.click('[name=format] input[value='+format+'] + label', 100);
                page.click('[name=filter_limit_all] input[value=no] + label', 100);
                page.evaluate(function(){
                    $('[name=filter_limit] input').val(100).trigger('change');
                });

                page.downloadLink('#reportExport a.btn');
            }, done);
        });
    }

    var formats = ['CSV', 'TSV', 'XML', 'JSON', 'PHP'];
    formats.forEach(normalReportTest);
    formats.forEach(evolutionReportTest);
    formats.forEach(rowEvolutionReportTest);
});