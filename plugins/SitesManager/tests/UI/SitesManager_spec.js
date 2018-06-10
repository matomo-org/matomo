/*!
 * Matomo - free/libre analytics platform
 *
 * SitesManager screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SitesManager", function () {
    this.timeout(0);
    this.fixture = "Piwik\\Plugins\\SitesManager\\tests\\Fixtures\\ManySites";

    var url = "?module=SitesManager&action=index&idSite=1&period=day&date=yesterday&showaddsite=false";

    function assertScreenshotEquals(screenshotName, done, test)
    {
        expect.screenshot(screenshotName).to.be.captureSelector('#content', test, done);
    }

    function loadNextPage(page)
    {
        page.click('.SitesManager .paging:first .next');
    }

    function loadPreviousPage(page)
    {
        page.click('.SitesManager .paging:first .prev');
    }

    function searchForText(page, textToAppendToSearchField)
    {
        page.sendKeys(".SitesManager .search:first input", textToAppendToSearchField);
        page.click('.SitesManager .search:first img');
        page.wait(150);
    }

    it("should load correctly and show page 0", async function() {
        assertScreenshotEquals("loaded", done, function (page) {
            page.load(url);
        });
    });

    it("should show page 1 when clicking next", async function() {
        assertScreenshotEquals("page_1", done, function (page) {
            loadNextPage(page);
        });
    });

    it("should show page 2 when clicking next", async function() {
        assertScreenshotEquals("page_2", done, function (page) {
            loadNextPage(page);
        });
    });

    it("should show page 1 when clicking prev", async function() {
        assertScreenshotEquals("page_1_again", done, function (page) {
            loadPreviousPage(page);
        });
    });

    it("should search for websites and reset page to 0", async function() {
        assertScreenshotEquals("search", done, function (page) {
            searchForText(page, 'SiteTes');
        });
    });

    it("should page within search result to page 1", async function() {
        assertScreenshotEquals("search_page_1", done, function (page) {
            loadNextPage(page);
        });
    });

    it("should search for websites no result", async function() {
        assertScreenshotEquals("search_no_result", done, function (page) {
            searchForText(page, 'RanDoMSearChTerm');
        });
    });

    it("should load the global settings page", async function() {
        assertScreenshotEquals("global_settings", done, function (page) {
            page.load('?module=SitesManager&action=globalSettings&idSite=1&period=day&date=yesterday&showaddsite=false');
            page.evaluate(function () {
                $('.form-help:contains(UTC time is)').hide();
            });
        });
    });
});