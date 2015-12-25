/*!
 * Piwik - free/libre analytics platform
 *
 * ViewDataTable screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ViewDataTableTest", function () { // TODO: should remove Test suffix from images instead of naming suites ...Test
    this.timeout(0);

    // TODO: rename screenshot files, remove numbers
    var url = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
            + "actionToWidgetize=getKeywords&viewDataTable=table&filter_limit=5&isFooterExpandedInDashboard=1";

    it("should load correctly", function (done) {
        expect.screenshot("0_initial").to.be.capture(function (page) {
            page.load(url);
        }, done);
    });

    it("should load all columns when all columns clicked", function (done) {
        expect.screenshot("1_all_columns").to.be.capture(function (page) {
            page.click('.tableIcon[data-footer-icon-id=tableAllColumns]');
        }, done);
    });

    it("should sort a column in descending order when column clicked initially", function (done) {
        expect.screenshot("2_column_sorted_desc").to.be.capture(function (page) {
            page.click('th#avg_time_on_site');
        }, done);
    });

    it("should sort a column in ascending order when column clicked second time", function (done) {
        expect.screenshot("3_column_sorted_asc").to.be.capture(function (page) {
            page.click('th#avg_time_on_site');
        }, done);
    });

    it("should exclude low population rows when low population clicked", function (done) {
        expect.screenshot("4_exclude_low_population").to.be.capture(function (page) {
            page.mouseMove('.tableConfiguration');
            page.click('.dataTableExcludeLowPopulation');
        }, done);
    });

    it("should load goals table when goals footer icon clicked", function (done) {
        expect.screenshot("5_goals").to.be.capture(function (page) {
            page.click('.tableIcon[data-footer-icon-id=tableGoals]');
        }, done);
    });

    it("should load bar graph when bar graph footer icon clicked", function (done) {
        expect.screenshot('6_bar_graph').to.be.capture(function (page) {
            page.mouseMove('.tableIconsGroup:nth-child(3)');
            page.click('.tableIcon[data-footer-icon-id=graphVerticalBar]');
        }, done);
    });

    it("should load pie graph when pie graph footer icon clicked", function (done) {
        expect.screenshot('7_pie_graph').to.be.capture(function (page) {
            page.mouseMove('.tableIconsGroup:nth-child(2)');
            page.click('.tableIcon[data-footer-icon-id=graphPie]');
        }, done);
    });

    it("should load a tag cloud when tag cloud footer icon clicked", function (done) {
        expect.screenshot('8_tag_cloud').to.be.capture(function (page) {
            page.mouseMove('.tableIconsGroup:nth-child(3)');
            page.click('.tableIcon[data-footer-icon-id=cloud]');
        }, done);
    });

    it("should load normal table when normal table footer icon clicked", function (done) {
        expect.screenshot('9_normal_table').to.be.capture(function (page) {
            page.click('.tableIcon[data-footer-icon-id=table]');
            page.mouseMove({x: -10, y: -10}); // mae sure no row is highlighted
        }, done);
    });

    it("should show the limit selector when the limit selector is clicked", function (done) {
        expect.screenshot('limit_selector_open').to.be.capture(function (page) {
            page.click('.limitSelection');
        }, done);
    });

    it("should change the number of rows when new limit selected", function (done) {
        expect.screenshot('10_change_limit').to.be.capture(function (page) {
            page.click('.limitSelection ul li[value=10]');
        }, done);
    });

    it("should flatten the table when the flatten link is clicked", function (done) {
        expect.screenshot('11_flattened').to.be.capture(function (page) {
            page.mouseMove('.tableConfiguration');
            page.click('.dataTableFlatten');
        }, done);
    });

    it("should show aggregate rows when the aggregate rows option is clicked", function (done) {
        expect.screenshot('12_aggregate_shown').to.be.capture(function (page) {
            page.mouseMove('.tableConfiguration');
            page.click('.dataTableIncludeAggregateRows');
        }, done);
    });

    it("should make the report hierarchical when the flatten link is clicked again", function (done) {
        expect.screenshot('13_make_hierarchical').to.be.capture(function (page) {
            page.mouseMove('.tableConfiguration');
            page.click('.dataTableFlatten');
        }, done);
    });

    it("should show the visits percent when hovering over a column", function (done) {
        expect.screenshot('14_visits_percent').to.be.capture(function (page) {
            page.mouseMove('td.column:not(.label)');
        }, done);
    });

    it("should load subtables correctly when row clicked", function (done) {
        expect.screenshot('subtables_loaded').to.be.capture(function (page) {
            page.click('tr.subDataTable:first');
            page.click('tr.subDataTable:eq(2)');
        }, done);
    });

    it("should search the table when a search string is entered and the search button clicked", function (done) {
        expect.screenshot('15_search').to.be.capture(function (page) {
            page.sendKeys('.dataTableSearchPattern>input[type=text]', 'term');
            page.click('.dataTableSearchPattern>input[type=submit]');
        }, done);
    });

    it("should display the export options when clicking the export icon", function (done) {
        expect.screenshot('export_options').to.be.capture(function (page) {
            page.click('.exportToFormatIcons', 2000);
        }, done);
    });

    it("should display a related report when related report link is clicked", function (done) {
        expect.screenshot('related_report_click').to.be.capture(function (page) {
            var newReportUrl = url.replace("=Referrers", "=DevicesDetection").replace("=getKeywords", "=getOsFamilies");

            page.load(newReportUrl);
            page.click('.datatableRelatedReports li>span:visible');
        }, done);
    });
});