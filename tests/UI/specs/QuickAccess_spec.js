/*!
 * Piwik - free/libre analytics platform
 *
 * ActionsDataTable screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("QuickAccess", function () {

    var selectorToCapture = ".quick-access,.quick-access .dropdown";

    this.timeout(0);

    var url = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09";

    function enterSearchTerm(page, searchTermToAdd)
    {
        page.sendKeys(".quick-access input", searchTermToAdd);
    }

    function captureSelector(screenshotName, done, selector, callback)
    {
        expect.screenshot(screenshotName).to.be.captureSelector(selector, callback, done);
    }

    function capture(screenshotName, done, callback)
    {
        captureSelector(screenshotName, done, selectorToCapture, callback);
    }

    it("should be displayed", async function () {
        capture('initially', done, function (page) {
            page.goto(url);
        });
    });

    it("should search for something and update view", async function () {
        capture('search_1', done, function (page) {
            enterSearchTerm(page, 's');
        });
    });

    it("should search again when typing another letter", async function () {
        capture('search_2', done, function (page) {
            enterSearchTerm(page, 'a');
        });
    });

    it("should show message if no results", async function () {
        capture('search_no_result', done, function (page) {
            enterSearchTerm(page, 'alaskdjfs');
        });
    });

    it("should be possible to activate via shortcut", async function () {
        capture('shortcut', done, function (page) {
            page.goto(url);
            page.sendKeys("body", 'f');
        });
    });

    it("should search for websites", async function () {
        capture('search_sites', done, function (page) {
            enterSearchTerm(page, 'si');
        });
    });

    it("clicking on a category should show all items that belong to that category", async function () {
        capture('search_category', done, function (page) {
            page.click('.quick-access-category:first');
        });
    });

});
