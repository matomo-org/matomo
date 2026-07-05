/*!
 * Matomo - free/libre analytics platform
 *
 * GoalsTable screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("GoalsTable", function () {
    const url = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
              + "actionToWidgetize=getKeywords&viewDataTable=table&filter_limit=5&isFooterExpandedInDashboard=1";

    it("should load when the goals icon is clicked", async function () {
        await page.goto(url);
        await page.click('.activateVisualizationSelection > span');
        await page.click('.tableIcon[data-footer-icon-id=tableGoals]');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('initial');
    });

    it("should show columns for all goals when idGoal is 0", async function () {
        const allGoalsUrl = page.url().replace(/viewDataTable=[^&]*/, "viewDataTable=tableGoals") + "&idGoal=0";
        await page.goto(allGoalsUrl);
        // The all-goals table renders its per-goal metric columns progressively; on CI the capture can be
        // taken before the full-width table (all metric columns) has loaded. Wait for the column count to
        // stabilise so all columns are present.
        await page.waitForNetworkIdle();
        // Nudge a layout recompute so the table renders its full column set on the faster CI.
        await page.evaluate(() => window.dispatchEvent(new Event('resize')));
        await page.waitForTimeout(250);
        await page.evaluate(() => { window.__gtCols = -1; window.__gtStable = 0; });
        await page.waitForFunction(() => {
            const n = document.querySelectorAll('table.dataTable thead th').length;
            if (n === window.__gtCols) {
                window.__gtStable += 1;
            } else {
                window.__gtStable = 0;
                window.__gtCols = n;
            }
            return window.__gtStable >= 4;
        }, { polling: 150, timeout: 8000 }).catch(() => {});

        const table = await page.$('table.dataTable');
        expect(await table.screenshot()).to.matchImage('goals_table_full');
    });

    it("should show columns for a single goal when idGoal is 1", async function () {
        await page.goto(page.url().replace(/idGoal=[^&]*/, "idGoal=1"));

        expect(await page.screenshot({ fullPage: true })).to.matchImage('goals_table_single');
    });

    it("should show an ecommerce view when idGoal is ecommerceOrder", async function () {
        await page.goto(page.url().replace(/idGoal=[^&]*/, "idGoal=ecommerceOrder"));

        expect(await page.screenshot({ fullPage: true })).to.matchImage('goals_table_ecommerce');
    });

    it("should show a special view when idGoal is ecommerceOrder and viewDataTable is ecommerceOrder", async function () {
        const ecommerceUrl = page.url().replace(/moduleToWidgetize=[^&]*/, "moduleToWidgetize=Goals")
            .replace(/actionToWidgetize=[^&]*/, "actionToWidgetize=getItemsSku")
            .replace(/viewDataTable=[^&]*/, "viewDataTable=ecommerceOrder");

        await page.goto(ecommerceUrl);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('goals_table_ecommerce_view');
    });

    it("should show abandoned carts data when the abandoned carts link is clicked", async function () {
        await page.click('.activateVisualizationSelection > span');
        await page.click('.tableIcon[data-footer-icon-id=ecommerceAbandonedCart]');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('goals_table_abandoned_carts');
    });
});
