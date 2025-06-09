/*!
 * Matomo - free/libre analytics platform
 *
 * evolution graph screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("EvolutionGraph", function () {
    const url = "?module=Widgetize&action=iframe&idSite=1&period=day&date=2012-01-31&evolution_day_last_n=30"
              + "&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry&viewDataTable=graphEvolution"
              + "&isFooterExpandedInDashboard=1";

    before(function () {
        return testEnvironment.callApi("Annotations.deleteAll", {idSite: 3});
    });

    it("should load correctly", async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('initial');
    });

    it("should show percent metrics like bounce rate correctly", async function () {
        await page.goto(url + "&columns=nb_visits,bounce_rate,avg_time_on_site&filter_add_columns_when_show_all_columns=0");
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('bounce_rate');
    });

    it("should show only one series when a label is specified", async function () {
        await page.goto(url + "&label=Canada");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('one_series');
    });

    it("should display the metric picker on hover of metric picker icon", async function () {
        await page.hover('.jqplot-seriespicker');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('metric_picker_shown');
    });

    it("should show multiple metrics when another metric picked", async function () {
        await page.waitForSelector('.jqplot-seriespicker-popover input');
        const element = await page.jQuery('.jqplot-seriespicker-popover input:not(:checked):first');
        await element.click();
        await page.waitForNetworkIdle();
        await page.waitForTimeout(250);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('two_metrics');
    });

    it("should show graph as image when export as image icon clicked", async function () {
        await page.click('#dataTableFooterExportAsImageIcon');
        await page.waitForNetworkIdle();

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('export_image');
    });

    it("should display more periods when limit selection changed", async function () {
        const element = await page.jQuery('.ui-dialog .ui-widget-header button:visible');
        await element.click();

        await page.click('.limitSelection input');
        await page.evaluate(function () {
            $('.limitSelection ul li:contains(60) span').click();
        });
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('limit_changed');
    });

    // annotations tests
    it("should show annotations when annotation icon on x-axis clicked", async function () {
        await page.click('.limitSelection input');
        await page.evaluate(function () {
            $('.limitSelection ul li:contains(30) span').click(); // change limit back
        });
        await page.waitForNetworkIdle();

        const element = await page.jQuery('.evolution-annotations>span[data-count!=0]');
        await element.click();
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotations_single_period');
    });

    it("should show all annotations when annotations footer link clicked", async function () { // TODO: fails
        await page.click('.annotationView');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotations_all');
    });

    it("should show no annotations message when no annotations for site", async function () {
        await page.goto(page.url().replace(/idSite=[^&]*/, "idSite=3") + "&columns=nb_visits");
        await page.click('.annotationView');
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotations_none');
    });

    it("should show add annotation form when create annotation clicked", async function () {
        await page.click('.add-annotation');
        await page.click('.annotation-period-edit>a');
        await page.evaluate(function () {
            $('.datepicker').datepicker("setDate", new Date(2012,0,2) );
            $(".ui-datepicker-current-day").trigger("click"); // this triggers onSelect event which sets .annotation-period-edit>a
        });
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('new_annotation_form');
    });

    it("should add new annotation when create annotation submitted", async function () {
        await page.focus('.new-annotation-edit');
        await page.keyboard.type('new annotation');
        await page.click('.annotation-period-edit>a');
        await page.evaluate(function () {
            $('.ui-datepicker-calendar td a:contains(15)').click();
        });
        await page.waitForNetworkIdle();
        await page.click('.annotation-list-range');
        await page.click('input.new-annotation-save');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('new_annotation_submit');
    });

    it("should star annotation when star image clicked", async function () {
        await page.click('.annotation-star');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotation_starred');
    });

    it("should show edit annotation form", async function () {
        await page.click('.edit-annotation');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotation_edit_form');
    });

    it("should edit annotation when edit form submitted", async function () {
        await page.focus('.annotation-edit');
        await page.keyboard.type('edited annotation');
        await page.click('.annotation-period-edit>a');
        await page.evaluate(function () {
            $('.annotation-meta .ui-datepicker-calendar td a:contains(16)').click();
        });
        await page.waitForNetworkIdle();
        await page.click('.annotation-list-range');
        await page.click('input.annotation-save');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotation_edit_submit');
    });

    it("should delete annotation when delete link clicked", async function () {
        await page.click('.edit-annotation');
        await page.waitForFunction("$('.delete-annotation:visible').length > 0");
        await page.evaluate(function () {
            $('.delete-annotation').click();
        });
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotations_none');
    });

    it("should cutout two labels so all can fit on screen", async function () {
        await page.webpage.setViewport({ width: 320, height: 320 });
        await page.goto(url.replace(/idSite=[^&]*/, "idSite=3") + "&columns=nb_visits");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('label_ticks_cutout');
    });

    it("should show available periods", async function () {
        await page.webpage.setViewport({
            width: 1350,
            height: 768,
        });
        await page.reload();
        await page.waitForNetworkIdle();
        await (await page.jQuery('.activatePeriodsSelection:last')).click();

        await page.mouse.move(-10, -10);
        await page.waitForTimeout(500); // wait for animation

        expect(await page.screenshot({ fullPage: true })).to.matchImage('periods_list');
    });

    it("should be possible to change period", async function () {
        await (await page.jQuery('[data-period=month]:last')).click();
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('periods_selected');
    });

    it("should not show add annotation form for user with view access", async function () {
        testEnvironment.idSitesViewAccess = [1];
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();

        await page.goto(url);
        await page.waitForNetworkIdle();
        await page.click('.annotationView');
        await page.waitForNetworkIdle();

        // check that add annotation link is not shown
        const element = await page.$('.add-annotation');
        expect(element).to.be.not.ok;
    });
});
