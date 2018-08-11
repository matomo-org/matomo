/*!
 * Piwik - free/libre analytics platform
 *
 * Opt-out form tests
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('CustomOptOutForm', function () {
    this.timeout(0);

    this.fixture = 'Piwik\\Plugins\\PrivacyManager\\tests\\Fixtures\\CustomOptOutTextFixture';

    var siteUrl = "/tests/resources/overlay-test-site-real/index.html",
        chromeUserAgent = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36";

    it('should show custom opt out text when opted in', function (done) {
        expect.screenshot('opted_in').to.be.captureSelector('iframe#optOutIframe', function (page) {
            phantom.clearCookies();

            page.userAgent = chromeUserAgent;
            page.load(siteUrl);
        }, done);
    });

    it('should show custom opt out text when opted out', function (done) {
        expect.screenshot('opted_out').to.be.captureSelector('iframe#optOutIframe', function (page) {
            page.evaluate(function () {
                $('iframe#optOutIframe').contents().find('input#trackVisits').click();
            });
            page.wait(4000); // wait for iframe to reload
        }, done);
    });
});
