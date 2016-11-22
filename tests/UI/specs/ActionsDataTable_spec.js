/*!
 * Piwik - free/libre analytics platform
 *
 * ActionsDataTable screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ActionsDataTable", function () {
    this.timeout(0);

    var url = "?module=Widgetize&action=iframe&idSite=1&period=year&date=2012-08-09&moduleToWidgetize=Actions&actionToWidgetize=getPageUrls&isFooterExpandedInDashboard=1";

    it("should load correctly", function (done) {
        expect.screenshot('initial').to.be.capture(function (page) {
            page.load(url);
        }, done);
    });

    it("should sort column correctly when column header clicked", function (done) {
        expect.screenshot('column_sorted').to.be.capture(function (page) {
            page.click('th#avg_time_on_page');
        }, done);
    });

    it("should load subtables correctly when row clicked", function (done) {
        expect.screenshot('subtables_loaded').to.be.capture(function (page) {
            page.click('tr.subDataTable:first');
            page.click('tr.subDataTable:eq(2)');
        }, done);
    });

    it("should show configuration options", function (done) {
        expect.screenshot('configuration_options').to.be.captureSelector('.tableConfiguration', function (page) {
            page.click('.dropdownConfigureIcon');
        }, done);
    });

    it("should flatten table when flatten link clicked", function (done) {
        expect.screenshot('flattened').to.be.capture(function (page) {
            page.click('.dataTableFlatten');
        }, done);
    });

    it("should exclude low population rows when exclude low population link clicked", function (done) {
        expect.screenshot('exclude_low_population').to.be.capture(function (page) {
            page.click('.dropdownConfigureIcon');
            page.click('.dataTableExcludeLowPopulation');
        }, done);
    });

    it("should load normal view when switch to view hierarchical view link is clicked", function (done) {
        expect.screenshot('unflattened').to.be.capture(function (page) {
            page.click('.dropdownConfigureIcon span');
            page.click('.dataTableFlatten');
        }, done);
    });

    it("should display pageview percentages when hovering over pageviews column", function (done) {
        this.retries(3);
        expect.screenshot('pageview_percentages').to.be.capture(function (page) {
            page.mouseMove('tr:contains("thankyou") td.column:eq(1)');
            page.wait(1000);
        }, done);
    });

    it("should generate a proper title for the visitor log segmented by the current row", function (done) {
        expect.screenshot('segmented_visitor_log_hover').to.be.capture(function (page) {
            var row = 'tr:contains("thankyou") ';
            page.mouseMove(row + 'td.column:first');
            page.mouseMove(row + 'td.label .actionSegmentVisitorLog');
            page.wait(1000);
        }, done);
    });

    it("should open the visitor log segmented by the current row", function (done) {
        expect.screenshot('segmented_visitor_log').to.be.capture(function (page) {
            page.click('tr:contains("thankyou") td.label .actionSegmentVisitorLog');
        }, done);
    });

    it("should display unique pageview percentages when hovering over unique pageviews column", function (done) {
        this.retries(3);
        expect.screenshot('unique_pageview_percentages').to.be.capture(function (page) {
            page.click('.ui-widget .ui-dialog-titlebar-close');

            page.mouseMove('tr:contains("thankyou") td.column:eq(2)');
            page.wait(1000);
        }, done);
    });

    it("should show the search when clicking on the search icon", function (done) {
        expect.screenshot('search_visible').to.be.capture(function (page) {
            page.click('.dataTableAction.searchAction');
        }, done);
    });

    it("should search through table when search input entered and search button clicked and input should be visible", function (done) {
        expect.screenshot('search').to.be.capture(function (page) {
            page.sendKeys('.searchAction .dataTableSearchInput', 'i');
            page.click('.searchAction .icon-search');
        }, done);
    });

    it("should close search when clicking on the x icon", function (done) {
        expect.screenshot('search_closed').to.be.capture(function (page) {
            page.click('.searchAction .icon-close');
        }, done);
    });

    it("should automatically expand subtables if it contains only one folder", function (done) {
        expect.screenshot('auto_expand').to.be.capture(function (page) {
            page.load(url + '&viewDataTable=table');
            page.click('tr .value:contains("blog")');
            page.click('tr .value:contains("2012")');
        }, done);
    });
});
