/*!
 * Piwik - free/libre analytics platform
 *
 * Installation screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PiwikUpdater", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\CoreUpdater\\Tests\\Fixtures\\FailUpdateHttpsFixture";

    var url = "?module=CoreUpdater&action=newVersionAvailable";

    it("should show a new version is available", function (done) {
        expect.screenshot("newVersion").to.be.capture(function (page) {
            page.load(url);
        }, done);
    });
});
