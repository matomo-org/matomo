/*!
 * Piwik - free/libre analytics platform
 *
 * Dashboard manager screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("DashboardManager", function () {
    this.timeout(0);

    const selectorToCapture = '.dashboard-manager,.dashboard-manager .dropdown';

    const generalParams = 'idSite=1&period=day&date=2012-01-01';
    const url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=Dashboard_Dashboard&subcategory=5';

    it("should load correctly", async function() {
        await page.goto(url);

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
            $('.dashboard-manager').data('uiControlObject').widgetSelected = function () {};
        });

        vot = await page.jQuery('.widgetpreview-widgetlist>li:contains(Visits Over Time)');
        await vot.click();

        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('loaded');
    });

    it("should create new dashboard with new default widget selection when create dashboard process completed", async function() {
        await page.click('.dashboard-manager .title');
        await page.click('li[data-action="createDashboard"]');
        await page.type('#createDashboardName', 'newdash2');
        button = await page.jQuery('.modal.open .modal-footer a:contains(Ok)');
        await button.click();

        await page.waitForFunction('$("ul.navbar ul li.active:contains(newdash2)").length > 0');
        await page.waitFor(500);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('create_new');
    });

    it("should remove dashboard when remove dashboard process completed", async function() {
        await page.click('.dashboard-manager .title');
        await page.click('li[data-action="removeDashboard"]');
        button = await page.jQuery('.modal.open .modal-footer a:contains(Yes)');
        await button.click();

        await page.waitFor(500);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('removed');
    });
});