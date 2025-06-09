/*!
 * Matomo - free/libre analytics platform
 *
 * ViewDataTable screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SearchFilterPersistenceTest", function () {
    var baseUrl = "?module=CoreHome&action=index&idSite=1&period=day&date=2012-08-09&category=General_Actions";
    var url1 = "&subcategory=Actions_SubmenuPagesEntry";
    var url2 = "&subcategory=Actions_SubmenuPagesExit";

    it("should load correctly", async function () {
        await page.goto(baseUrl + url1);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('load_ok');
    });

    it("should search the table when a search string is entered and the search button clicked", async function () {
        await page.click('.dataTableAction.searchAction');
        await page.focus('.searchAction .dataTableSearchInput');
        await page.keyboard.type('lo');
        await page.click('.searchAction .icon-search');
        await page.waitForNetworkIdle();
        await page.evaluate(() => document.activeElement.blur());
        await page.mouse.move(-10, -10);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('search_results');
    });

    it("should persist the search term when date is moved", async function () {
        await page.click('.periodSelector .move-period-next');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('persisted_date');
    });

    it("should persist column sorting when date is moved", async function () {
        // click twice to sort ascending
        await(await page.jQuery('th#sum_bandwidth', { waitFor: true })).click();
        await page.waitForNetworkIdle();
        await page.waitForTimeout(200);
        await(await page.jQuery('th#sum_bandwidth', { waitFor: true })).click();
        await page.waitForNetworkIdle();
        await page.waitForTimeout(200);

        await page.click('.periodSelector .move-period-prev');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('persisted_sorting');
    });

    it("should persist the search term when segment is changed", async function () {
        await page.click('.segmentationContainer');
        await(await page.jQuery('li[data-idsegment=3] .segname', { waitFor: true })).click();
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('persisted_segment');
    });

    it("should reset the search term when page is changed there and back", async function () {
        await page.goto(baseUrl + url1);
        await page.waitForNetworkIdle();
        await page.goto(baseUrl + url2);
        await page.waitForNetworkIdle();
        await page.goto(baseUrl + url1);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('load_ok');
    });
});
