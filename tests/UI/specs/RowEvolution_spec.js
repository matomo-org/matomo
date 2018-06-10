/*!
 * Piwik - free/libre analytics platform
 *
 * row evolution screenshot tests
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("RowEvolution", function () {
    this.timeout(0);

    var viewDataTableUrl = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=week&date=2012-02-09&"
                         + "actionToWidgetize=getKeywords&viewDataTable=table&filter_limit=5";

    var ecommerceItemReportWidgetized = "?module=Widgetize&action=iframe&moduleToWidgetize=Goals&actionToWidgetize=getItemsSku&idGoal=ecommerceAbandonedCart"
                                      + "&idSite=1&period=year&date=2012-02-09&viewDataTable=ecommerceAbandonedCart&filter_limit=-1";

    it('should load when icon clicked in ViewDataTable', async function() {
        expect.screenshot('row_evolution').to.be.captureSelector('.ui-dialog', function (page) {
            page.goto(viewDataTableUrl);
            page.mouseMove('tbody tr:first-child');
            page.mouseMove('a.actionRowEvolution:visible'); // necessary to get popover to display
            page.click('a.actionRowEvolution:visible');
        }, done);
    });

    it('should change the metric shown when a metric sparkline row is clicked', async function() {
        expect.screenshot('row_evolution_other_metric').to.be.captureSelector('.ui-dialog', function (page) {
            page.click('table.metrics tr[data-i=1]');
        }, done);
    });

    it('should show two serieses when a metric sparkline row is shift+clicked', async function() {
        expect.screenshot('row_evolution_multiple_series').to.be.captureSelector('.ui-dialog', function (page) {
            page.click('table.metrics tr[data-i=2]', ['shift']);
        }, done);
    });

    it('should load multi-row evolution correctly', async function() {
        expect.screenshot('multirow_evolution').to.be.captureSelector('.ui-dialog', function (page) {
            page.click('.rowevolution-startmulti');
            page.mouseMove('tbody tr:nth-child(2)');
            page.mouseMove('a.actionRowEvolution:visible');
            page.click('a.actionRowEvolution:visible');
        }, done);
    });

    it('should display a different row evolution metric when the metric selection is changed', async function() {
        expect.screenshot('multirow_evolution_other_metric').to.be.captureSelector('.ui-dialog', function (page) {
            page.evaluate(function () {
                $('select.multirowevoltion-metric').val($('select.multirowevoltion-metric option:nth-child(3)').val()).change();
            });
            page.wait(1000);
        }, done);
    });

    it('should display row evolution for an ecommerce item report correctly', async function() {
        expect.screenshot('row_evolution_ecommerce_item').to.be.captureSelector('.ui-dialog', function (page) {
            page.goto(ecommerceItemReportWidgetized);
            page.mouseMove('tbody tr:first-child');
            page.mouseMove('a.actionRowEvolution:visible'); // necessary to get popover to display
            page.click('a.actionRowEvolution:visible');
        }, done);
    });
});
