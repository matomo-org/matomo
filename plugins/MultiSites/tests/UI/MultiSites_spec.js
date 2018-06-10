/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests for MultiSites.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("MultiSitesTest", function () {
    this.timeout(0);

    var generalParams = 'idSite=1&period=year&date=2012-08-09';
    var rangeParams = 'idSite=1&period=range&date=2012-08-05,2012-08-15';
    var selector = '#multisites,.expandDataTableFooterDrawer';

    var createdSiteId = null;

    before(async function() {
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

    after(async function() {
        if (createdSiteId) {
            testEnvironment.callApi("SitesManager.deleteSite", {idSite: createdSiteId}, done);
        }
    });

    it('should load the all websites dashboard correctly', async function() {
        this.retries(3);

        expect.screenshot('all_websites').to.be.captureSelector(selector, function (page) {
            page.goto("?" + generalParams + "&module=MultiSites&action=index");
            page.wait(3000);
        }, done);
    });

    it('should load next page correctly', async function() {
        this.retries(3);

        expect.screenshot('all_websites_page_1').to.be.captureSelector(selector, function (page) {
            page.click('.paging .next');
            page.wait(1000);
        }, done);
    });

    it('should search correctly', async function() {
        expect.screenshot('all_websites_search').to.be.captureSelector(selector, function (page) {
            page.sendKeys('.site_search input', 'Site');
            page.click('.site_search .search_ico');
        }, done);
    });

    it('should toggle sort order when click on current metric', async function() {
        expect.screenshot('all_websites_changed_sort_order').to.be.captureSelector(selector, function (page) {
            page.click('#visits .heading');
        }, done);
    });

    it('should load the all websites dashboard correctly when period is range', function (done) {
        this.retries(3);

        expect.screenshot('all_websites_range').to.be.captureSelector(selector, function (page) {
            page.load("?" + rangeParams + "&module=MultiSites&action=index");
            page.wait(3000);
        }, done);
    });

});
