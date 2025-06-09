/*!
 * Matomo - free/libre analytics platform
 *
 * PivotByDimension UI tests
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PivotByDimension", function () {
    var eventsUrl = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09#?idSite=1&period=year&date=2012-08-09&category=General_Actions&subcategory=Events_Events",
        actionsUrl = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09#?idSite=1&period=year&date=2012-08-09&category=General_Actions&subcategory=General_Pages",
        cvarsUrl = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09#?idSite=1&period=year&date=2012-08-09&category=General_Visitors&subcategory=CustomVariables_CustomVariables"
        ;

    it("should pivot a report correctly when the pivot cog option is selected", async function () {
        await page.goto(eventsUrl);

        let element = await page.jQuery('.dimension:contains(Event Names)');
        await element.click();
        await page.waitForNetworkIdle();

        await page.evaluate(function(){
            $('.dropdownConfigureIcon').click();
            $('.dataTablePivotBySubtable').click();
        });
        await page.waitForNetworkIdle();

        await page.mouse.move(-150, -150); // make sure nothing is highlighted

        element = await page.$('.theWidgetContent');
        expect(await element.screenshot()).to.matchImage('pivoted');
    });

    it("should not display the pivot option on actions reports", async function () {
        await page.goto(actionsUrl);

        const element = await page.$('.dataTablePivotBySubtable');
        expect(element).to.be.not.ok;
    });

    it("should display the pivot option on reports that set a custom columns_to_display", async function () {
        await page.goto(cvarsUrl);
        await page.waitForNetworkIdle();
        await page.click('.dropdownConfigureIcon');
        await page.click('.dataTablePivotBySubtable');
        await page.waitForNetworkIdle();

        await page.mouse.move(-15, -15); // make sure nothing is highlighted
        await page.waitForTimeout(100);

        expect(await page.screenshotSelector('.dataTable,.expandDataTableFooterDrawer')).to.matchImage({
            imageName: 'pivoted_columns_report',
            comparisonThreshold: 0.0003,
        });
    });
});
