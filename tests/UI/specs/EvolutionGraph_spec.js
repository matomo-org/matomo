/*!
 * Piwik - free/libre analytics platform
 *
 * evolution graph screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("EvolutionGraph", function () {
    this.timeout(0);

    var url = "?module=Widgetize&action=iframe&idSite=1&period=day&date=2012-01-31&evolution_day_last_n=30"
            + "&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry&viewDataTable=graphEvolution"
            + "&isFooterExpandedInDashboard=1";

    before(function (done) {
        testEnvironment.callApi("Annotations.deleteAll", {idSite: 3}, done);
    });

    it("should load correctly", function (done) {
        expect.screenshot('initial').to.be.capture(function (page) {
            page.load(url);
        }, done);
    });

    it("should show percent metrics like bounce rate correctly", function (done) {
        expect.screenshot('bounce_rate').to.be.capture(function (page) {
            page.load(url + "&columns=nb_visits,bounce_rate,avg_time_on_site&filter_add_columns_when_show_all_columns=0");
        }, done);
    });

    it("should show only one series when a label is specified", function (done) {
        expect.screenshot('one_series').to.be.capture(function (page) {
            page.load(url + "&label=Canada");
        }, done);
    });

    it("should display the metric picker on hover of metric picker icon", function (done) {
        expect.screenshot('metric_picker_shown').to.be.capture(function (page) {
            page.mouseMove('.jqplot-seriespicker');
        }, done);
    });

    it("should show multiple metrics when another metric picked", function (done) {
        expect.screenshot('two_metrics').to.be.capture(function (page) {
            page.click('.jqplot-seriespicker-popover input:not(:checked)');
        }, done);
    });

    it("should show graph as image when export as image icon clicked", function (done) {
        expect.screenshot('export_image').to.be.capture(function (page) {
            page.click('#dataTableFooterExportAsImageIcon>a');
        }, done);
    });

    it("should display more periods when limit selection changed", function (done) {
        expect.screenshot('limit_changed').to.be.capture(function (page) {
            page.click('.limitSelection');
            page.evaluate(function () {
                $('.limitSelection ul li[value=60]').click();
            });
        }, done);
    });

    // annotations tests
    it("should show annotations when annotation icon on x-axis clicked", function (done) {
        expect.screenshot('annotations_single_period').to.be.capture(function (page) {
            page.evaluate(function () {
                $('.limitSelection ul li[value=30]').click(); // change limit back
            });

            page.click('.evolution-annotations>span[data-count!=0]', 3000);
        }, done);
    });

    it("should show all annotations when annotations footer link clicked", function (done) {
        expect.screenshot('annotations_all').to.be.capture(function (page) {
            page.click('.annotationView', 3000);
        }, done);
    });

    it("should show no annotations message when no annotations for site", function (done) {
        expect.screenshot('annotations_none').to.be.capture(function (page) {
            page.load(page.getCurrentUrl().replace(/idSite=[^&]*/, "idSite=3") + "&columns=nb_visits");
            page.click('.annotationView', 3000);
        }, done);
    });

    it("should show add annotation form when create annotation clicked", function (done) {
        expect.screenshot('new_annotation_form').to.be.capture(function (page) {
            page.click('.add-annotation');
            page.click('.annotation-period-edit>a');
            page.evaluate(function () {
                $('.datepicker').datepicker("setDate", new Date(2012,0,02) );
                $(".ui-datepicker-current-day").trigger("click"); // this triggers onSelect event which sets .annotation-period-edit>a
            });
        }, done);
    });

    it("should add new annotation when create annotation submitted", function (done) {
        expect.screenshot('new_annotation_submit').to.be.capture(function (page) {
            page.sendKeys('.new-annotation-edit', 'new annotation');
            page.click('.annotation-period-edit>a');
            page.evaluate(function () {
                $('.ui-datepicker-calendar td a:contains(15)').click();
            });
            page.click('.annotation-list-range');
            page.click('input.new-annotation-save', 3000);
        }, done);
    });

    it("should star annotation when star image clicked", function (done) {
        expect.screenshot('annotation_starred').to.be.capture(function (page) {
            page.click('.annotation-star');
        }, done);
    });

    it("should show edit annotation form", function (done) {
        expect.screenshot('annotation_edit_form').to.be.capture(function (page) {
            page.click('.edit-annotation');
        }, done);
    });

    it("should edit annotation when edit form submitted", function (done) {
        expect.screenshot('annotation_edit_submit').to.be.capture(function (page) {
            page.sendKeys('.annotation-edit', 'edited annotation');
            page.click('.annotation-period-edit>a');
            page.evaluate(function () {
                $('.ui-datepicker-calendar td a:contains(16)').click();
            });
            page.click('.annotation-list-range');
            page.click('input.annotation-save', 3000);
        }, done);
    });

    it("should delete annotation when delete link clicked", function (done) {
        expect.screenshot('annotation_delete').to.be.capture(function (page) {
            page.click('.edit-annotation');
            page.click('.delete-annotation');
        }, done);
    });

    it("should cutout two labels so all can fit on screen", function (done) {
        expect.screenshot('label_ticks_cutout').to.be.capture(function (page) {
            page.setViewportSize(320,320);
            page.load(url.replace(/idSite=[^&]*/, "idSite=3") + "&columns=nb_visits");
        }, done);
    });
});