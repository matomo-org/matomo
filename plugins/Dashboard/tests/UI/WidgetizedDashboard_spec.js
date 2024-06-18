/*!
 * Matomo - free/libre analytics platform
 *
 * Dashboard screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("WidgetizedDashboard", function () {
    this.timeout(0);

    var url = "?module=Widgetize&action=iframe&idSite=1&period=year&date=2012-08-09&moduleToWidgetize=Dashboard&"
            + "actionToWidgetize=index&idDashboard=1";

    var removeAllExtraDashboards = async function() {
        var dashboards = await testEnvironment.callController("Dashboard.getAllDashboards", {});
        dashboards = (dashboards || []).filter(function (dash) {
            return parseInt(dash.iddashboard) > 5;
        });

        var removeDashboard = async function (i) {
            if (i >= dashboards.length) {
                return;
            }

            console.log("Removing dashboard ID = " + dashboards[i].iddashboard);
            await testEnvironment.callApi("Dashboard.removeDashboard", {idDashboard: dashboards[i].iddashboard});
            await removeDashboard(i + 1);
        };

        await removeDashboard(0);
    };

    var clickDashboardMenuItem = async function (item) {
        await page.click('.dashboard-manager .title');
        await page.waitForTimeout(50);
        await page.click('li[data-action="' + item + '"]');
    }

    var setup = async function() {
        // make sure live widget doesn't refresh constantly for UI tests
        testEnvironment.overrideConfig('General', 'live_widget_refresh_after_seconds', 1000000);
        // ensure tour widget always shows the same state
        testEnvironment.completeAllChallenges = 1;
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();

        // save empty layout for dashboard ID = 1
        var layout = [
            [
                {
                    uniqueId: "widgetVisitsSummarygetEvolutionGraphforceView1viewDataTablegraphEvolution",
                    parameters: {module: "VisitsSummary", action: "getEvolutionGraph", columns: "nb_visits"}
                }
            ],
            [],
            []
        ];

        await testEnvironment.callController("Dashboard.saveLayout", {name: 'D4', layout: JSON.stringify(layout), idDashboard: 1, idSite: 2});
        await testEnvironment.callController("Dashboard.saveLayoutAsDefault", {layout: 0});
        await removeAllExtraDashboards();
    };

    before(setup);
    after(setup);

    it("should load correctly", async function() {
        await page.goto(url);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('loaded');
    });

    it("should move a widget when widget is drag & dropped", async function() {
        var widget = await page.$('.widgetTop');
        await widget.hover();
        await page.mouse.down();

        var col2 = await page.jQuery('#dashboardWidgetsArea > .col:eq(2)');
        await col2.hover();
        await page.mouse.up();
        await page.waitForNetworkIdle();
        await page.waitForTimeout(100);
        await page.mouse.move(-10, -10);

        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('widget_move');
    });

    it("should refresh widget when widget refresh icon clicked", async function() {
        var widget = await page.$('.widgetTop');
        await widget.hover();

        await page.click('.button#refresh');
        await page.mouse.move(-10, -10);

        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('widget_refresh');
    });

    it("should minimise widget when widget minimise icon clicked", async function() {
        var widget = await page.$('.widgetTop');
        await widget.hover();
        await page.click('.button#minimise');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('widget_minimised');
    });

    it("should unminimise widget when widget maximise icon is clicked after being minimised", async function() {
        var widget = await page.$('.widgetTop');
        await widget.hover();
        await page.click('.button#maximise');
        await page.mouse.move(-10, -10);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('widget_unminimise');
    });

    it("should maximise widget when widget maximise icon is clicked", async function() {
        var widget = await page.$('.widgetTop');
        await widget.hover();
        await page.click('.button#maximise');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('widget_maximise');
    });

    it("should close maximise dialog when minimise icon is clicked", async function() {
        var widget = await page.$('.widgetTop');
        await widget.hover();
        await page.click('.button#minimise');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('widget_unmaximise');
    });

    it("should add a widget when a widget is selected in the dashboard manager", async function() {
        await page.click('.dashboard-manager .title');

        await page.waitForSelector('.widgetpreview-categorylist>li');

        var live = await page.jQuery('.widgetpreview-categorylist>li:contains(Goals)'); // have to mouse move twice... otherwise Live! will just be highlighted
        await live.hover();
        await live.click();

        var behaviour = await page.jQuery('.widgetpreview-categorylist>li:contains(Behaviour):first');
        await behaviour.hover();
        await behaviour.click();

        var pages = await page.jQuery('.widgetpreview-widgetlist>li:contains(Pages):first');
        await pages.hover();
        await pages.click();

        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('widget_add_widget');
    });

    it("should open row evolution", async function() {
        var row = await page.jQuery('#dashboardWidgetsArea .dataTable tbody td:contains(thankyou)');
        await row.hover();
        var icon = await page.waitForSelector('#dashboardWidgetsArea .dataTable tbody a.actionRowEvolution');
        await icon.click();
        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();
        var dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('rowevolution');
    });

    it("should remove widget when remove widget icon is clicked", async function() {
        await page.click('.ui-dialog-titlebar-close'); // close row evolution

        var widget = '[id="widgetActionsgetPageUrls"]';

        var titlebar = await page.$(widget + ' .widgetTop');
        await titlebar.hover();

        var icon = await page.$(widget + ' .button#close');
        await icon.click();

        var button = await page.jQuery('.modal.open .modal-footer a:contains(Yes)');
        await button.click();

        // wait until widget was removed and modal closed
        await page.waitForFunction((widget) => $(widget).length === 0);
        await page.waitForTimeout(250);

        // check that one widget remains
        const widgetsCount = await page.evaluate(() => $('#dashboardWidgetsArea .widget').length);
        expect(widgetsCount).to.equal(1);
    });

    it("should change dashboard layout when new layout is selected", async function() {
        await clickDashboardMenuItem('showChangeDashboardLayoutDialog');
        await (await page.waitForSelector('.modal.open div[layout="50-50"]')).click();
        var button = await page.jQuery('.modal.open .modal-footer a:contains(Save)');
        await button.click();
        await page.mouse.move(-10, -10);
        await page.waitForTimeout(250); // animation
        await page.evaluate(() => $('.evolution-annotations').css('display','none'));
        expect(await page.screenshot({ fullPage: true })).to.matchImage('change_layout');
    });

    it("should rename dashboard when dashboard rename process completed", async function() {
        await clickDashboardMenuItem('renameDashboard');
        await page.waitForSelector('.modal.open');

        await page.evaluate(() => $('#newDashboardName').val('newname'));
        await page.waitForTimeout(250);
        var button = await page.jQuery('.modal.open .modal-footer a:contains(Save)');
        await button.click();
        await page.waitForFunction(() => $('#Dashboard_embeddedIndex_1').text().trim() === 'newname');
        await page.waitForTimeout(100); // wait for javascript to finish everything
    });

    it("should copy dashboard successfully when copy dashboard process completed", async function() {
        await clickDashboardMenuItem('copyDashboardToUser');
        await page.waitForSelector('.modal.open');

        await page.evaluate(function () {
            $('#copyDashboardName').val('');
        });
        await page.type('#copyDashboardName', 'new <dash> 💩');
        await page.waitForSelector('#copyDashboardUser [value="superUserLogin"]');
        await page.select('#copyDashboardUser', 'superUserLogin');
        var button = await page.jQuery('.modal.open .modal-footer a:contains(Ok)');
        await button.click();
        await page.waitForFunction("$('.ui-confirm :contains(\"Current dashboard successfully copied to selected user.\").length > 0')");

        await page.goto(url.replace("idDashboard=1", "idDashboard=6"));
        await page.waitForNetworkIdle();
        await page.waitForSelector('.widget');
        await page.waitForNetworkIdle();

        const dashboardCount = await page.evaluate(() => $('#Dashboard ul li').length);
        expect(dashboardCount).to.equal(6);

        const widgetsCount = await page.evaluate(() => $('#dashboardWidgetsArea .widget').length);
        expect(widgetsCount).to.equal(1);

        const dashboardName = await page.evaluate(() => $('#Dashboard_embeddedIndex_6').text().trim());
        expect(dashboardName).to.equal('new <dash> 💩');
    });

    it("should reset dashboard when reset dashboard process completed", async function() {
        await clickDashboardMenuItem('resetDashboard');
        await page.waitForSelector('.modal.open');
        var button = await page.jQuery('.modal.open .modal-footer a:contains(Yes)');
        await button.click();
        await page.waitForNetworkIdle();
        await page.waitForSelector('.widget');
        await page.waitForNetworkIdle();

        // after resetting dashboard should have 10 widgets
        const widgetsCount = await page.evaluate(() => $('#dashboardWidgetsArea .widget').length);
        expect(widgetsCount).to.equal(10);
    });

    it("should remove dashboard when remove dashboard process completed", async function() {
        await clickDashboardMenuItem('removeDashboard');
        await page.waitForSelector('.modal.open');
        var button = await page.jQuery('.modal.open .modal-footer a:contains(Yes)');
        await button.click();
        await page.waitForTimeout(200);
        await page.waitForNetworkIdle();
        await page.waitForSelector('.widget');
        await page.waitForNetworkIdle();

        // dashboard should be removed from list, so 5 remaining dashboards
        const dashboardCount = await page.evaluate(() => $('#Dashboard ul li').length);
        expect(dashboardCount).to.equal(5);
        const dashboardNames = await page.evaluate(() => $('#Dashboard ul').text().trim());
        expect(dashboardNames).to.not.contain('new <dash> 💩');

        // first dashboard should be loaded, which contains 1 widget
        const activeDashboardName = await page.evaluate(() => $('#Dashboard ul li.active').text().trim());
        expect(activeDashboardName).to.equal('newname');
        const widgetsCount = await page.evaluate(() => $('#dashboardWidgetsArea .widget').length);
        expect(widgetsCount).to.equal(1);
    });

    it("should not fail when default widget selection changed", async function() {
        await page.goto(url);
        await clickDashboardMenuItem('setAsDefaultWidgets');
        var button = await page.jQuery('.modal.open .modal-footer a:contains(Yes)');
        await button.click();
        await page.waitForTimeout(200);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('default_widget_selection_changed');
    });

    it("should create new dashboard with new default widget selection when create dashboard process completed", async function() {
        await clickDashboardMenuItem('createDashboard');
        await page.waitForSelector('#createDashboardName', { visible: true });

        // try to type the text a few times, as it sometimes doesn't get the full value
        var name = 'newdash2';
        for (var i=0; i<5; i++) {
            await page.evaluate(function() {
                $('#createDashboardName').val('');
            });
            await page.type('#createDashboardName', name);
            await page.waitForTimeout(500); // sometimes the text doesn't seem to type fast enough

            var value = await page.evaluate(function() {
                return $('#createDashboardName').prop('value');
            });

            if (value === name) {
                break;
            }
        }

        var button = await page.jQuery('.modal.open .modal-footer a:contains(Ok)');
        await button.click();
        await page.waitForSelector('.widget');
        await page.waitForNetworkIdle();

        // dashboard should be added to list, so 6 dashboards available
        const dashboardCount = await page.evaluate(() => $('#Dashboard ul li').length);
        expect(dashboardCount).to.equal(6);
        const dashboardNames = await page.evaluate(() => $('#Dashboard ul').text().trim());
        expect(dashboardNames).to.contain(name);

        // new dashboard should be loaded, which contains 1 widget
        const activeDashboardName = await page.evaluate(() => $('#Dashboard ul li.active').text().trim());
        expect(activeDashboardName).to.equal(name);
        const widgetsCount = await page.evaluate(() => $('#dashboardWidgetsArea .widget').length);
        expect(widgetsCount).to.equal(1);
    });

    it("should load segmented dashboard", async function() {
        await removeAllExtraDashboards();
        await page.goto(url + '&segment=' + encodeURIComponent("browserCode==FF"));

        expect(await page.screenshot({ fullPage: true })).to.matchImage('segmented');
    });

    it("should load correctly with token_auth", async function() {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();

        var tokenAuth = "a4ca4238a0b923820dcc509a6f75849f";
        await page.goto(url.replace("idDashboard=5", "idDashboard=1") + '&token_auth=' + tokenAuth);

        // list of dashboard should be hidden
        expect(await page.$('#Dashboard')).to.be.not.ok;

        // should show one widget on dashboard
        const widgetsCount = await page.evaluate(() => $('#dashboardWidgetsArea .widget').length);
        expect(widgetsCount).to.equal(1);
    });

    it("should fail to load with invalid token_auth", async function() {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();

        var tokenAuth = "anyInvalidToken";
        await page.goto(url.replace("idDashboard=5", "idDashboard=1") + '&token_auth=' + tokenAuth);

        // should show login page with error message
        expect(await page.$('#loginPage')).to.be.ok;
        const errorMessage = await page.evaluate(() => $('.message_container').text());
        expect(errorMessage).to.contain('You must be logged in to access this functionality.');
    });
});
