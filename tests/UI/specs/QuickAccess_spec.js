/*!
 * Piwik - free/libre analytics platform
 *
 * ActionsDataTable screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("QuickAccess", function () {
    this.retries(3);

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

    it("should be displayed", function (done) {
        capture('initially', done, function (page) {
            page.load(url);
        });
    });

    it("should search for something and update view", function (done) {
        capture('search_1', done, function (page) {
            enterSearchTerm(page, 'b');
        });
    });

    it("should search again when typing another letter", function (done) {
        capture('search_2', done, function (page) {
            enterSearchTerm(page, 'a');
        });
    });

    it("should show message if no results", function (done) {
        capture('search_no_result', done, function (page) {
            enterSearchTerm(page, 'x');
        });
    });

    it("should be possible to activate via shortcut", function (done) {
        capture('shortcut', done, function (page) {
            page.load(url);
            page.sendKeys("body", 'f');
        });
    });

    it("should search for websites", function (done) {
        capture('search_sites', done, function (page) {
            enterSearchTerm(page, 'si');
        });
    });

    it("clicking on a category should show all items that belong to that category", function (done) {
        capture('search_category', done, function (page) {
            page.click('.quick-access-category:first');
        });
    });

});
