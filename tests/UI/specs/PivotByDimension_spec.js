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

    var eventsUrl = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09#?idSite=1&period=year&date=2012-08-09&category=General_Actions&subcategory=Events_Events",
        actionsUrl = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09#?idSite=1&period=year&date=2012-08-09&category=General_Actions&subcategory=General_Pages",
        cvarsUrl = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09#?idSite=1&period=year&date=2012-08-09&category=General_Visitors&subcategory=CustomVariables_CustomVariables"
        ;

    function showDataTableFooter(page)
    {
        page.mouseMove('.dataTableFeatures');
    }

    it("should pivot a report correctly when the pivot cog option is selected", async function () {
        expect.screenshot('pivoted').to.be.captureSelector('.dataTable,.expandDataTableFooterDrawer', function (page) {
            page.goto(eventsUrl);
            page.click('.dimension:contains(Event Names)');
            showDataTableFooter(page);
            page.evaluate(function(){
                $('.dropdownConfigureIcon').click();
                $('.dataTablePivotBySubtable').click();
            }, 2000);
            page.mouseMove({x: -15, y: -15}); // make sure nothing is highlighted
        }, done);
    });

    it("should not display the pivot option on actions reports", async function () {
        expect.page(actionsUrl).not.contains('.dataTablePivotBySubtable', function () {}, done);
    });

    it("should display the pivot option on reports that set a custom columns_to_display", async function () {
        expect.screenshot('pivoted_columns_report').to.be.captureSelector('.dataTable,.expandDataTableFooterDrawer', function (page) {
            page.goto(cvarsUrl);
            showDataTableFooter(page);
            page.click('.dropdownConfigureIcon');
            page.click('.dataTablePivotBySubtable');
            page.mouseMove({x: -15, y: -15}); // make sure nothing is highlighted
        }, done);
    });
});