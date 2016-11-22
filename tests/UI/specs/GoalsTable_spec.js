/*!
 * Piwik - free/libre analytics platform
 *
 * GoalsTable screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("GoalsTable", function () {
    this.timeout(0);

    var url = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
            + "actionToWidgetize=getKeywords&viewDataTable=table&filter_limit=5&isFooterExpandedInDashboard=1";

    it("should load when the goals icon is clicked", function (done) {
        expect.screenshot('initial').to.be.capture(function (page) {
            page.load(url);
            page.click('.activateVisualizationSelection');
            page.click('.tableIcon[data-footer-icon-id=tableGoals]');
        }, done);
    });

    it("should show columns for all goals when idGoal is 0", function (done) {
        expect.screenshot('goals_table_full').to.be.capture(function (page) {
            var url = page.getCurrentUrl().replace(/viewDataTable=[^&]*/, "viewDataTable=tableGoals") + "&idGoal=0";
            page.load(url);
        }, done);
    });

    it("should show columns for a single goal when idGoal is 1", function (done) {
        expect.screenshot('goals_table_single').to.be.capture(function (page) {
            page.load(page.getCurrentUrl().replace(/idGoal=[^&]*/, "idGoal=1"));
        }, done);
    });

    it("should show an ecommerce view when idGoal is ecommerceOrder", function (done) {
        expect.screenshot('goals_table_ecommerce').to.be.capture(function (page) {
            page.load(page.getCurrentUrl().replace(/idGoal=[^&]*/, "idGoal=ecommerceOrder"));
        }, done);
    });

    it("should show a special view when idGoal is ecommerceOrder and viewDataTable is ecommerceOrder", function (done) {
        expect.screenshot('goals_table_ecommerce_view').to.be.capture(function (page) {
            var url = page.getCurrentUrl().replace(/moduleToWidgetize=[^&]*/, "moduleToWidgetize=Goals")
                                          .replace(/actionToWidgetize=[^&]*/, "actionToWidgetize=getItemsSku")
                                          .replace(/viewDataTable=[^&]*/, "viewDataTable=ecommerceOrder");
            page.load(url);
        }, done);
    });

    it("should show abandoned carts data when the abandoned carts link is clicked", function (done) {
        expect.screenshot('goals_table_abandoned_carts').to.be.capture(function (page) {
            page.click('.activateVisualizationSelection');
            page.click('.tableIcon[data-footer-icon-id=ecommerceAbandonedCart]');
        }, done);
    });
});