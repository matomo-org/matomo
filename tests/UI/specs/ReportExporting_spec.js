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

    function setWindowOpen(page) {
        page.evaluate(function(){
            window.downloadUrl = null;
            window.openBackup = window.open;

            window.open = function(url) {
                window.downloadUrl = url;
                return false;
            }
        })
    }

    function unsetWindowOpen(page) {
        page.evaluate(function() {
            window.open = window.openBackup;
        });
    }

    var baseUrl = "?module=Widgetize&action=iframe&idSite=1&period=year&date=2012-08-09&isFooterExpandedInDashboard=1",
        referrersGetWebsitesUrl = baseUrl + "&moduleToWidgetize=Referrers&actionToWidgetize=getWebsites&filter_limit=5",
        visitsSummaryGetUrl = baseUrl + "&moduleToWidgetize=VisitsSummary&actionToWidgetize=get&forceView=1&viewDataTable=graphEvolution";

    function normalReportTest(format) {
        it("should export a normal report correctly when the " + format + " export is chosen", function (done) {
            expect.file('Referrers.getWebsites_exported.' + format.toLowerCase() + '.txt').to.be.pageContents(function (page) {

                if (page.getCurrentUrl() != referrersGetWebsitesUrl) {
                    page.load(referrersGetWebsitesUrl);
                    page.click('.activateExportSelection');
                    page.evaluate(function(){
                        $('<a>').attr('id', 'downloadLink').appendTo('body');
                    });
                }

                setWindowOpen(page);

                page.click('[name=format] input[value='+format+'] + label', 100);
                page.click('[name=filter_limit_all] input[value=no] + label', 100);
                page.evaluate(function(){
                    $('[name=filter_limit] input').val(100).trigger('change');
                });
                page.click('[ng-click="processExport()"]', 100);

                unsetWindowOpen(page);

                page.execCallback(function () {
                    page.webpage.evaluate(function () {
                        $('a#downloadLink').attr('href', window.downloadUrl);
                    });
                });

                page.downloadLink('a#downloadLink');
            }, done);
        });
    }

    function evolutionReportTest(format) {
        it("should export an evolution graph report correctly when the " + format + " export is chosen", function (done) {
            expect.file('VisitsSummary.get_exported.' + format.toLowerCase() + '.txt').to.be.pageContents(function (page) {
                if (page.getCurrentUrl() != visitsSummaryGetUrl) {
                    page.load(visitsSummaryGetUrl);
                    page.click('.activateExportSelection');
                    page.evaluate(function(){
                        $('<a>').attr('id', 'downloadLink').appendTo('body');
                    });
                }

                setWindowOpen(page);

                page.click('[name=format] input[value='+format+'] + label', 100);
                page.click('[name=filter_limit_all] input[value=no] + label', 100);
                page.evaluate(function(){
                    $('[name=filter_limit] input').val(100).trigger('change');
                });
                page.click('[ng-click="processExport()"]', 100);

                unsetWindowOpen(page);

                page.execCallback(function () {
                    page.webpage.evaluate(function () {
                        $('a#downloadLink').attr('href', window.downloadUrl);
                    });
                });

                page.downloadLink('a#downloadLink');
            }, done);
        });
    }

    function rowEvolutionReportTest(format) {
        it("should export an row evolution graph report correctly when the " + format + " export link is clicked", function (done) {
            expect.file('RowEvolution_exported.' + format.toLowerCase() + '.txt').to.be.pageContents(function (page) {
                if (!page.getCurrentUrl() || page.getCurrentUrl().indexOf('popover') == -1) {
                    page.load(referrersGetWebsitesUrl);
                    page.mouseMove('tbody tr:first-child');
                    page.mouseMove('a.actionRowEvolution:visible'); // necessary to get popover to display
                    page.click('a.actionRowEvolution:visible');
                    page.click('.ui-dialog .activateExportSelection');
                    page.evaluate(function(){
                        $('<a>').attr('id', 'downloadLink').appendTo('body');
                    });
                }

                setWindowOpen(page);

                page.click('[name=format] input[value='+format+'] + label', 100);
                page.click('[name=filter_limit_all] input[value=no] + label', 100);
                page.evaluate(function(){
                    $('[name=filter_limit] input').val(100).trigger('change');
                });
                page.click('[ng-click="processExport()"]', 100);

                unsetWindowOpen(page);

                page.execCallback(function () {
                    page.webpage.evaluate(function () {
                        $('a#downloadLink').attr('href', window.downloadUrl);
                    });
                });

                page.downloadLink('a#downloadLink');
            }, done);
        });
    }

    var formats = ['CSV', 'TSV', 'XML', 'JSON', 'PHP'];
    formats.forEach(normalReportTest);
    formats.forEach(evolutionReportTest);
    formats.forEach(rowEvolutionReportTest);
});