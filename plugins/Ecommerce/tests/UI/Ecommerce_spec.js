/*!
 * Piwik - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Ecommerce", function () {
    this.timeout(0);

    var generalParams = 'idSite=1&period=year&date=2012-08-09',
        urlBase = 'module=CoreHome&action=index&' + generalParams;

    before(function (done) {
        testEnvironment.queryParamOverride = {
            forceNowValue: testEnvironment.forcedNowTimestamp,
            visitorId: testEnvironment.forcedIdVisitor,
            realtimeWindow: 'false'
        };
        testEnvironment.save();

        testEnvironment.callApi("SitesManager.setSiteAliasUrls", {idSite: 3, urls: []}, done);
    });

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

    // goals pages
    it('should load ecommerce overview', function (done) {
        expect.screenshot('ecommerce_overview').to.be.captureSelector('.pageWrap,.expandDataTableFooterDrawer', function (page) {
            page.load("?" + urlBase + "#" + generalParams + "&module=Ecommerce&action=ecommerceReport&idGoal=ecommerceOrder");
        }, done);
    });

    it('should load ecommerce log', function (done) {
        expect.screenshot('ecommerce_log').to.be.captureSelector('.pageWrap,.expandDataTableFooterDrawer', function (page) {
            page.load( "?" + urlBase + "#" + generalParams + "&module=Ecommerce&action=getEcommerceLog");
        }, done);
    });

    it('should load ecommerce products', function (done) {
        expect.screenshot('ecommerce_products').to.be.captureSelector('.pageWrap,.expandDataTableFooterDrawer', function (page) {
            page.load("?" + urlBase + "#" + generalParams + "&module=Ecommerce&action=ecommerceProducts&idGoal=ecommerceOrder");
        }, done);
    });

});