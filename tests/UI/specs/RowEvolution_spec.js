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

    const viewDataTableUrl = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=week&date=2012-02-09&"
                         + "actionToWidgetize=getKeywords&viewDataTable=table&filter_limit=5";

    const ecommerceItemReportWidgetized = "?module=Widgetize&action=iframe&moduleToWidgetize=Goals&actionToWidgetize=getItemsSku&idGoal=ecommerceAbandonedCart"
                                      + "&idSite=1&period=year&date=2012-02-09&viewDataTable=ecommerceAbandonedCart&filter_limit=-1";

    it('should load when icon clicked in ViewDataTable', async function() {
        await page.goto(viewDataTableUrl);
        const row = await page.waitForSelector('tbody tr:first-child');
        await row.hover();

        const icon = await page.waitForSelector('tbody tr:first-child a.actionRowEvolution');
        await icon.click();

        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('row_evolution');
    });

    it('should change the metric shown when a metric sparkline row is clicked', async function() {
        await page.click('table.metrics tr[data-i="1"]');
        await page.waitForNetworkIdle();

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('row_evolution_other_metric');
    });

    it('should show two serieses when a metric sparkline row is shift+clicked', async function() {
        await page.keyboard.down('Shift');
        await page.click('table.metrics tr[data-i="2"]', ['shift']);
        await page.keyboard.up('Shift');
        await page.waitForNetworkIdle();

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('row_evolution_multiple_series');
    });

    it('should load multi-row evolution correctly', async function() {
        await page.click('.rowevolution-startmulti');

        const row = await page.waitForSelector('tbody tr:nth-child(2)');
        await row.hover();

        const icon = await page.waitForSelector('tbody tr:nth-child(2) a.actionRowEvolution');
        await icon.click();

        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('multirow_evolution');
    });

    it('should display a different row evolution metric when the metric selection is changed', async function() {
        await page.evaluate(function () {
            $('select.multirowevoltion-metric').val($('select.multirowevoltion-metric option:nth-child(3)').val()).change();
        });

        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('multirow_evolution_other_metric');
    });

    it('should display row evolution for an ecommerce item report correctly', async function() {
        await page.goto(ecommerceItemReportWidgetized);
        const row = await page.waitForSelector('tbody tr:first-child');
        await row.hover();

        const icon = await page.waitForSelector('tbody tr:first-child a.actionRowEvolution');
        await icon.click();

        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('row_evolution_ecommerce_item');
    });
});
