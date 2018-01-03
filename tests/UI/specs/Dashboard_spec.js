/*!
 * Piwik - free/libre analytics platform
 *
 * Dashboard screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// TODO: should move this & dashboard manager test to Dashboard plugin
describe("Dashboard", function () {
    this.timeout(0);

    var url = "?module=Widgetize&action=iframe&idSite=1&period=year&date=2012-08-09&moduleToWidgetize=Dashboard&"
            + "actionToWidgetize=index&idDashboard=5";

    var removeAllExtraDashboards = function (done) {
        testEnvironment.callController("Dashboard.getAllDashboards", {}, function (err, dashboards) {
            dashboards = (dashboards || []).filter(function (dash) {
                return parseInt(dash.iddashboard) > 5;
            });

            var removeDashboard = function (i) {
                if (i >= dashboards.length) {
                    done();
                    return;
                }

                console.log("Removing dashboard ID = " + dashboards[i].iddashboard);
                testEnvironment.callController("Dashboard.removeDashboard", {idDashboard: dashboards[i].iddashboard}, function () {
                    removeDashboard(i + 1);
                });
            };

            removeDashboard(0);
        });
    };

    var setup = function (done) {
        // make sure live widget doesn't refresh constantly for UI tests
        testEnvironment.overrideConfig('General', 'live_widget_refresh_after_seconds', 1000000);
        testEnvironment.save();

        // save empty layout for dashboard ID = 5
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

        // TODO: should probably include an async lib
        testEnvironment.callController("Dashboard.saveLayout", {name: 'D4', layout: JSON.stringify(layout), idDashboard: 5, idSite: 2}, function () {
            // reset default widget selection
            testEnvironment.callController("Dashboard.saveLayoutAsDefault", {layout: 0}, function () {
                removeAllExtraDashboards(done);
            });
        });
    };

    before(setup);
    after(setup);

    it("should load correctly", function (done) {
        expect.screenshot("loaded").to.be.capture(function (page) {
            page.load(url, 5000);
        }, done);
    });

    it("should move a widget when widget is drag & dropped", function (done) {
        expect.screenshot("widget_move").to.be.capture(function (page) {
            page.mousedown('.widgetTop');
            page.mouseMove('#dashboardWidgetsArea > .col:eq(2)');
            page.mouseup('#dashboardWidgetsArea > .col:eq(2)');
        }, done);
    });

    it("should refresh widget when widget refresh icon clicked", function (done) {
        expect.screenshot("widget_move_refresh").to.be.capture(function (page) {
            page.mouseMove('.widgetTop');
            page.click('.button#refresh');
            page.mouseMove('.dashboard-manager'); // let widget top hide again
        }, done);
    });

    it("should minimise widget when widget minimise icon clicked", function (done) {
        expect.screenshot("widget_minimised").to.be.capture(function (page) {
            page.mouseMove('.widgetTop');
            page.click('.button#minimise');
        }, done);
    });

    it("should unminimise widget when widget maximise icon is clicked after being minimised", function (done) {
        expect.screenshot("widget_move_unminimised").to.be.capture(function (page) {
            page.mouseMove('.widgetTop');
            page.click('.button#maximise');
            page.mouseMove('.dashboard-manager'); // let widget top hide again
        }, done);
    });

    it("should maximise widget when widget maximise icon is clicked", function (done) {
        expect.screenshot("widget_maximise").to.be.capture(function (page) {
            page.mouseMove('.widgetTop');
            page.click('.button#maximise');
        }, done);
    });

    it("should close maximise dialog when minimise icon is clicked", function (done) {
        expect.screenshot("widget_move_unmaximise").to.be.capture(function (page) {
            page.mouseMove('.widgetTop');
            page.click('.button#minimise');
            page.mouseMove('.dashboard-manager'); // let widget top hide again
        }, done);
    });

    it("should add a widget when a widget is selected in the dashboard manager", function (done) {
        expect.screenshot("widget_add_widget").to.be.capture(function (page) {
            page.click('.dashboard-manager .title');

            page.mouseMove('.widgetpreview-categorylist>li:contains(Live!)'); // have to mouse move twice... otherwise Live! will just be highlighted
            page.click('.widgetpreview-categorylist>li:contains(Live!)');

            page.mouseMove('.widgetpreview-categorylist>li:contains(Actions):first');
            page.click('.widgetpreview-categorylist>li:contains(Actions):first');

            page.mouseMove('.widgetpreview-widgetlist>li:contains(Pages):first');
            page.click('.widgetpreview-widgetlist>li:contains(Pages):first');
        }, done);
    });

    it("should open row evolution", function (done) {
        expect.screenshot("rowevolution").to.be.captureSelector('.ui-dialog:visible', function (page) {
            page.mouseMove('#widgetActionsgetPageUrls table.dataTable tbody tr:contains(thankyou) td:first-child', 100);
            page.mouseMove('a.actionRowEvolution:visible'); // necessary to get popover to display
            page.click('a.actionRowEvolution:visible', 2000);
        }, done);
    });

    it("should remove widget when remove widget icon is clicked", function (done) {
        expect.screenshot("widget_move_removed").to.be.capture(function (page) {
            page.click('.ui-dialog-titlebar-close:visible'); // close row evolution

            var widget = '[id="widgetActionsgetPageUrls"]';

            page.mouseMove(widget + ' .widgetTop');
            page.click(widget + ' .button#close');

            page.click('.modal.open .modal-footer a:contains(Yes)');
            page.mouseMove('.dashboard-manager');
        }, done);
    });

    it("should change dashboard layout when new layout is selected", function (done) {
        expect.screenshot("change_layout").to.be.capture(function (page) {
            page.click('.dashboard-manager .title');
            page.click('li[data-action=showChangeDashboardLayoutDialog]');
            page.click('.modal.open div[layout=50-50]');
            page.click('.modal.open .modal-footer a:contains(Save)');
        }, done);
    });

    it("should rename dashboard when dashboard rename process completed", function (done) {
        expect.screenshot("rename").to.be.capture(function (page) {
            page.click('.dashboard-manager .title');
            page.click('li[data-action=renameDashboard]');
            page.evaluate(function () {
                $('#newDashboardName:visible').val('newname'); // don't use sendKeys or click, since in this test it appears to trigger a seg fault on travis
                $('.modal.open .modal-footer a:contains(Save):visible').click();
            });
        }, done);
    });

    it("should copy dashboard successfully when copy dashboard process completed", function (done) {
        expect.screenshot("copied").to.be.capture(function (page) {
            page.click('.dashboard-manager .title');
            page.click('li[data-action=copyDashboardToUser]');
            page.evaluate(function () {
                $('[id=copyDashboardName]:last').val('');
            });
            page.sendKeys('[id=copyDashboardName]:last', 'newdash');
            page.evaluate(function () {
                $('[id=copyDashboardUser]:last').val('superUserLogin');
            });
            page.click('.modal.open .modal-footer a:contains(Ok)');

            page.load(url.replace("idDashboard=5", "idDashboard=6"));
        }, done);
    });

    it("should reset dashboard when reset dashboard process completed", function (done) {
        this.retries(3);
        expect.screenshot("reset").to.be.capture(function (page) {
            page.click('.dashboard-manager .title');
            page.click('li[data-action=resetDashboard]');
            page.click('.modal.open .modal-footer a:contains(Yes)', 4000);
            page.evaluate(function(){
                $('#widgetReferrersgetReferrerType').hide();
                $('#widgetReferrersgetReferrerType').offsetHeight;
                $('#widgetReferrersgetReferrerType').show();
            }, 100);
            page.mouseMove('.dashboard-manager');
        }, done);
    });

    it("should remove dashboard when remove dashboard process completed", function (done) {
        expect.screenshot("removed").to.be.capture(function (page) {
            page.click('.dashboard-manager .title');
            page.click('li[data-action=removeDashboard]');
            page.click('.modal.open .modal-footer a:contains(Yes)');
            page.mouseMove('.dashboard-manager');
            page.evaluate(function () {
                $('.widgetTop').removeClass('widgetTopHover');
            });
        }, done);
    });

    it("should not fail when default widget selection changed", function (done) {
        expect.screenshot("default_widget_selection_changed").to.be.capture(function (page) {
            page.load(url);
            page.click('.dashboard-manager .title');
            page.click('li[data-action=setAsDefaultWidgets]');
            page.click('.modal.open .modal-footer a:contains(Yes)');
        }, done);
    });

    it("should create new dashboard with new default widget selection when create dashboard process completed", function (done) {
        expect.screenshot("create_new").to.be.capture(function (page) {
            page.click('.dashboard-manager .title');
            page.click('li[data-action=createDashboard]');
            page.sendKeys('#createDashboardName:visible', 'newdash2');
            page.click('.modal.open .modal-footer a:contains(Ok)');
        }, done);
    });

    it("should load segmented dashboard", function (done) {

        removeAllExtraDashboards(function(){});

        expect.screenshot("segmented").to.be.capture(function (page) {
            page.load(url + '&segment=' + encodeURIComponent("browserCode==FF"), 5000);
        }, done);
    });

    it("should load correctly with token_auth", function (done) {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();

        expect.screenshot("loaded_token_auth").to.be.capture(function (page) {
            var tokenAuth = "9ad1de7f8b329ab919d854c556f860c1";
            page.load(url.replace("idDashboard=5", "idDashboard=1") + '&token_auth=' + tokenAuth, 5000);
        }, done);
    });

    it("should fail to load with invalid token_auth", function (done) {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();

        expect.screenshot("invalid_token_auth").to.be.capture(function (page) {
            var tokenAuth = "anyInvalidToken";
            page.load(url.replace("idDashboard=5", "idDashboard=1") + '&token_auth=' + tokenAuth, 5000);
        }, done);
    });

});
