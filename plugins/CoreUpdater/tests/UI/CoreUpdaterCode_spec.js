/*!
 * Matomo - free/libre analytics platform
 *
 * CoreUpdater screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("CoreUpdaterCode", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\CoreUpdater\\tests\\Fixtures\\FailUpdateHttpsFixture";

    var url = "?module=CoreUpdater&action=newVersionAvailable";

    it("should show a new version is available", async function() {
        expect.screenshot("newVersion").to.be.capture(function (page) {
            page.load(url);
        }, done);
    });

    it("should offer to retry using https when updating over https fails", async function() {
        expect.screenshot("httpsUpdateFail").to.be.capture(function (page) {
            page.click('#updateAutomatically');
        }, done);
    });

    it("should offer to retry over http when updating over https fails", async function() {
        expect.screenshot("httpsUpdateFail").to.be.capture(function (page) {
            page.click('#updateUsingHttps');
        }, done);
    });

    it("should show the update steps when updating over http succeeds", async function() {
        expect.screenshot("httpUpdateSuccess").to.be.capture(function (page) {
            page.click('#updateUsingHttp');
        }, done);
    });
});
