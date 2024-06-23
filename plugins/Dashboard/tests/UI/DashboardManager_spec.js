/*!
 * Matomo - free/libre analytics platform
 *
 * Dashboard manager screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("DashboardManager", function () {
    const selectorToCapture = '.dashboard-manager,.dashboard-manager .dropdown';

    const generalParams = 'idSite=1&period=day&date=2012-01-01';
    const url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=Dashboard_Dashboard&subcategory=1';

    it("should load correctly", async function() {
        await page.goto(url);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('loaded');
    });

    it("should expand when clicked", async function() {
        await page.click('.dashboard-manager .title');

        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('expanded');
    });

    it("should show widget for a category when category label hovered", async function() {
        live = await page.jQuery('.widgetpreview-categorylist>li:contains(Goals)');
        await live.hover();

        visitors = await page.jQuery('.widgetpreview-categorylist>li:contains(Visitors):first');
        await visitors.hover();
        await visitors.click();

        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('widget_list_shown');
    });

    it("should load a widget preview when a widget is hovered", async function() {
        vot = await page.jQuery('.widgetpreview-widgetlist>li:contains(Visits Over Time)');
        await vot.hover();

        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('widget_preview');
    });

    it("should close the manager when a widget is selected", async function() {
        // make sure selecting a widget does nothing
        await page.evaluate(function () {
            window.MATOMO_DASHBOARD_SETTINGS_WIDGET_SELECTED_NOOP = true;
        });

        vot = await page.jQuery('.widgetpreview-widgetlist>li:contains(Visits Over Time)');
        await vot.click();

        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('loaded');
    });

    it("should create new dashboard with new default widget selection when create dashboard process completed", async function() {
        // check that widget count on currently loaded dashboard is correct, so we are able to say if
        // number of widgets has changed after creating and loading the new one
        const widgetsCountBefore = await page.evaluate(() => $('#dashboardWidgetsArea .widget').length);
        expect(widgetsCountBefore).to.equal(1);

        await page.click('.dashboard-manager .title');
        await page.click('li[data-action="createDashboard"]');
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
                return $('#createDashboardName').attr('value');
            });

            if (value === name) {
                break;
            }
        }

        button = await page.jQuery('.modal.open .modal-footer a:contains(Ok)');
        // ensure tour widget always shows the same state
        testEnvironment.completeAllChallenges = 1;
        testEnvironment.save();

        await button.click();

        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        await page.waitForSelector('.widget');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(100); // wait for widgets to render fully

        // check new dashboard was loaded, which should have 10 widgets
        const widgetsCount = await page.evaluate(() => $('#dashboardWidgetsArea .widget').length);
        expect(widgetsCount).to.equal(10);

        // check dashboard has been added to menu, causing menu to switch from listing to selector
        expect(await page.screenshotSelector('#secondNavBar .menuTab.active')).to.matchImage('create_new');
    });


    it("should load widgets on smaller screen", async function(){
        await page.webpage.setViewport({ width: 815, height: 512 });
        await page.waitForTimeout(500);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('small_screen');
    });

    it("should remove dashboard when remove dashboard process completed", async function() {
        await page.click('.dashboard-manager .title');
        await page.click('li[data-action="removeDashboard"]');
        button = await page.jQuery('.modal.open .modal-footer a:contains(Yes)');
        await button.click();

        await page.mouse.move(-10, -10);
        await page.waitForTimeout(500);
        await page.waitForNetworkIdle();

        await page.waitForSelector('.widget');
        await page.waitForNetworkIdle();

        // check initial dashboard was loaded again, which should have 1 widget only
        const widgetsCount = await page.evaluate(() => $('#dashboardWidgetsArea .widget').length);
        expect(widgetsCount).to.equal(1);

        // check dashboard has been removed from menu, causing menu to switch from selector to listing
        expect(await page.screenshotSelector('#secondNavBar .menuTab.active')).to.matchImage('removed');
    });
});
