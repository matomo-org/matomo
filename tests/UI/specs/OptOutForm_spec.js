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

    var siteUrl = "/tests/resources/overlay-test-site-real/index.html";

    it("should display correctly when embedded in another site", function (done) {
        expect.screenshot('loaded').to.be.captureSelector('iframe#optOutIframe', function (page) {
            page.load(siteUrl);
        }, done);
    });

    it("should reload the iframe when clicking the opt out checkbox and display an empty checkbox", function (done) {
        expect.screenshot('opted-out').to.be.captureSelector('iframe#optOutIframe', function (page) {
            page.evaluate(function () {
                $('iframe#optOutIframe').contents().find('input#trackVisits').click();
            });
            page.wait(3000); // wait for iframe to reload (after setTimeout in optOut.twig finishes)
        }, done);
    });

    it("should correctly show the checkbox unchecked after reloading after opting-out", function (done) {
        expect.screenshot('opted-out').to.be.captureSelector('opted-out-reload', 'iframe#optOutIframe', function (page) {
            page.load(siteUrl);
        }, done);
    });
});