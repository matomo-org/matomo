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
    var selector = '.pageWrap,.expandDataTableFooterDrawer';

    beforeEach(function () {
        delete testEnvironment.configOverride;
        testEnvironment.testUseRegularAuth = 0;
        testEnvironment.save();
    });

    after(function () {
        delete testEnvironment.queryParamOverride;
        testEnvironment.testUseRegularAuth = 0;
        testEnvironment.save();
    });

    it('should load the all websites dashboard correctly', function (done) {
        expect.screenshot('all_websites').to.be.captureSelector(selector, function (page) {
            page.load("?" + generalParams + "&module=MultiSites&action=index");
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