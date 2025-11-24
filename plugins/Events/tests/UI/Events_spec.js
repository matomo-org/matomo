describe("Events", function () {
    var generalParams = 'idSite=1&period=year&date=2012-08-09',
        urlBaseGeneric = 'module=CoreHome&action=index&',
        urlBase = urlBaseGeneric + generalParams;

    it('should load the Events > index page correctly', async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=Events_Events");
        await page.mouse.move(-10, -10);

        expect(await page.screenshotSelector('.pageWrap,.dataTable')).to.matchImage('overview');
    });

    it("should show report flattened", async function() {
        await page.click('.dropdownConfigureIcon');
        await page.mouse.move(-10, -10);
        await page.waitForTimeout(250); // rendering
        await page.click('.dataTableFlatten');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector('.theWidgetContent')).to.matchImage('flattened');
    });

    it("should show another secondary dimension", async function() {
        await page.evaluate(() => $('.datatableRelatedReports li span:contains("Event Name")').click());
        await page.waitForTimeout(50);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector('.theWidgetContent')).to.matchImage('secondary_switched');
    });
});
