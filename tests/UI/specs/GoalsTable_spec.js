/*!
 * Piwik - free/libre analytics platform
 *
 * GoalsTable screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("GoalsTable", function () {
    const url = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
              + "actionToWidgetize=getKeywords&viewDataTable=table&filter_limit=5&isFooterExpandedInDashboard=1";

    it("should load when the goals icon is clicked", async function () {
        await page.goto(url);
        await page.click('.activateVisualizationSelection');
        await page.click('.tableIcon[data-footer-icon-id=tableGoals]');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('initial');
    });

    it("should show columns for all goals when idGoal is 0", async function () {
        const allGoalsUrl = page.url().replace(/viewDataTable=[^&]*/, "viewDataTable=tableGoals") + "&idGoal=0";
        await page.goto(allGoalsUrl);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('goals_table_full');
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
        await page.click('.activateVisualizationSelection');
        await page.click('.tableIcon[data-footer-icon-id=ecommerceAbandonedCart]');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('goals_table_abandoned_carts');
    });
});