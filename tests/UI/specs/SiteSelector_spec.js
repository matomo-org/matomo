/*!
 * Piwik - free/libre analytics platform
 *
 * Site selector screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SiteSelector", function () {
    this.timeout(0);

    const selectorToCapture = '[piwik-siteselector],[piwik-siteselector] .dropdown';
    const url = "?module=UsersManager&action=userSettings&idSite=1&period=day&date=yesterday";

    it("should load correctly", async function() {
        await page.goto(url);
        await page.waitForNetworkIdle();

        dialog = await page.$(selectorToCapture);
        expect(await dialog.screenshot()).to.matchImage('loaded');
    });

    it("should display expanded when clicked", async function() {
        await page.click('.sites_autocomplete .title');

        await page.waitForSelector('.custom_select_ul_list');
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('expanded');
    });

    it("should show no results when search returns no results", async function() {
        await page.type(".websiteSearch", "abc");
        await page.waitFor(500);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('search_no_results');
    });

    it("should search when one character typed into search input", async function() {
        await page.click('.reset');
        await page.type(".websiteSearch", "s");
        await page.waitForNetworkIdle();
        await page.waitFor(500);

        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('search_one_char');
    });

    it("should search again when second character typed into search input", async function() {
        await page.type(".websiteSearch", "t");
        await page.waitForNetworkIdle();
        await page.waitFor(500);

        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('search_two_chars');
    });

    it("should change the site when a site is selected", async function() {
        elem = await page.jQuery(".custom_select_ul_list>li:visible");
        elem.click();
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('site_selected');
    });
});