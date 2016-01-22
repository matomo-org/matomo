/*!
 * Piwik - free/libre analytics platform
 *
 * PivotByDimension UI tests
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PivotByDimension", function () {
    this.timeout(0);

    var eventsUrl = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09#/idSite=1&period=year&date=2012-08-09&module=Events&action=index",
        actionsUrl = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09#/idSite=1&period=year&date=2012-08-09&module=Actions&action=menuGetPageUrls",
        cvarsUrl = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09#/idSite=1&period=year&date=2012-08-09&module=CustomVariables&action=menuGetCustomVariables"
        ;

    it("should pivot a report correctly when the pivot cog option is selected", function (done) {
        expect.screenshot('pivoted').to.be.captureSelector('.dataTable,.expandDataTableFooterDrawer', function (page) {
            page.load(eventsUrl);
            page.click('.dimension:contains(Event Names)');
            page.click('.expandDataTableFooterDrawer');
            page.mouseMove('.tableConfiguration');
            page.click('.dataTablePivotBySubtable');
            page.mouseMove({x: -15, y: -15}); // make sure nothing is highlighted
        }, done);
    });

    it("should not display the pivot option on actions reports", function (done) {
        expect.page(actionsUrl).not.contains('.dataTablePivotBySubtable', function () {}, done);
    });

    it("should display the pivot option on reports that set a custom columns_to_display", function (done) {
        expect.screenshot('pivoted_columns_report').to.be.captureSelector('.dataTable,.expandDataTableFooterDrawer', function (page) {
            page.load(cvarsUrl);
            page.click('.expandDataTableFooterDrawer');
            page.mouseMove('.tableConfiguration');
            page.click('.dataTablePivotBySubtable');
            page.mouseMove({x: -15, y: -15}); // make sure nothing is highlighted
        }, done);
    });
});