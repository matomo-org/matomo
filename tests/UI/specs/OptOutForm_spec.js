/*!
 * Piwik - free/libre analytics platform
 *
 * Opt-out form tests
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// NOTE: this test actually tests safari-specific opt out form behavior, since phantomjs' user-agent string
//       is similar to Safari's
describe("OptOutForm", function () {
    this.timeout(0);

    var siteUrl = "/tests/resources/overlay-test-site-real/index.html",
        safariUserAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A",
        chromeUserAgent = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36";

    it("should display correctly when embedded in another site", function (done) {
        expect.screenshot('loaded').to.be.captureSelector('iframe#optOutIframe', function (page) {
            page.userAgent = chromeUserAgent;
            page.load(siteUrl);
        }, done);
    });

    it("should reload the iframe when clicking the opt out checkbox and display an empty checkbox", function (done) {
        expect.screenshot('opted-out').to.be.captureSelector('iframe#optOutIframe', function (page) {
            page.evaluate(function () {
                $('iframe#optOutIframe').contents().find('input#trackVisits').click();
            });
            page.wait(2000); // wait for iframe to reload
        }, done);
    });

    it("should correctly show the checkbox unchecked after reloading after opting-out", function (done) {
        expect.screenshot('opted-out').to.be.captureSelector('opted-out-reload', 'iframe#optOutIframe', function (page) {
            page.userAgent = chromeUserAgent;
            page.load(siteUrl);
        }, done);
    });

    it("should correctly show display opted-in form when cookies are cleared", function (done) {
        expect.screenshot('loaded').to.be.captureSelector('safari-loaded', 'iframe#optOutIframe', function (page) {
            page.webpage.clearCookies();

            page.userAgent = safariUserAgent;
            page.load(siteUrl);
        }, done);
    });

    it("should correctly set opt-out cookie on safari", function (done) {
        expect.screenshot('opted-out').to.be.captureSelector('safari-opted-out', 'iframe#optOutIframe', function (page) {
            page.evaluate(function () {
                $('iframe#optOutIframe').contents().find('input#trackVisits').click();
            });
            page.wait(1000); // wait for iframe to reload
            page.load(siteUrl); // reload to check that cookie was set
        }, done);
    });
});