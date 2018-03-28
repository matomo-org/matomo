/*!
 * Piwik - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("MultiSitesTest", function () {
    this.timeout(0);

    var generalParams = 'idSite=1&period=year&date=2012-08-09';
    var selector = '#multisites,.expandDataTableFooterDrawer';

    var createdSiteId = null;

    before(function (done) {
        var callback = function (error, response) {
            if (error) {
                done(error, response);
                return;
            }
            
            createdSiteId = response.value;
            done();
        };

        testEnvironment.callApi("SitesManager.addSite", {
            siteName: '%3CMy%20website%22%27%3E%3B%2C%3F with a very very very very long stupid name',
            urls: 'http%3A%2F%2Fpiwik.org'},
        callback);
    });

    after(function (done) {
        if (createdSiteId) {
            testEnvironment.callApi("SitesManager.deleteSite", {idSite: createdSiteId}, done);
        }
    });

    it('should load the all websites dashboard correctly', function (done) {
        this.retries(3);

        expect.screenshot('all_websites').to.be.captureSelector(selector, function (page) {
            page.load("?" + generalParams + "&module=MultiSites&action=index");
            page.wait(3000);
        }, done);
    });

    it('should load next page correctly', function (done) {
        this.retries(3);

        expect.screenshot('all_websites_page_1').to.be.captureSelector(selector, function (page) {
            page.click('.paging .next');
            page.wait(1000);
        }, done);
    });

    it('should search correctly', function (done) {
        expect.screenshot('all_websites_search').to.be.captureSelector(selector, function (page) {
            page.sendKeys('.site_search input', 'Site');
            page.click('.site_search .search_ico');
        }, done);
    });

    it('should toggle sort order when click on current metric', function (done) {
        expect.screenshot('all_websites_changed_sort_order').to.be.captureSelector(selector, function (page) {
            page.click('#visits .heading');
        }, done);
    });

});
